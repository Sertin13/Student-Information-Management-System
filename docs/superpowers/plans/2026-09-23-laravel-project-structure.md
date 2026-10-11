# Laravel Project Structure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create the minimal Laravel 12 backend project skeleton required for Prompt 1, without implementing any domain module.

**Architecture:** The repository remains a monorepo with `backend/` reserved exclusively for Laravel and `frontend/` reserved for the later React client. This task installs only Laravel's standard skeleton and configures its safe example environment for the required MySQL API; migrations, resources, endpoints, and frontend code are intentionally deferred.

**Tech Stack:** PHP 8.2+, Composer 2, Laravel 12, MySQL configuration (no database access in this task).

**Spec:** `docs/superpowers/specs/2026-09-23-student-information-management-system-design.md`

## Global Constraints

- Create only `backend/`; do not create the frontend application until its designated prompt.
- Do not add Docker, email verification, refresh tokens, optional packages, domain modules, database migrations, or API endpoints.
- Laravel must be installed as version 12 and use its standard application structure.
- Configure `.env.example`; never commit credentials or a real `.env` file.
- The intended production database driver is MySQL; a live database is not required for Prompt 1.

## Review Focus

- Missing PHP executable: halt before scaffolding and report the prerequisite instead of generating a non-Laravel substitute.
- Missing Composer executable: halt before scaffolding and report the prerequisite instead of hand-creating framework files.
- Wrong framework major version: verify Composer resolves Laravel 12 before treating the scaffold as complete.
- Accidental domain implementation: verify no student, program, course, enrollment, grade, API, or frontend files are added.
- Leaked credentials: verify `.env` is ignored and only a placeholder-only `.env.example` is tracked.

---

### Task 1: Verify the Laravel scaffolding prerequisites

**Files:**
- Modify: none
- Test: command-line prerequisite check

**Interfaces:**
- Consumes: the Windows development environment.
- Produces: a PHP version at least `8.2` and an available Composer 2 executable for Task 2.

- [ ] **Step 1: Check PHP and Composer availability**

```powershell
php --version
composer --version
```

- [ ] **Step 2: Verify the expected version constraints**

```powershell
php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);"
composer --version | Select-String -Pattern 'Composer version 2\.'
```

Expected: both commands succeed, PHP is 8.2 or newer, and Composer reports major version 2.

- [ ] **Step 3: Stop with an actionable prerequisite report if either tool is unavailable**

Report: install PHP 8.2+ and Composer 2, ensure both are on `PATH`, then rerun this task. Do not hand-create a Laravel-like directory or install Docker as a workaround.

### Task 2: Scaffold the Laravel 12 backend directory

**Files:**
- Create: `backend/` and Laravel 12's standard files, including `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`, `artisan`, `composer.json`, `.env.example`, and `.gitignore`
- Test: `backend/artisan about`

**Interfaces:**
- Consumes: PHP and Composer confirmed by Task 1.
- Produces: `backend/artisan`, which accepts standard Laravel CLI commands for later prompts.

- [ ] **Step 1: Scaffold the required Laravel major version**

```powershell
composer create-project laravel/laravel backend "^12.0"
```

- [ ] **Step 2: Confirm Composer resolved Laravel 12**

```powershell
composer show --working-dir=backend laravel/framework --format=json
```

Expected: the installed package version starts with `v12.`.

- [ ] **Step 3: Confirm the framework starts without requiring a database connection**

```powershell
php backend/artisan about
```

Expected: exit code `0` and Laravel application details.

### Task 3: Configure the safe MySQL environment template and verify scope

**Files:**
- Modify: `backend/.env.example`
- Test: `backend/.env.example`, `git status --short`, `php backend/artisan about`

**Interfaces:**
- Consumes: Laravel's generated `.env.example`.
- Produces: a safe MySQL configuration template for the migration work in Prompt 2.

- [ ] **Step 1: Set only the required database placeholders in `backend/.env.example`**

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_information_system
DB_USERNAME=root
DB_PASSWORD=
```

- [ ] **Step 2: Verify real environment files remain ignored**

```powershell
git check-ignore backend/.env
git ls-files backend/.env
```

Expected: the first command identifies `backend/.env` as ignored; the second returns no path.

- [ ] **Step 3: Verify Prompt 1 contains only scaffold work**

```powershell
git status --short
Get-ChildItem -Recurse backend/app -File | Select-Object -ExpandProperty FullName
php backend/artisan about
```

Expected: no custom students, programs, courses, academic terms, offerings, enrollments, grades, controllers, requests, resources, migrations, or API routes exist; the Laravel command exits `0`.

- [ ] **Step 4: Commit the completed scaffold**

```powershell
git add backend
git commit -m "chore: scaffold Laravel backend"
```
