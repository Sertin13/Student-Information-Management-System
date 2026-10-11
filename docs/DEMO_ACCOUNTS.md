# Local Demo Accounts

These addresses are seeded by `backend/database/seeders/AcademicSeeder.php` and are intended for local testing. The seeder defines a common initial password; it is intentionally not reproduced here. Inspect `backend/database/seeders/AcademicSeeder.php:16` locally if needed. Existing account passwords may have changed since seeding.

| Role | Demo email |
|---|---|
| Admin | `admin@example.com` |
| Staff | `staff@example.com`, `registrar@example.com` |
| Instructor | `instructor@example.com` |
| Student | `student@example.com` |

`backend/database/seeders/DatabaseSeeder.php:14` invokes `AcademicSeeder`. The login endpoint validates the email/password pair and checks the stored password hash (`backend/app/Http/Controllers/Api/V1/AuthController.php:13-15`); the `User` model hashes assigned passwords (`backend/app/Models/User.php:48`).

## Read-only database verification

Verified against the configured `student_information_system` database on 2026-10-11. All five seeded email addresses exist with their expected roles. The Student account is linked to exactly one Student profile. The verification selected only role, email, and linked-profile count; it did not read or display password hashes, tokens, or other account secrets. No account or database data was changed.
