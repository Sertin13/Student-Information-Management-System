# Technical Documentation

## Architecture

The application has a React 19/Vite single page frontend and a Laravel 12 JSON API. React pages call API services through a centralized Axios client configured with VITE_API_BASE_URL. AuthContext manages the Sanctum token and current user. The browser never connects directly to MySQL.

Laravel route definitions are in routes/api.php. API controllers live in app/Http/Controllers/Api/V1, request validation uses Laravel validation rules, and Eloquent models represent the schema. MySQL is the development persistence layer. The frontend route and endpoint mapping is in ../../docs/API_INTEGRATION_MAP.md.

## Data model

The schema contains roles, users, programs, students, academic terms, courses, course offerings, enrollments, and grades. A Student may link to a user account. Course offerings reference a course and academic term and may reference an Instructor-role user. Enrollments reference a Student and course offering and are unique per pair. Each enrollment can have at most one grade. See ERD.md for the entity relationship diagram.

## Authentication and authorization

Sanctum issues bearer tokens. Admin and Staff can access management resources. Course Offering instructor assignment accepts only an existing user with the Instructor role; null remains valid for unassigned offerings. Instructor teaching endpoints scope results and grade writes to the authenticated instructor's assigned offerings. Student self-service is scoped to the Student profile linked to the authenticated account. The backend enforces all permissions independently of frontend navigation.

Successful API responses use success/data envelopes. Validation errors use 422, unauthenticated and forbidden requests use 401 and 403, conflicts use 409, and missing resources use safe 404 JSON. Collection endpoints return Laravel pagination metadata. Student collections support search, program/year/status filters, controlled sorting, and capped pagination.

## Verification and development assistance

backend/phpunit.xml configures SQLite in-memory for automated tests. The test suite exercises API validation, CRUD, ownership, role authorization, academic relationships, and error responses without using development MySQL. Current results are recorded in ../../docs/FINAL_VERIFICATION_REPORT.md.

AI assistance supported implementation review, focused test creation, and documentation. Changes were checked against route definitions, migrations, API responses, and automated tests. See ../../docs/AI_DEVELOPMENT_LOG.md.
