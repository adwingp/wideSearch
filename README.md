

---

# Website-wide Search System Documentation

A robust, developer-friendly search solution for Laravel 11 using Laravel Scout and Docker.

---

## Table of Contents
- [Project Overview](#project-overview)
- [Project Structure](#project-structure)
- [Docker-based Setup (Recommended)](#docker-based-setup-recommended)
- [Non-Docker Setup (Advanced)](#non-docker-setup-advanced)
- [Environment Configuration](#environment-configuration)
- [Indexing & Search Logic](#indexing--search-logic)
- [Running Queues & Scheduler](#running-queues--scheduler)
- [API Endpoints](#api-endpoints)
- [Sample Queries & Results](#sample-queries--results)
- [Troubleshooting](#troubleshooting)

---

## Project Overview
This project implements a unified, website-wide search system for Blog Posts, Products, Pages, and FAQs. It uses Laravel Scout for indexing and supports:
- Partial/fuzzy search
- Relevance ranking
- Pagination
- Unified `/api/search` endpoint
- Queue-based index sync (for large datasets)
- Dockerized development and deployment
- All API responses in camelCase

## Project Structure
```
app/
  Models/           # BlogPost, Product, Page, FAQ (all Searchable)
  Http/
    Controllers/    # SearchController (API)
  Services/         # SearchService (search logic)
config/
  scout.php         # Scout configuration
routes/
  api.php           # API route definitions
Dockerfile, docker-compose.yml
README.md           # This documentation
```

---

## Docker-based Setup (Recommended)

1. **Clone & Configure**
   ```bash
   git clone <repo-url>
   cd <project-folder>
   cp .env.example .env
   # Edit .env for your DB and SCOUT_DRIVER (database or meilisearch)
   ```
2. **Build & Start Containers**
   ```bash
   docker-compose up -d --build
   ```
   This starts both the Laravel app and the queue worker.
3. **Install Dependencies & Generate Key (in container)**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   ```
4. **Run Migrations & Seeders**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```
5. **Access the App**
   - Web: http://localhost:8000
   - API: http://localhost:8000/api/search
6. **Queue Worker**
   - Runs automatically in the `queue-worker` container.
   - View logs: `docker-compose logs -f queue-worker`

---

## Non-Docker Setup (Advanced)
1. Install PHP, Composer, MySQL, Node.js as per Laravel docs.
2. Clone repo, copy `.env`, set DB config, and run:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   php artisan queue:work
   ```
3. All search, indexing, and API usage is the same as above.

---

## Environment Configuration
- Edit `.env` for your database and search driver:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=db
  DB_PORT=3306
  DB_DATABASE=laravel
  DB_USERNAME=laravel
  DB_PASSWORD=secret
  SCOUT_DRIVER=database # or meilisearch
  # For MeiliSearch:
  # MEILISEARCH_HOST=http://meilisearch:7700
  # MEILISEARCH_KEY=null
  ```
- For Docker, ensure your `docker-compose.yml` matches your `.env` DB settings.

---

## Indexing & Search Logic
- All models (`BlogPost`, `Product`, `Page`, `FAQ`) use the `Searchable` trait and implement `toSearchableArray()` for relevant fields.
- Laravel Scout automatically updates the index on create, update, and delete actions.
- Queue-based index sync is enabled in `config/scout.php` (`'queue' => true`).
- The `/api/search` endpoint aggregates results from all indexed models, supports partial/fuzzy search, relevance ranking, and pagination.
- All API responses use camelCase keys for consistency.

---

## Running Queues & Scheduler
- **Queue Worker**
  - Docker: Runs automatically in `queue-worker` service.
  - Non-Docker: Run `php artisan queue:work` manually.
- **Scheduler**
  - If you add scheduled jobs, use `php artisan schedule:work` or set up a cron job as per Laravel docs (not required for basic search).

---

## API Endpoints

### Unified Search
- **POST /api/search**
  - **Body:**
    ```json
    {
      "q": "search term",
      "perPage": 10,
      "page": 1
    }
    ```
  - **Response:**
    - `data`: Array of search results (blog posts, products, pages, faqs)
    - `meta`: Pagination and query info
    - All keys in camelCase

### Example Response
```json
{
  "data": [
    {
      "type": "blogPost",
      "title": "Laravel 11 Released",
      "snippet": "Laravel 11 introduces ...",
      "link": "http://localhost:8000/blog/1",
      "publishedAt": "2025-07-30T10:00:00Z"
    },
    {
      "type": "faq",
      "title": "How to install Laravel?",
      "snippet": "To install Laravel, you ...",
      "link": "http://localhost:8000/faqs/2"
    }
  ],
  "meta": {
    "total": 2,
    "perPage": 10,
    "currentPage": 1,
    "query": "laravel"
  }
}
```

---

## Troubleshooting
- **Database connection errors:**
  - Ensure DB settings in `.env` and `docker-compose.yml` match.
  - Make sure the DB container is running (`docker-compose ps`).
- **Queue worker not processing jobs:**
  - Check logs: `docker-compose logs -f queue-worker`
  - Ensure `'queue' => true` in `config/scout.php`.
- **Search returns empty results:**
  - Ensure models are indexed and seeders have run.
  - Try reindexing if using MeiliSearch.
- **Port conflicts:**
  - Make sure port 8000 is free or update `docker-compose.yml`.

---

Happy searching!

### 4. Run Migrations & Seeders
```bash
docker-compose exec app php artisan migrate --seed
```

### 5. Access the App
- App: http://localhost:8000
- API: http://localhost:8000/api/search

### 6. Queue Worker
- The queue worker runs automatically in the `queue-worker` container.
- To view logs:
  ```bash
  docker-compose logs -f queue-worker
  ```

---

## Indexing & Search Logic
- All searchable models (`BlogPost`, `Product`, `Page`, `FAQ`) use the `Searchable` trait and define `toSearchableArray()`.
- Laravel Scout auto-indexes on create, update, delete.
- Index sync is via Laravel Queues (queueing enabled in `config/scout.php`).
- Unified `/api/search` endpoint aggregates all models, supports partial/fuzzy search, relevance, pagination.
- All API responses use camelCase keys.

---

## Sample Search Query
- **Endpoint:** `POST /api/search`
- **Request Body:**
  ```json
  {
    "q": "laravel",
    "perPage": 10,
    "page": 1
  }
  ```
- **Sample Response:**
  ```json
  {
    "data": [
      {
        "type": "blogPost",
        "title": "Laravel 11 Released",
        "snippet": "Laravel 11 introduces ...",
        "link": "http://localhost:8000/blog/1",
        "publishedAt": "2025-07-30T10:00:00Z"
      },
      {
        "type": "faq",
        "title": "How to install Laravel?",
        "snippet": "To install Laravel, you ...",
        "link": "http://localhost:8000/faqs/2"
      }
    ],
    "meta": {
      "total": 2,
      "perPage": 10,
      "currentPage": 1,
      "query": "laravel"
    }
  }
  ```

---

## Minimal Non-Docker Setup (for advanced users)

1. Install PHP, Composer, MySQL, and Node.js as per Laravel docs.
2. Clone repo, copy `.env`, set DB config, and run:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   php artisan queue:work
   ```
3. All search, indexing, and API usage is the same as above.

---
