<?php

namespace App\Services;

use AdaiasMagdiel\Rubik\Rubik;
use App\Cache;
use Exception;
use GuzzleHttp\Client;

/**
 * Service responsible for interacting with the Jikan API (MyAnimeList).
 * * Provides methods for fetching raw anime data and generating 
 * processed analytics with a built-in caching layer.
 */
class JikanService
{
	/** @var Client|null Singleton instance of the Guzzle HTTP Client */
	private static ?Client $client = null;

	/**
	 * Initializes or returns the Guzzle client with pre-configured headers.
	 * * @return Client
	 */
	private static function getClient(): Client
	{
		if (self::$client === null) {
			self::$client = new Client([
				'base_uri' => 'https://api.jikan.moe/v4/',
				'timeout'  => 15.0,
				'headers'  => [
					'User-Agent' => 'anime-analytics/1.0 (+https://github.com/adaiasmagdiel/anime-analytics)',
					'Accept'     => 'application/json',
				]
			]);
		}

		return self::$client;
	}

	/**
	 * Internal helper to process raw anime data into a rich set of analytics.
	 *
	 * Extracts counts for sources, types, ratings, studios, producers, 
	 * genres, themes, and demographics. Also calculates scoring distributions.
	 * * @param array $animeList Raw list of anime data from the API.
	 * @return array Processed statistics and distributions.
	 */
	private static function processStats(array $animeList): array
	{
		$stats = [
			'count' => count($animeList),
			'sources' => [],      // Pie Chart: Manga vs Original
			'types' => [],        // Pie Chart: TV vs Movie
			'ratings' => [],      // Doughnut: Age rating
			'studios' => [],      // Bar Chart: Most productive studios
			'producers' => [],    // Bar Chart: Funding entities
			'genres' => [],       // Horizontal Bar: Genres
			'themes' => [],       // Word Cloud or Bar: Themes
			'demographics' => [], // Radar Chart: Shounen, Seinen, etc.
			'scoring' => [
				'average' => 0,
				'distribution' => [
					'masterpiece' => 0, // 9-10
					'great' => 0,       // 7-8.99
					'average' => 0,     // 5-6.99
					'bad' => 0          // < 5
				]
			]
		];

		$totalScore = 0;
		$scoredAnimes = 0;

		foreach ($animeList as $anime) {
			// Basic counts
			$stats['sources'][$anime['source'] ?? 'Unknown'] = ($stats['sources'][$anime['source'] ?? 'Unknown'] ?? 0) + 1;
			$stats['types'][$anime['type'] ?? 'Unknown'] = ($stats['types'][$anime['type'] ?? 'Unknown'] ?? 0) + 1;
			$stats['ratings'][$anime['rating'] ?? 'Unknown'] = ($stats['ratings'][$anime['rating'] ?? 'Unknown'] ?? 0) + 1;

			// Score Analysis
			if (!empty($anime['score'])) {
				$score = $anime['score'];
				$totalScore += $score;
				$scoredAnimes++;

				if ($score >= 9) $stats['scoring']['distribution']['masterpiece']++;
				elseif ($score >= 7) $stats['scoring']['distribution']['great']++;
				elseif ($score >= 5) $stats['scoring']['distribution']['average']++;
				else $stats['scoring']['distribution']['bad']++;
			}

			// Entity counting (Studios, Genres, etc.)
			$countEntity = function ($entities, $key) use (&$stats) {
				foreach ($entities as $entity) {
					$name = $entity['name'];
					$stats[$key][$name] = ($stats[$key][$name] ?? 0) + 1;
				}
			};

			$countEntity($anime['studios'], 'studios');
			$countEntity($anime['producers'], 'producers');
			$countEntity($anime['genres'], 'genres');
			$countEntity($anime['themes'], 'themes');
			$countEntity($anime['demographics'], 'demographics');
		}

		$stats['scoring']['average'] = $scoredAnimes > 0 ? round($totalScore / $scoredAnimes, 2) : 0;

		// Sort descending to make Chart.js rendering easier
		foreach (['sources', 'types', 'ratings', 'studios', 'producers', 'genres', 'themes', 'demographics'] as $key) {
			arsort($stats[$key]);
		}

		return $stats;
	}

	/**
	 * Returns full analytics for a specific season.
	 * * @param string|null $season Season name (winter, spring, summer, fall).
	 * @param int|null    $year   Calendar year.
	 * @return array Processed stats for the given season.
	 */
	public static function seasonAnalytics(?string $season = null, ?int $year = null): array
	{
		$year = $year ?? (int) date('Y');

		if ($season === null) {
			$month = (int) date('n');
			$season = match (true) {
				$month >= 1 && $month <= 3  => "winter",
				$month >= 4 && $month <= 6  => "spring",
				$month >= 7 && $month <= 9  => "summer",
				default                     => "fall",
			};
		}

		return Cache::remember("analytics:season:$year:$season", function () use ($year, $season) {
			$data = self::season($season, $year);
			return self::processStats($data);
		}, Cache::DAY * 7);
	}

	/**
	 * Returns full analytics for the entire year.
	 * * Includes a special 'seasonal_performance' comparison.
	 * * @param int|null $year Calendar year.
	 * @return array Processed stats and seasonal comparison.
	 */
	public static function yearAnalytics(?int $year = null): array
	{
		$year = $year ?? (int) date('Y');

		return Cache::remember("analytics:year:$year", function () use ($year) {
			$allAnime = self::year($year);
			$stats = self::processStats($allAnime);

			// Special addition for year view: Performance per season
			$seasonalScores = [];
			foreach (['winter', 'spring', 'summer', 'fall'] as $s) {
				$seasonData = array_filter($allAnime, fn($a) => ($a['season'] ?? '') === $s);
				$totalS = array_reduce($seasonData, fn($carry, $item) => $carry + ($item['score'] ?? 0), 0);
				$countS = count(array_filter($seasonData, fn($a) => !empty($a['score'])));
				$seasonalScores[$s] = $countS > 0 ? round($totalS / $countS, 2) : 0;
			}

			$stats['seasonal_performance'] = $seasonalScores;
			return $stats;
		}, Cache::DAY * 7);
	}

	/**
	 * Fetches raw anime data for a specific season.
	 * * Iterates through all Jikan pagination to compile a full list.
	 *
	 * @param string|null $season Season name.
	 * @param int|null    $year   Calendar year.
	 * @throws Exception If year is outside allowed range (1922 to current+5).
	 * @return array List of processed anime items.
	 */
	public static function season(?string $season = null, ?int $year = null): array
	{
		$currentYear = (int) date('Y');
		$year = $year ?? $currentYear;

		$maxYear = $currentYear + 5;
		if ($year < 1922 || $year > $maxYear) {
			throw new Exception("Invalid year range: 1922 to $maxYear");
		}

		if ($season === null) {
			$month = (int) date('n');
			$season = match (true) {
				$month >= 1 && $month <= 3  => "winter",
				$month >= 4 && $month <= 6  => "spring",
				$month >= 7 && $month <= 9  => "summer",
				default                     => "fall",
			};
		}

		$season = strtolower($season);

		return Cache::remember("season_full:$year:$season", function () use ($year, $season) {
			$client = self::getClient();
			$results = [];
			$currentPage = 1;
			$hasMorePages = true;

			/**
			 * Helper to clean up related item arrays (genres, studios, etc.)
			 */
			$processItemArray = fn(array $item): array => [
				"mal_id" => $item['mal_id'] ?? null,
				"type"   => $item['type'] ?? 'unknown',
				"name"   => $item['name'] ?? 'N/A',
			];

			while ($hasMorePages) {
				$response = $client->get("seasons/$year/$season", [
					"query" => ["sfw" => true, "page" => $currentPage]
				]);

				$json = json_decode($response->getBody(), true);
				$data = $json['data'] ?? [];

				$totalPages = $json['pagination']['last_visible_page'] ?? 1;
				dev_log("[JikanService::season $currentPage/$totalPages] - $season | $year ");

				foreach ($data as $a) {
					if (!in_array(strtolower($a['type'] ?? ''), ['tv', 'movie', 'ona'])) {
						continue;
					}

					$results[] = [
						"mal_id" => $a['mal_id'],
						"title" => $a['title'],
						"url" => $a['url'],
						"image" => $a['images']['webp']['image_url'] ?? $a['images']['jpg']['image_url'] ?? 'https://placehold.co/225x317?text=No%20Image%20Available',
						"type" => $a['type'] ?? null,
						"source" => $a['source'] ?? null,
						"rating" => $a['rating'] ?? null,
						"score" => $a['score'] ?? null,
						"popularity" => $a['popularity'] ?? null,
						"season" => $a['season'] ?? null,
						"broadcast" => $a['broadcast']['string'] ?? null,

						"producers" => array_map($processItemArray, ($a['producers'] ?? [])),
						"licensors" => array_map($processItemArray, ($a['licensors'] ?? [])),
						"studios" => array_map($processItemArray, ($a['studios'] ?? [])),
						"genres" => array_map($processItemArray, ($a['genres'] ?? [])),
						"explicit_genres" => array_map($processItemArray, ($a['explicit_genres'] ?? [])),
						"themes" => array_map($processItemArray, ($a['themes'] ?? [])),
						"demographics" => array_map($processItemArray, ($a['demographics'] ?? [])),
					];
				}

				$lastVisiblePage = $json['pagination']['last_visible_page'] ?? 1;

				if ($currentPage >= $lastVisiblePage) {
					$hasMorePages = false;
				} else {
					$currentPage++;
					sleep(1);
				}
			}

			return $results;
		}, Cache::DAY * 7);
	}

	/**
	 * Compiles raw data for all four seasons of a specific year.
	 * * @param int|null $year Calendar year.
	 * @throws Exception If year is outside allowed range.
	 * @return array Merged list of all anime for the year.
	 */
	public static function year(?int $year = null): array
	{
		$currentYear = (int) date('Y');
		$year = $year ?? $currentYear;

		$maxYear = $currentYear + 5;
		if ($year < 1922 || $year > $maxYear) {
			throw new Exception("Invalid year range: 1922 to $maxYear");
		}

		$seasons = ['winter', 'spring', 'summer', 'fall'];

		// Use the season method to get the cache if exists
		$data = [];
		foreach ($seasons as $season) {
			$res = self::season($season, $year);
			array_push($data, ...$res);
			sleep(1);
		}

		return $data;
	}
}
