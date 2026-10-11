# Student Information Management System

Laboratory 2 and 3 project with a Laravel 12 REST API and a React/Vite single page application. Laravel is the source of truth for data validation and authorization; the frontend communicates through the versioned JSON API and does not connect directly to MySQL.

## Project layout

- backend/ — Laravel API, Sanctum authentication, migrations, seeders, PHPUnit tests, and API documentation.
- frontend/ — React application, role-aware routes and pages, centralized Axios client, and Vite tooling.
- docs/ — recovery audit, verification evidence, API integration map, AI development log, and final verification report.

## Local setup

See [backend setup](backend/README.md) and [frontend setup](frontend/README.md) for dependency and environment instructions. Set local API and database configuration in ignored .env files; never commit secrets. Only run migrations against a newly created local database intended for this application.

## Submission documents

- [API Integration Map](docs/API_INTEGRATION_MAP.md)
- [AI Development Log](docs/AI_DEVELOPMENT_LOG.md)
- [Final Verification Report](docs/FINAL_VERIFICATION_REPORT.md)
- [Phase 9 test evidence](docs/PHASE_9_TEST_REPORT.md)
- [OpenAPI specification](backend/docs/openapi.json)
- [Postman collection](backend/docs/postman/Student-Information-System.postman_collection.json)
- [Architecture and technical documentation](backend/docs/TECHNICAL_DOCUMENTATION.md)
- [Entity Relationship Diagram](backend/docs/ERD.md)

Backend automated tests use SQLite in-memory as configured in backend/phpunit.xml; they do not require or modify the development MySQL database.
