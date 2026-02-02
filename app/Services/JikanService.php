<?php

namespace App\Services;

use App\Cache;
use DateTime;
use Exception;
use GuzzleHttp\Client;

class JikanService
{
	private static ?Client $client = null;

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

	public static function season(?string $season = null, ?int $year = null)
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
}
