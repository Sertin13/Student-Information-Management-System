# Student Information Management System API

Laravel 12 REST API for student information, programs, courses, terms, offerings, enrollments, grades, and academic records.

## Stack

PHP 8.4, Laravel 12, MySQL, Laravel Sanctum, PHPUnit, and SQLite in-memory testing.

## Install and run

```bash
git clone <repository-url>
cd backend
composer install
copy .env.example .env
php artisan key:generate
```

Create an empty MySQL database and set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in local `.env`. Never commit `.env`.

```bash
php artisan migrate
php artisan db:seed --class=AcademicSeeder
php artisan serve
```

The API base URL is `http://127.0.0.1:8000/api/v1`.

## Authentication

`POST /api/v1/auth/login` returns a Sanctum token. Send it on protected requests:

```http
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Seeded development accounts intentionally defined by `AcademicSeeder`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| Staff | staff@example.com | password |
| Staff | registrar@example.com | password |
| Instructor | instructor@example.com | password |
| Student | student@example.com | password |

Change them in deployed environments.

## Documentation

- [OpenAPI specification](docs/openapi.json) — import into Swagger Editor, or configure Swagger UI to load this file.
- [ERD](docs/ERD.md)
- [Postman collection](docs/postman/Student-Information-System.postman_collection.json)
- [Technical documentation](docs/TECHNICAL_DOCUMENTATION.md)

## Tests

```bash
php artisan test
```

`phpunit.xml` uses SQLite in-memory; the suite does not write to the configured development MySQL database.

## Structure

- `app/Models` — entities and relationships
- `app/Http/Controllers/Api/V1` — API controllers
- `routes/api.php` — versioned routes
- `database/migrations`, `database/seeders` — schema and sample data
- `tests` — isolated tests
- `docs` — submission artifacts

## Security and AI disclosure

Keep secrets and tokens out of version control, use a production-safe `APP_KEY`, and restrict production credentials. API errors avoid stack traces. AI assistance supported implementation, test creation, documentation, and review; changes were checked against routes, migrations, and the automated suite.
