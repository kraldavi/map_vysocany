# Vysočany Map

Web app showing address points from OpenStreetMap on an interactive map (Leaflet).
Each house can link to the cadastre (S-JTSK coordinates) and store owner records.

## Stack

| Layer | Stack |
|--------|--------|
| Backend | PHP 8.2+, [Nette](https://nette.org) 3.2 |
| ORM | [Nextras ORM](https://nextras.org/orm/) + [Nextras Dbal](https://github.com/nextras/dbal) |
| Database | MySQL 8 |
| Frontend | Latte, Leaflet, OpenStreetMap tiles |
| Console | Symfony Console + Contributte |
| Coordinates | [proj4php](https://github.com/proj4php/proj4php) (WGS84 → S-JTSK EPSG:5514) |
| Containers | Docker Compose |

## Quick start

```bash
# 0. Dependencies (locally or in the composer container)
cd www && composer install

# 1. Build and start (Apache + MySQL)
docker compose up -d --build

# 2. Database migrations (table schema)
docker compose exec app php bin/console.php migrations:continue

# 3. Import addresses from OSM JSON (~100 points)
docker compose exec app php bin/console.php app:import-osm

# 4. Open in browser
# http://localhost:8000
```

> **Note:** The database init script runs only on first volume creation.
> If you previously used another DB name, remove the volume: `docker compose down -v`

> **Owners schema upgrade:** if you still have the old `owners` table with `place`/`housenumber`,
> run `docker/mysql/02-migrate-owners-house-id.sql`.

## Project layout

```
mapa_vysocany/
├── docker-compose.yml
├── docker/mysql/01-schema.sql   # house + owners table schema
└── www/
    ├── app/
    │   ├── Model/               # Nextras ORM (Orm, House, Owner)
    │   ├── Console/ImportOsmCommand.php
    │   └── Presentation/
    │       ├── Home/            # map
    │       └── Owners/          # API to save owners
    ├── migrations/              # Nextras Migrations (structures, basic-data, …)
    ├── www/data.json            # export from Overpass API (OSM)
    └── config/
        ├── common.neon
        └── extensions.neon      # Nextras Dbal + Orm
```

### Nextras ORM

- `App\Model\Orm` – central model with `houses` and `owners` repositories
- `House` **1:m** `Owner` (FK `owners.house_id`)
- Presenters and console use `$orm->houses` / `$orm->owners`, not Nette Database

## API – save owners

`POST /owners` (form-data, **requires API key**)

Set `API_KEY` in the environment (Docker Compose uses `dev-secret` by default).
If `API_KEY` is empty, the endpoint stays open — useful for local experiments only.

Send the key as `Authorization: Bearer <key>` or header `X-Api-Key: <key>`.

| Field | Description |
|------|--------|
| `place` | Place name (e.g. `Molenburk`) |
| `housenumber` | House number |
| `owners` | JSON array `[{"name":"…","share":"1/2"}, …]` |

Example:

```bash
curl -X POST http://localhost:8000/owners \
  -H "Authorization: Bearer dev-secret" \
  -d 'place=Molenburk' \
  -d 'housenumber=97' \
  -d 'owners=[{"name":"John Doe","share":"1/2"},{"name":"Jane Doe","share":"1/2"}]'
```

Houses with recorded owners appear **red** on the map, others **blue**.

## Local development without Docker

```bash
cd www
composer install
php -S localhost:8000 -t www
```

Edit `config/common.local.neon` (database host `127.0.0.1`) and run migrations:

```bash
cd www && composer install
php bin/console.php migrations:continue
```

## Database migrations

The project uses [Nextras Migrations](https://nextras.org/migrations/) on Nextras Dbal.

| Command | Description |
|--------|--------|
| `php bin/console.php migrations:continue` | Run all pending migrations |
| `php bin/console.php migrations:create structures change-description` | Create a new SQL file in group `structures` |
| `php bin/console.php migrations:reset` | Drop DB and re-run migrations (dev only) |

Groups in `www/migrations/`:

- `structures` – schema changes (`CREATE` / `ALTER` tables)
- `basic-data` – production data
- `dummy-data` – test data (debug mode only)

In Docker:

```bash
docker compose run --rm composer require   # after adding a dependency
docker compose exec app php bin/console.php migrations:continue
```

## Code quality (lint)

| Tool | Purpose | Command |
|---------|------|--------|
| [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) | PHP style (PSR-12, tabs, strict types) | `composer cs-check` / `composer cs-fix` |
| [PHPStan](https://phpstan.org/) | Static analysis (level 6) | `composer phpstan` |
| Latte linter | `.latte` templates | `composer latte-lint` |

All at once:

```bash
cd www && composer install
composer lint
```

In Docker (`composer` service):

```bash
docker compose run --rm composer install
docker compose run --rm composer lint
```

### Tests (Nette Tester)

Tests use a **separate database** `mapa_vysocany_test` (not `mapa_vysocany` with your data). Tables are truncated before each test (`TRUNCATE`).

**One-time** test schema setup (a new Docker volume applies `docker/mysql/03-test-database.sql` automatically):

```bash
docker compose exec -T db mysql -uroot -prootsecret < docker/mysql/03-test-database.sql
```

Run tests (sequential `-j 1`, shared test DB):

```bash
cd www && composer tester
```

In Docker:

```bash
docker compose up -d db app
docker compose exec app php vendor/bin/tester tests -s -j 1
```

> The `composer` service uses the `composer` entrypoint — for dependencies: `docker compose run --rm composer install`.

Optional: `TEST_DB_HOST`, `TEST_DB_DATABASE` (must end with `_test`), `TEST_DB_USER`, `TEST_DB_PASSWORD`, `TEST_API_KEY`.

Config: `www/.php-cs-fixer.dist.php`, `www/phpstan.neon`, `.editorconfig`.  
GitHub workflow `.github/workflows/qa.yml` runs on push / pull request.

## Possible improvements

- **UI form** – add owners from the map popup (instead of curl)
- **CI tests** – add Nette Tester to workflow `qa.yml`
- **Import test** – fixture JSON → verify S-JTSK coordinates
- **Assets** – Leaflet via npm/Vite instead of CDN
- **Idempotent import** – `--fresh` flag for full reload

## Data licence

Address data comes from [OpenStreetMap](https://www.openstreetmap.org/) (ODbL licence).
