# IT85 Laboratory 2 and 3 — Codex Project Instructions

## 1. Project Overview

This project is a Student Information Management System.

**Backend**
- Laravel 12
- Laravel Sanctum authentication
- MySQL database
- REST API
- PHPUnit tests

**Frontend**
- React + Vite
- Axios
- React Router
- Tailwind CSS
- Context API

Project structure:
- `backend/` — Laravel REST API
- `frontend/` — React application

The Laravel backend is the authoritative source for data, validation, and authorization.

## 2. Token Efficiency — HIGH PRIORITY

Minimize unnecessary token consumption without compromising correctness.

- Implement requested changes directly.
- Avoid long explanations and repeated progress updates.
- Do not repeat or summarize my prompt before working.
- Inspect only files relevant to the task.
- Prefer targeted searches over reading entire directories.
- Do not repeatedly inspect unchanged files without a reason.
- Reuse existing components, services, and utilities.
- Avoid unnecessary refactoring.
- Keep terminal output concise.
- Do not generate documentation unless requested.
- Do not perform web searches or use Exa unless explicitly requested or essential.
- Do not run redundant tests or builds.
- Make routine implementation decisions independently.
- Report only important findings, failures, and blockers.

Never skip necessary validation, security checks, or testing merely to save tokens.

## 3. Database Safety — CRITICAL

Development MySQL database:

`student_information_system`

NEVER execute against the development database:

- `php artisan migrate:fresh`
- `php artisan db:wipe`
- Database resets or truncation
- Destructive reseeding
- Destructive automated tests

Additional restrictions:

- Never delete existing seeded records merely for testing.
- Never modify existing academic records without authorization.
- Never change development database credentials without approval.
- Never disable foreign-key checks to force cleanup.
- Never run tests configured to reset the development MySQL database.
- Use isolated SQLite in-memory for automated backend tests.
- Inspect the testing environment before running database-related tests.

Normal authenticated API requests are allowed.

For CRUD verification, use newly created temporary records only when appropriate.

Do not perform destructive cleanup if relationships prevent safe deletion.

## 4. Backend Development Rules

- Preserve completed Laboratory 2 functionality.
- Do not modify backend endpoints without explicit approval.
- Inspect actual Laravel routes and controllers before integrating APIs.
- Do not invent endpoints, fields, validation rules, or response structures.
- Preserve Sanctum authentication and existing authorization.
- Never weaken security to make frontend functionality work.
- Report genuine backend limitations before implementing changes.

## 5. Frontend Development Rules

- Reuse the centralized Axios API client.
- Use `VITE_API_BASE_URL` for backend configuration.
- Never connect React directly to MySQL.
- Use real Laravel API responses instead of mock data.
- Reuse existing authentication, routing, and component patterns.
- Respect actual backend role permissions.
- Display appropriate loading, empty, validation, and error states.
- Keep the interface responsive and consistent.
- Avoid unnecessary dependencies and UI frameworks.

## 6. Error Handling

Handle relevant HTTP responses correctly:

- 200/201 — Successful operation
- 204 — Successful operation without response body
- 401 — Unauthenticated
- 403 — Forbidden
- 404 — Resource not found
- 409 — Conflict
- 422 — Validation failure
- 500+ — Server error

Do not expose raw stack traces or sensitive backend details.

## 7. Development Scope

- Work only on the requested feature or phase.
- Do not implement future phases automatically.
- Do not rewrite completed modules unnecessarily.
- Prefer minimal, targeted changes.
- If a major design decision or backend modification is required, request approval.
- Preserve existing working functionality.

## 8. Verification

- Run relevant tests when appropriate.
- Run `npm run build` after meaningful frontend changes.
- Verify real API integration when required.
- Clearly distinguish implemented features from verified features.
- Do not claim successful tests that were not executed.
- Avoid repeating successful tests unnecessarily unless affected code changes.

## 9. Current Project Status

Laboratory 2:
- Backend complete.
- Authentication, authorization, CRUD modules, documentation, and automated tests implemented.

Laboratory 3:
- Phase 1: Backend Review — Complete
- Phase 2: Frontend Setup — Complete
- Phase 3: Authentication — Complete
- Phase 4: Programs, Courses, Academic Terms — Complete
- Phase 5: Student Management — Complete
- Phase 6: Course Offerings, Enrollments, Grades — Verified for Admin/Staff
- Phase 7: Academic Record and Role Views — Implemented; Student self-service verification pending
- Phase 8: UX Hardening — Pending
- Phase 9: Testing — Pending
- Phase 10: Documentation and Defense — Pending

Known limitations:
- Existing Student test account has no linked Student profile.
- Instructor enrollment discovery requires an additional authorized backend endpoint.
- Phase 6 temporary verification records must be preserved until cleanup is explicitly approved.

## 10. Mandatory Codex Response Format

Every final response MUST contain exactly these five lines, in this order, with no introduction, explanation, or content before or after them:

Completed: [Brief description of completed work]
Tests: [Actual tests performed and results, or None run]
Issues: [Remaining problems or None]
Files changed: [Exact modified paths or None]
Next: [One recommended next action]

Do not explain actions step by step or provide lengthy explanations, introductions, or unnecessary progress summaries. Keep each line concise while accurately reporting failures and blockers. Never claim tests passed unless they were actually run. Use `None` where the line has no applicable item.

## 11. Persistent Development Instructions

- Read this root `AGENTS.md` before performing a task, and inspect relevant project files before modifying them.
- Preserve the existing Laravel, React, API, and database architecture.
- Never reset, truncate, destructively reseed, or overwrite the development MySQL database without explicit user approval.
- Do not run destructive migrations or modify existing development data.
- Before backend automated tests, verify the test configuration and use isolated SQLite testing; never run tests that reset or write to the development MySQL database.
- Preserve dependency lockfiles and avoid unnecessary package upgrades.
- Never automatically commit or push changes.
- Prefer small, targeted, verifiable changes.
- Implement only the currently requested task; do not add future features or unrelated work.
