# Anime Analytics Dashboard

A lightweight, high-performance dashboard for visualizing anime statistics categorized by season and year. This project leverages a custom-built PHP ecosystem to handle routing, data persistence, and database migrations.

## Built With

This project is a showcase of the **Adaias Magdiel** PHP ecosystem:

- **[Erlenmeyer](https://github.com/adaiasmagdiel/erlenmeyer):** A sleek web framework for routing and request handling.
- **[Rubik ORM](https://github.com/adaiasmagdiel/rubik-orm):** Used as the backbone for managing the SQLite cache layer.
- **[FullCrawl](https://github.com/adaiasmagdiel/fullcrawl):** The migration manager used to keep the database schema in sync.
- **[Guzzle](https://github.com/guzzle/guzzle):** Used to communicate with the [Jikan API](https://jikan.moe/) (Unofficial MyAnimeList API).

## System Architecture

The dashboard implements a **Write-Through Cache** strategy using SQLite. To respect the Jikan API rate limits and provide a fast user experience:

1. On the first request for a specific season/year, the system crawls all available pages from the API.
2. The processed data is stored as a JSON payload in the SQLite `cache` table.
3. Subsequent requests are served instantly from the local database for the next 7 days.

## Installation

1. **Clone the repository:**

```bash
git clone https://github.com/adaiasmagdiel/anime-analytics.git
cd anime-analytics

```

2. **Install dependencies:**

```bash
composer install

```

3. **Run Migrations:**
   Use FullCrawl to set up your SQLite cache table:

```bash
php vendor/bin/fullcrawl --run

```

4. **Start the server:**
   You can use the built-in PHP server or a local environment:

```bash
php -S localhost:5013

```

## API Endpoints

### Get Season Data

Returns a collection of anime for a specific season.

- **URL:** `/api/season`
- **Method:** `GET`
- **Query Params:**
  - `year` (Optional, defaults to current year)
  - `season` (Optional: `winter`, `spring`, `summer`, `fall`. Defaults to current season)

**Example Request:**
`GET /api/season?year=2024&season=winter`

### Get Year Full Data

Returns a collection of all anime for a specific year.

- **URL:** `/api/yeary`
- **Method:** `GET`
- **Params:**
  - `year`

**Example Request:**
`GET /api/year/2026`

## License

This project is licensed under the **GPLv3 License**. See the `[LICENSE](LICENSE)` and `[COPYRIGHT](COPYRIGHT)` files for more details.
