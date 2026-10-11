# Student Information System Frontend

React 19 and Vite frontend for the Laravel 12 REST API. Authentication uses Laravel Sanctum; all API calls go through the centralized Axios client.

## Local setup

Install the locked dependencies and create a local frontend environment file:

```powershell
npm.cmd ci
Copy-Item .env.example .env
npm.cmd run dev -- --host 127.0.0.1
```

Set VITE_API_BASE_URL to the Laravel API base URL, for example http://127.0.0.1:8000/api/v1. Do not put backend credentials or tokens in this file.

## Verification commands

```powershell
npm.cmd run lint
npm.cmd run build
npm.cmd audit
```

## Implemented areas

The app includes authentication and profile views, a role-aware Dashboard, Student Management, Programs, Courses, Academic Terms, Course Offerings, Enrollments, Grades, and Academic Records. API access and role permissions are documented in ../docs/API_INTEGRATION_MAP.md. The Laravel backend remains authoritative for validation and authorization.
