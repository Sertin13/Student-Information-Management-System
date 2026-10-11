# Local Recovery Audit — Laboratory 2 and 3 SIMS

Audit date: 2026-10-11
Scope: Read-only inspection of the extracted project. No dependencies were installed, no application source was changed, and no database command or connection was used.

## Summary

The project contains the expected Laravel API and React/Vite source trees, both lockfiles, and a `.env.example` for each application. The extracted dependency directories (`backend/vendor/` and `frontend/node_modules/`) are absent; both are intentionally excluded by `.gitignore`. The frontend has an existing local `.env`. The backend local `.env` and SQLite file are absent, also consistent with excluded/local runtime files. No missing frontend relative import targets were found by static path scan.

Two environment compatibility issues were identified: the installed Node.js is v25.9.0, while locked Vite 8.3.1 and @vitejs/plugin-react 6.1.1 declare Node `^20.19.0 || >=22.12.0` (so Node 25 meets the declared range); however npm is available only through `npm.cmd` because PowerShell blocks `npm.ps1`. More materially, PHP CLI is 8.2.12 but composer.lock includes `symfony/yaml` v8.1.6 requiring PHP >=8.4.1, despite composer.json declaring PHP ^8.2. This locked dependency appears incompatible with the detected PHP runtime and should be confirmed with Composer platform checks after dependencies are restored. A build/runtime check was not run because installed dependencies are absent.

## Project structure and Git exclusions

- Root contains `backend/`, `frontend/`, `docs/`, project instructions, and Phase 9 CSV/PowerShell artifacts.
- Backend source/configuration is present under `backend/app/`, `bootstrap/`, `config/`, `database/`, `routes/`, `tests/`, and `resources/`; Laravel entry points, Composer manifest/lock, PHPUnit config, and Vite config are present.
- Frontend source is present under `frontend/src/` (API client, components, context, layout, pages, routes, services, utilities), plus `public/`, Vite config, HTML entry point, manifest, and lockfile.
- Root `.gitignore` excludes `.worktrees/` and `.superpowers/`.
- `backend/.gitignore` excludes `vendor/`, `.env`, PHPUnit caches, generated public assets, and runtime storage artifacts. These are local/generated files, not missing tracked source. `backend/.env.example` is present.
- `frontend/.gitignore` excludes `node_modules/`, `dist/`, and `*.local`; installed modules and build output are expected to be absent from an extracted source archive. `frontend/.env` and `.env.example` are present.

## Findings by category

### Missing installed dependencies

- `backend/vendor/` is absent (`backend/.gitignore` excludes it). Composer dependencies are therefore not installed; `backend/composer.lock` is present (Laravel 12.69.2, Sanctum 4.3.3, PHPUnit 11.5.56 among locked packages).
- `frontend/node_modules/` is absent (`frontend/.gitignore` excludes it). npm dependencies are therefore not installed; `frontend/package-lock.json` is present (lockfile v3; Vite 8.3.1 and React plugin 6.1.1).
- Do not classify source imports as broken solely because these ignored dependency folders are absent.

### Missing environment/configuration files

- `backend/.env` is absent; `backend/.env.example` is present. This is an expected local configuration file excluded by Git. The example config points to MySQL database `student_information_system`; no database actions were taken.
- `frontend/.env` and `frontend/.env.example` both exist and set `VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1`.
- `backend/database/database.sqlite` is absent. It is a local runtime/test artifact and is not required for the PHPUnit suite configured for in-memory SQLite.
- No required tracked application configuration file was found missing in the inspected trees.

### Missing application source files

- No missing frontend local import target was found by static relative-import path scan.
- Laravel API routes, controllers, requests, resources, models, role middleware, provider, migrations, factories, seeders, and tests are present in their expected directories.
- No definite missing application source file was established from the available static evidence.

### Existing code or import errors

- All scanned relative imports in `frontend/src/` resolve to an existing file. External imports correspond to dependencies listed in `frontend/package.json`; resolution/build remains unverified until `node_modules` is installed.
- Frontend Vite config enables React and Tailwind Vite plugins; `frontend/src/index.css` imports Tailwind CSS. The required packages are declared in the manifest and lockfile.
- Backend `phpunit.xml` explicitly sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. `backend/config/database.php` supports both SQLite and MySQL. This is configuration inspection only, not a test run.
- No static source/import error was confirmed. Runtime and build errors remain unverified because dependencies are absent.

### Environment/tool compatibility problems

- PHP CLI: 8.2.12; Composer: 2.9.3; Node.js: v25.9.0; npm: 11.12.1 (via `npm.cmd`); MySQL CLI: 9.4.0.
- `npm` resolves to `npm.ps1`, but PowerShell execution policy blocks that script. `npm.cmd --version` works, so use `npm.cmd` for future npm commands in this shell or adjust the environment policy outside this audit.
- Composer lock includes `symfony/yaml` v8.1.6 requiring PHP >=8.4.1, which exceeds the detected PHP 8.2.12 runtime. The project manifest allows PHP ^8.2; run a Composer platform compatibility check after restoring vendor to confirm whether the locked dependency graph blocks installation/execution.
- Vite and its React plugin declare Node `^20.19.0 || >=22.12.0`; detected Node v25.9.0 satisfies this declared engine range.
- PHP PDO drivers include MySQL and SQLite (`pdo_mysql`, `pdo_sqlite`); this confirms driver availability only and does not imply database access was attempted.

## Recommended repair order

1. Resolve the PHP/composer.lock platform mismatch: determine the intended PHP version or adjust the dependency constraint/lock through an authorized dependency maintenance task. Preserve the lockfile until the compatibility decision is made.
2. After PHP compatibility is settled, install backend dependencies from the existing lockfile; prepare `backend/.env` locally from `.env.example` only when authorized/needed, preserving current environment-specific values.
3. Install frontend dependencies from `frontend/package-lock.json` with `npm.cmd ci`; retain the lockfile.
4. Perform non-destructive static/build verification and backend automated tests only after confirming their SQLite in-memory configuration; never run database resets or use the development MySQL database for tests.
5. Verify actual API connectivity and role-specific behavior only in a separately authorized verification phase. No repair or verification requiring application execution was performed in this audit.

## Verification limits

This audit inspected files, paths, package declarations/lock metadata, command availability, and static relative imports. It did not install packages, run a frontend build, execute PHPUnit, launch Laravel, connect to MySQL, run migrations/seeders, or modify application/environment/database state.

## Frontend lint and dependency follow-up (2026-10-11)

- Confirmed installed `source-map-js` is **1.2.2**, matching `frontend/package-lock.json`. No dependency synchronization was needed.
- Resolved the ten lint warnings without rule suppression: moved `useAuth`/context exports into `frontend/src/context/authContext.js` so the provider module exports only its React component; initialized `StudentForm` state from its student prop and keyed the form by record; moved initial student and instructor data fetches into async effect work; replaced effect-triggering loader calls in `ReferenceDataPage` with an async initial fetch; made its loader stable with explicit hook dependencies; replaced side-effect ternaries with `if/else`; and made the Add Grade button conditional on `canAdd` as intended for an empty ungraded-enrollment list.
- The `canAdd` condition hides the Add button when there are no ungraded enrollments. Existing Admin/Staff role gating remains in place, consistent with the backend grade route authorization.
- Verification: `npm.cmd run lint` passed with zero warnings; `npm.cmd run build` passed; `npm.cmd audit --json` reported **0 vulnerabilities** (63 production, 24 development, 75 optional dependencies); installed `source-map-js` version check returned **1.2.2**.
- No backend files, environment files, database state, package manifests, or lockfiles were changed. No remaining frontend lint warnings or dependency vulnerabilities were identified.

## PHP and Composer compatibility follow-up (2026-10-11)

### Exact locked requirements

`backend/composer.json` declares PHP `^8.2`, but this does not make the current lock compatible with PHP 8.2. The exact package originally identified is `symfony/yaml` v8.1.6, whose locked `require.php` is `>=8.4.1`. Inspection of every locked production and development package shows this is not the only blocker: `symfony/clock` v8.1.0, `symfony/css-selector` v8.1.6, `symfony/event-dispatcher` v8.1.5, `symfony/string` v8.1.7, and `symfony/translation` v8.1.5 also require `>=8.4.1`. These Symfony 8.1 packages set the minimum PHP requirement for the complete locked graph at PHP 8.4.1. `laravel/pint` v1.32.1 separately requires `^8.3.0`, which is satisfied by PHP 8.4.1. Other package constraints are compatible with 8.4.1; e.g. `nette/schema` v1.3.6 permits PHP 8.1–8.5 and `nette/utils` v4.1.5 permits 8.2–8.5. The lock's root platform declaration is `php: ^8.2`, which is broader than some selected package requirements.

### Local PHP and Composer evidence

- `composer` is Composer 2.9.3 (2025-12-30).
- Composer's launcher at `C:\ProgramData\ComposerSetup\bin\composer.bat` invokes the bare `php` command. In this shell, `php` resolves to `C:\Xammp web\php\php.exe`; Composer reports PHP 8.2.12 at that exact path. Its loaded ini is `C:\Xammp web\php\php.ini`.
- The active PHP installation is an XAMPP installation rooted at `C:\Xammp web` (contains `apache`, `htdocs`, `mysql`, `php`, `xampp-control.exe`, and related XAMPP components). Its PHP CLI and the nested `C:\Xammp web\php\windowsXamppPhp\php.exe` are both PHP 8.2.12.
- `C:\xampp\php\php.exe` does not exist. Although `C:\xampp\php` appears on PATH, no executable was found there. No Laragon, standalone PHP, or other PHP executable was found in the inspected common installation roots. Therefore no compatible PHP 8.4+ executable is currently available in the locations checked. This is a bounded local path inspection, not a guarantee that no binary exists in an uninspected custom directory.
- Composer's package platform listing confirms its active PHP platform is 8.2.12. `composer validate --no-check-publish --no-check-lock` reports composer.json valid; that command is not a dependency platform-compatibility check and does not disprove the locked package requirements.

### Recommendation

Preserve `backend/composer.lock` and use a separate PHP 8.4.1-or-newer CLI installation to run Composer. PHP 8.4.1 is the minimum established by every locked package; a maintained PHP 8.4 patch release or newer supported branch is preferable. A separate installation can coexist without replacing XAMPP's PHP: invoke its `php.exe` by absolute path for Composer, or use a temporary per-process PATH/Composer PHP override after compatibility is independently verified. Do not change the system PATH or replace the current XAMPP installation as part of this recovery. The nested XAMPP PHP copy is still 8.2.12 and is not a workaround. No compatible alternate executable was found locally, so one would need to be provisioned in a separate authorized setup step before installing backend dependencies.

No Composer install/update, PHP installation change, environment/PATH change, source edit, or database operation was performed.

## PHP 8.4+ side-by-side Windows CLI preparation (2026-10-11)

### Local architecture and build

- Windows reports x64 and the current PHP process is 64-bit.
- XAMPP PHP is x64, Visual C++ 2019 / VS16, thread-safe (TS/ZTS). Its CLI binary is `C:\Xammp web\php\php.exe`; Composer 2.9.3's `composer.bat` invokes bare `php`, so Composer currently uses that executable from PATH. The launcher can be bypassed by invoking `composer.phar` with a chosen PHP executable.
- The separate PHP is intended only for CLI/Composer. It does not need to match XAMPP's TS build; use the official x64 Non Thread Safe (NTS) ZIP for CLI. Keep it outside XAMPP (recommended `C:\tools\php84`) and do not add it to global PATH or change XAMPP configuration.

### Complete locked PHP and extension constraints

A scan of `require` entries for all locked production and dev packages shows the hard PHP floor is 8.4.1 due to Symfony 8.1 packages (see preceding section). The other newer constraints include `laravel/pint` v1.32.1 requiring `^8.3.0`; `dragonmantank/cron-expression` v3.6.0 allows `^8.2|^8.3|^8.4|^8.5`; and `nette/schema` v1.3.6 / `nette/utils` v4.1.5 cap support at PHP 8.5. Thus both PHP 8.4 (>=8.4.1) and PHP 8.5 are admitted by the inspected locked package PHP constraints; PHP 8.5 is not excluded by these package constraints, while PHP 8.6 is not admitted by the `nette/*` caps. Recommended baseline: latest stable PHP 8.4 x64 NTS ZIP, which meets the exact minimum and avoids selecting a newer minor than needed. Recheck all package constraints if composer.lock changes.

Locked `require` declarations collectively require these PHP extensions: ctype, dom, fileinfo, filter, hash, iconv, json, libxml, mbstring, openssl, pcre, phar, session, tokenizer, xml, and xmlwriter. Some are core/bundled. For this Laravel/MySQL app, also enable/check `curl`, `pdo`, and `pdo_mysql` for HTTP and the configured database driver; `zip` is useful for Composer distribution extraction and archive support. Current PHP has the required core modules plus curl and pdo_mysql, but its module set is not evidence about the separate PHP build. Composer may additionally report platform extensions at runtime, so confirm using `php -m` and `composer check-platform-reqs` after installation (the latter requires vendor to be installed).

### Official binary and runtime

Use the official PHP for Windows download page, PHP 8.4: https://windows.php.net/download/ (select PHP 8.4, VS17 x64 Non Thread Safe ZIP). As of this audit, PHP.net lists PHP 8.4.26 as a current security release. The PHP download guidance recommends x64 and states Windows builds require the Microsoft Visual C++ Redistributable for Visual Studio 2015–2022 matching architecture. PHP 8.4 is built with VS17/Visual Studio 2022. The x64 VC++ 2015–2022 runtime is already registered on this computer as installed (v14.50.35719.00), so a separate runtime install appears unnecessary; if the downloaded binary reports a missing runtime DLL, recheck/install the official x64 redistributable.

### Safe installation and verification procedure (not executed)

1. Manually visit the official PHP 8.4 downloads page and select the current stable **VS17 x64 Non Thread Safe** ZIP (not debug pack, SDK, source, or TS build). Confirm the selected archive label/version and published SHA-256 on the official page before use. This manual confirmation matters because PHP point releases and download labels change.
2. Extract the archive into a new `C:\tools\php84` directory. Do not extract over or copy files into `C:\Xammp web\php`.
3. Copy `C:\tools\php84\php.ini-development` to `C:\tools\php84\php.ini`. In that file set `extension_dir="C:\tools\php84\ext"` and enable at least `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `zip`, `dom`, `xml`, and `xmlwriter` if present as commented extension directives. Keep the existing XAMPP `php.ini` untouched. For CLI-only use, do not configure Apache module loading.
4. Verify directly, without changing PATH: `& 'C:\tools\php84\php.exe' -v`; `& 'C:\tools\php84\php.exe' --ini`; `& 'C:\tools\php84\php.exe' -m`. Confirm PHP is at least 8.4.1, x64/NTS, and required modules are loaded. Check that `php -v` still reports the original XAMPP PHP 8.2.12 in a fresh shell.
5. To run Composer with the separate runtime, bypass `composer.bat` and invoke the installed Composer PHAR directly, e.g. from the project root: `& 'C:\tools\php84\php.exe' 'C:\ProgramData\ComposerSetup\bin\composer.phar' --version`; for backend commands append `--working-dir='C:\New folder\Laboratory-2-and-3-main\backend'`. Composer scripts use the PHP process that launched Composer. Before any dependency installation, run a read-only platform check if possible; do not use `--ignore-platform-reqs`. This audit did not run install/update.

### Remaining manual confirmations

- The official PHP downloads page and checksum must be checked at installation time; the current recommended point release may change.
- Confirm the computer remains x64 and that the selected ZIP is explicitly VS17 x64 NTS.
- The VC++ runtime registry entry indicates the x64 redistributable is installed, but actual PHP startup is the definitive test.
- PHP 8.5 also satisfies the package constraints scanned here, but PHP 8.4 is the conservative recommendation. This is Composer metadata compatibility, not a guarantee that every project runtime behavior has been tested on PHP 8.4.
- No PHP was installed, no XAMPP/PATH/environment/dependency files were changed, no Composer dependencies were installed, and no database operation was performed.

## Standalone PHP 8.4.26 CLI configuration and Composer check (2026-10-11)

### Runtime and configuration

- Verified `C:\tools\php84\php.exe`: PHP 8.4.26 CLI, x64, NTS (thread safety disabled), Visual C++ 2022 build. Existing XAMPP PHP was not changed.
- `php.ini-development`, `php.ini-production`, and `ext\` were present; `php.ini` was absent. Created `C:\tools\php84\php.ini` by copying `php.ini-development`, then set `extension_dir` to `C:\tools\php84\ext` and enabled only extension entries whose DLLs were present.
- Enabled DLL extensions: `curl`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, and `zip`. Verified these load. Built-in/core modules `ctype`, `dom`, `filter`, `hash`, `iconv`, `json`, `libxml`, `pcre`, `Phar`, `session`, `tokenizer`, `xml`, and `xmlwriter` also load.
- `C:\tools\php84\php.exe --ini` reports `C:\tools\php84\php.ini`; `-m` lists required modules. This config is for the standalone CLI only; it does not configure or replace XAMPP Apache PHP.

### Explicit Composer invocation and platform verification

Composer 2.9.3 is installed as `C:\ProgramData\ComposerSetup\bin\composer.phar`; its normal `composer.bat` launches whichever `php` is on PATH. To force the standalone binary without changing PATH, invoke the PHAR directly:

```powershell
& 'C:\tools\php84\php.exe' 'C:\ProgramData\ComposerSetup\bin\composer.phar' check-platform-reqs --lock --working-dir='C:\New folder\Laboratory-2-and-3-main\backend'
```

Executed the above non-installing check. It exited 0; all locked requirements passed, including PHP 8.4.26 and the locked core extensions. No vendor directory was needed because `--lock` checks `composer.lock`. Composer version invoked the same way reported PHP 8.4.26 at `C:\tools\php84\php.exe`. Application-level MySQL and SQLite drivers `pdo_mysql` and `pdo_sqlite` are loaded, although they are not listed as requirements in the lock's platform section.

No Laravel dependencies were installed, no Composer update ran, and no database or application source was accessed/changed. Only `C:\tools\php84\php.ini` and this audit report were changed.

## Laravel dependency restoration and bootstrap verification (2026-10-11)

### Pre-install script safety review

Reviewed `backend/composer.json` and the lockfile before install. The lock contains 112 packages; Composer reported 112 installs, 0 updates, and 0 removals. Development dependencies are included. The install event `post-autoload-dump` runs Laravel's `package:discover`; the `post-create-project-cmd` contains key generation, SQLite file creation, and `artisan migrate --graceful`, but Composer `install` does not run the create-project event. No `post-install-cmd` is defined. `post-update-cmd` is associated with update and was not run. Therefore the install lifecycle did not invoke migrations, seeders, DB commands, or `.env` creation.

### Installation result

Ran Composer install from the backend project using the explicit standalone runtime and existing lock:

```powershell
& 'C:\tools\php84\php.exe' 'C:\ProgramData\ComposerSetup\bin\composer.phar' install --working-dir='C:\New folder\Laboratory-2-and-3-main\backend' --no-interaction --prefer-dist
```

Installation completed successfully, including `require-dev`. `backend/vendor/` and `backend/vendor/autoload.php` now exist; Composer's installed metadata lists all 112 locked packages, including Laravel Framework v12.69.2 and PHPUnit v11.5.56. `backend/composer.lock` remains at its original length (312426 bytes) and content hash (`70af00ccb7a092da6a20f7b7fcc82c00`). `backend/.env` remains absent and was not created. Composer's initial sandboxed attempt could not write ZIPs under vendor; after permission was granted for the authorized install, rerunning the same command completed normally.

### Verification

All commands below invoked `C:\tools\php84\php.exe` explicitly; XAMPP PHP and global PATH were not changed.

- `composer check-platform-reqs` against installed vendor packages: passed, exit 0 (PHP 8.4.26 and all required platform entries satisfied).
- `php artisan --version`: passed; Laravel Framework 12.69.2.
- `php artisan route:list --path=api --except-vendor`: passed; listed 46 API routes.
- Confirmed `vendor/autoload.php`, PHPUnit binary, and vendor directory exist; confirmed `.env` is still absent.
- No migration, seeder, database command, or test suite was run. No application source, Composer manifest/lock, XAMPP installation, or development database was modified/accessed. Dependency files were restored only under ignored `backend/vendor/`.

## Local Laravel environment and MySQL readiness check (2026-10-11)

### Environment file

- Before setup, `backend/.env` did not exist. Copied `backend/.env.example` to create it; no existing values were overwritten.
- Verified without printing secrets: `APP_ENV=local`, `APP_DEBUG=true`, `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, and `DB_DATABASE=student_information_system` match the template. `DB_USERNAME` and `DB_PASSWORD` remain the template values (`root` and blank). A new APP_KEY was generated with `C:\tools\php84\php.exe artisan key:generate`; the key itself is intentionally not recorded.
- The standalone PHP 8.4.26 CLI has required extensions loaded, including openssl, mbstring, pdo_mysql, pdo_sqlite, fileinfo, curl, json, tokenizer, xml, dom, and ctype.

### Read-only database connectivity result

A PDO connection to the configured MySQL host and port reached MySQL, but authentication failed: `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)`. Therefore the configured credential pair is not accepted by the running local server. The schema existence query was not reached; whether `student_information_system` exists remains unverified. No credentials were changed, and no records or schema were read or modified beyond the attempted login (no SQL was executed because connection establishment failed).

### Laravel bootstrap and readiness

- `C:\tools\php84\php.exe artisan about --only=environment` succeeded and reported Laravel 12.69.2, PHP 8.4.26, local environment, and debug enabled.
- `C:\tools\php84\php.exe artisan route:list --path=api --except-vendor` succeeded and listed 46 routes.
- Backend configuration/bootstrap is ready, but the application is **not ready for database-backed local use** until valid local MySQL credentials are supplied and read-only connectivity/schema existence can be confirmed. Do not create the database or alter credentials automatically.
- No migrations, seeders, database changes, XAMPP changes, PATH changes, or source edits were performed. Changed files for this step: new `backend/.env` (contains local secret material; do not commit) and this report.

## MySQL authentication diagnosis (read-only, 2026-10-11)

### Configuration and server identity

- Laravel database config selects `mysql` and uses the usual PDO MySQL connection. The current `backend/.env` target is `127.0.0.1:3306`, schema `student_information_system`, username `root`, with no password configured. The app settings match the template, but the credential pair is inconsistent with the server's authentication policy because the previous connection returned MySQL error 1045 (`root`@`localhost`, no password).
- MySQL/MariaDB CLI tools found: `C:\Program Files\MySQL\MySQL Server 9.4\bin\mysql.exe` reports MySQL Community Server 9.4.0; `C:\Xammp web\mysql\bin\mysql.exe` reports MariaDB 10.4.32. The standalone `mysqld.exe` is MySQL 9.4.0; XAMPP's is MariaDB 10.4.32.
- Port 3306 is listening on all IPv4 interfaces. A read-only MySQL protocol greeting to `127.0.0.1:3306` identifies the endpoint as **MySQL 9.4.0**, so the responding server is from the standalone MySQL family, not XAMPP MariaDB. Windows service `MySQL94` is running and configured to launch `C:\Program Files\MySQL\MySQL Server 9.4\bin\mysqld.exe` with `C:\ProgramData\MySQL\MySQL Server 9.4\my.ini`; `MySQL80` is stopped. The 9.4 configuration sets port 3306 and its ProgramData data directory.
- XAMPP's `C:\Xammp web\mysql\bin\my.ini` also configures port 3306 and its own XAMPP data directory. Do not start both database servers on the same port without an intentional port plan. Inspected files did not explicitly set `bind-address`, `skip-grant-tables`, or an authentication override. No authentication changes were made.

### Existing credential sources and read-only query status

- MySQL Workbench has one profile named `Local instance MySQL94` with host `localhost`, port 3306, and user `root`, matching the endpoint. The XML profile contains no password key. A MySQL login-path file (`C:\Users\Mae\.mylogin.cnf`) is absent. No working local credential source could be established without attempting passwords or retrieving secrets.
- No SQL was issued in this diagnostic turn because no known working credential was available. Server identity was read from the unauthenticated protocol greeting only. The prior empty-password attempt was rejected; schema existence and expected application tables remain unverified.
- Service/process inspection has a limitation: WMI/CIM service enumeration and tasklist service mapping were denied by Windows. `netstat` reports a `mysqld` listener PID that does not match `sc queryex MySQL94`'s service PID, so exact listener-process PID attribution could not be confirmed, although the protocol greeting identifies the endpoint as MySQL 9.4.0 and the 9.4 service/config are present and running.

### Required manual action

Obtain the intended local MySQL 9.4 `root` password or a database username/password from the authorized database administrator/owner, then update only the local `backend/.env` credentials with that approved value. Do not guess or reset credentials. Afterward, repeat read-only `SELECT 1`, schema-existence, and table-name checks; do not create the schema or run migrations as part of this diagnosis.

No database accounts, credentials, configuration, records, or application source were changed. Only this audit report was updated.

## Migration and demo-seeder readiness review (2026-10-11)

### Connection and current schema status

- Inspected `backend/.env` without printing secrets. The target remains MySQL at `127.0.0.1:3306`, database `student_information_system`, user `root`; a password is not configured. Laravel's MySQL connection uses PDO MySQL as expected.
- Retried authentication using only those configured credentials. It failed with MySQL error 1045, `Access denied for user 'root'@'localhost' (using password: NO)`. No password guesses or account changes were made. No schema or table queries could be run, so the existence of tables and the `migrations` history table remain unverified despite the database having been created manually.

### Migration order, relationships, and constraints

The 12 migration files are timestamp-ordered. Base tables (`roles`, `users`, cache, jobs) precede academic tables. Same-timestamp filenames sort dependencies first: `programs` before `students`, `course_offerings` before `enrollments`; courses and academic terms precede offerings; enrollments precede grades. Sanctum `personal_access_tokens` is last.

- `users.role_id` references `roles` and nulls on role deletion. `students.user_id` optionally references a user, is unique, and nulls on user deletion; `students.program_id` references programs and restricts deletion.
- `course_offerings` references courses and academic terms with restricted deletion; optional instructor references `users` and nulls on deletion.
- `enrollments` references students and course offerings with restricted deletion and has a unique student/offering pair. `grades.enrollment_id` is unique and restricts deletion of a graded enrollment.
- Additional unique constraints cover role names, user emails, program codes, student numbers, course codes, academic year/semester, course/term/section, failed-job UUIDs, and Sanctum token values. Student user identity is also unique. Sessions contain an indexed nullable user_id without a declared FK; Sanctum's polymorphic tokenable columns do not declare a normal FK.
- Migration `up()` methods create tables; their `down()` methods drop their corresponding tables. No data migration or table alteration is present. A normal `artisan migrate` does not intentionally reset tables, but it is only safe against a confirmed empty schema (or a schema whose migrations history accurately matches). If an untracked/pre-existing table collides, `Schema::create` can fail; MySQL DDL may leave earlier tables created. Since authentication prevented inspection, this database is **not yet cleared for migration execution**. Do not use `migrate:fresh` or rollback/drop commands.

### Seeder and factory review

- `DatabaseSeeder` calls `AcademicSeeder`; `RoleSeeder` is empty and is not called. `AcademicSeeder` itself creates four roles and five development accounts, three programs, 100 students, 20 courses, two academic terms, 20 offerings, and 200 enrollments with 200 grades. The README documents five demo logins using the shared demo password `password`; `User` hashes assigned passwords via its model cast. This is a substantial dataset suitable for a Laboratory demonstration on a newly initialized empty database.
- The seeder uses `firstOrCreate` for most rows, so it avoids duplicate inserts when rerun, but it is not wholly read-only/idempotent: it unconditionally updates the first seeded student to link the demo student user. It may therefore alter an existing student record. Run only on the intended fresh demonstration dataset after authorization and schema checks; preserve existing records.
- `UserFactory` has populated default fields. The other eight factories (role, program, student, course, academic term, course offering, enrollment, grade) have empty definitions and are not ready for general factory-generated records without additional setup. This does not prevent the explicit demo seeder from constructing its data.

### Readiness decision

Migration dependency ordering and table definitions appear coherent for a newly created, empty database, and the explicit academic seeder contains the documented Laboratory demonstration accounts/data. However, current DB authentication fails with the configured blank root password, and table/migration-history state cannot be checked. Therefore migration/seeder readiness is conditional, not verified: first supply the authorized local MySQL credentials in `backend/.env`, run read-only server/schema/table checks, and confirm the schema is empty. No migration, seeder, record query, or data change was performed in this review; only this audit report was updated.

## Laravel MySQL connection recheck with `laravel_app` request (2026-10-11)

- Read `backend/.env` without outputting its password or APP_KEY. Laravel has no cached config file (`bootstrap/cache/config.php` absent), so `config:clear` was unnecessary and not run. Bootstrapping the application confirmed the effective connection is `mysql`, host `127.0.0.1`, port `3306`, database `student_information_system`, and that a password is configured.
- Configuration discrepancy: the username currently stored in `.env` is `laraval_app` (spelled `laraval`), while the requested account name is `laravel_app`. No value was changed.
- A read-only connection attempt using Laravel's effective config failed with MySQL error 1045: `Access denied for user 'laraval_app'@'localhost' (using password: YES)`. The credential value was not printed. Authentication did not reach any SQL query, so no table list or emptiness result is available.
- Database is not verified empty, and migrations cannot be approved to proceed safely until the username discrepancy and authentication are resolved by the owner and a read-only table listing confirms the schema state. No database records, credentials, or MySQL configuration were modified; no migrations/seeding were run. Only this report was updated.

## MySQL authentication retry after expected username correction (2026-10-11)

- Inspected `backend/.env` without displaying secrets. The configured database target remains MySQL at `127.0.0.1:3306`, schema `student_information_system`, with a password configured. The configured username is still `laraval_app`, not the expected exact value `laravel_app`.
- Laravel has no cached configuration file (`bootstrap/cache/config.php` is absent), so no cache clear was necessary. Explicit application bootstrap confirmed Laravel loads `laraval_app` from the current environment file.
- Retried a connection using Laravel's current configured credentials only (no credential substitution or guessing). It failed with MySQL error 1045: `Access denied for user 'laraval_app'@'localhost' (using password: YES)`. No table query ran; the database's emptiness and migration safety remain unverified.
- No credential, cache, application source, or database state was changed. Migration/seeder execution remains blocked until the local username is corrected to the authorized account and read-only table inspection confirms schema state. Only this audit report was updated.

## MySQL authentication retry after username correction (2026-10-11)

- `backend/.env` now contains exact `DB_USERNAME=laravel_app`; the value was verified without reading or displaying the password. The target is `mysql` at `127.0.0.1:3306`, database `student_information_system`, and a password is configured.
- `bootstrap/cache/config.php` is absent, so Laravel configuration cache clearing was unnecessary. Explicit Laravel bootstrap confirmed it loads username `laravel_app` and the expected host, port, and database.
- A read-only connection using Laravel's configured values still failed with MySQL error 1045: `Access denied for user 'laravel_app'@'localhost' (using password: YES)`. No password was guessed or displayed. No metadata query ran; table names, database emptiness, and migration readiness remain unverified.
- No credentials, cache, or database state were changed. Only this audit report was updated. The user task text ended at step 8, `Determine whether the`, so any additional requested determination was unavailable.

## Successful Laravel MySQL connection and initial-schema readiness (2026-10-11)

- Verified `backend/.env` has exact `DB_USERNAME=laravel_app`; password and APP_KEY were not displayed. Laravel config cache is absent, so cache clearing was unnecessary. Explicit Laravel bootstrap loaded the MySQL driver, host `127.0.0.1`, port `3306`, schema `student_information_system`, and expected username.
- Read-only Laravel PDO connection succeeded. Server identity is MySQL 9.4.0 and `DATABASE()` returned `student_information_system`.
- Read-only `information_schema.TABLES` query returned zero tables. The target database is empty; no existing tables conflict with the migrations.
- `SHOW GRANTS FOR CURRENT_USER()` returned `USAGE ON *.*` and `ALL PRIVILEGES ON student_information_system.*` for the configured user. This is sufficient privilege evidence for initial schema creation, subject to server policy.
- Compared the empty schema with the migration definitions: the 12 migration files create 17 application/framework tables (`roles`, `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `programs`, `students`, `academic_terms`, `courses`, `course_offerings`, `enrollments`, `grades`, `personal_access_tokens`). Laravel also creates its `migrations` tracking table when applying them. None currently exists.
- **Readiness:** database is ready for the initial normal `artisan migrate` sequence: it is empty, the migration order/foreign-key prerequisites were reviewed earlier, and the DB user has database-level privileges. Migrations and seeders were not executed in this verification.
- No database records, credentials, or source files were changed. Only this report was updated.

## Initial migration preflight (2026-10-11)

- Re-read the root instructions and audit history. Reviewed all 12 migration files before any database action. Each `up()` creates its own table; no `DROP`, `TRUNCATE`, raw SQL, external connection, or unexpected destructive operation was found. `down()` methods drop their corresponding tables as expected, but none were run. Table/FK ordering is coherent: roles before users, programs before students, courses/terms before offerings, offerings before enrollments, and enrollments before grades. Migration definitions use Laravel schema-builder constructs; no incompatible raw MySQL syntax was found.
- Final read-only Laravel bootstrap check, using `C:\tools\php84\php.exe`, confirmed the effective database is exactly `student_information_system`; an `information_schema.tables` query returned zero tables. This confirms the schema is empty at preflight. No credentials or secrets were output.
- **Execution blocked:** this request explicitly directs running `artisan migrate --force` and `artisan migrate`, but its restrictions also explicitly prohibit running `migrate`. No migration or seeder was run, so no tables or migration records were created. Need clarification that normal `artisan migrate` is permitted despite that restriction before proceeding.

## Authorized migration request recheck (2026-10-11)

- Reconfirmed via Laravel bootstrap that the configured database is exactly `student_information_system`. A read-only `information_schema.tables` query returned zero tables; no migrations are recorded because no application tables exist.
- Counted the current migration files: 12. Rechecked for destructive or external database operations. `up()` methods contain no drop/truncate/delete/raw SQL/external connection references; matches for `Schema::dropIfExists` are confined to the corresponding rollback `down()` methods. No rollback was run.
- **Migration not executed:** this request authorizes `artisan migrate --force` but also includes the direct restriction “Never execute migrate, migrate, migrate, migrate, or db.” That command-level prohibition conflicts with the requested operation, so I did not run `migrate` or `migrate:status`. No seeders ran and database state remains empty.

## Initial MySQL migration execution (2026-10-11)

- Immediately before migration, Laravel bootstrap confirmed the target was exactly `student_information_system`; read-only `information_schema.tables` returned zero tables. Confirmed there are 12 existing migration files. Their `up()` methods create tables only; destructive `Schema::dropIfExists` statements are confined to rollback `down()` methods. No external database references or raw SQL were found.
- Executed the explicitly authorized normal command from `backend/`: `C:\tools\php84\php.exe artisan migrate --force`. All 12 migrations completed successfully in batch 1. No seeders were run.
- Read-only verification confirmed the target remains `student_information_system` and contains 18 tables: `academic_terms`, `cache`, `cache_locks`, `course_offerings`, `courses`, `enrollments`, `failed_jobs`, `grades`, `job_batches`, `jobs`, `migrations`, `password_reset_tokens`, `personal_access_tokens`, `programs`, `roles`, `sessions`, `students`, and `users`.
- `C:\tools\php84\php.exe artisan migrate:status` reports all 12 migration files as `[1] Ran`. No migration errors or remaining pending migrations were reported. No destructive recovery, credential changes, seeding, or unrelated database operations occurred.

## Seeder and factory pre-seeding review (2026-10-11)

### Seeder order and expected fresh-database rows

- `DatabaseSeeder::run()` calls only `AcademicSeeder`. `RoleSeeder` is empty and is not called.
- On a truly empty application schema, `AcademicSeeder` is expected to create 4 roles (`Admin`, `Staff`, `Student`, `Instructor`), 5 users (admin, staff, registrar, instructor, student demo accounts), 3 programs, 100 students, 20 courses, 2 academic terms, 20 course offerings, 200 enrollments, and 200 grades. These are code-derived expected counts, not observed database counts.
- The user accounts all receive one of the four created roles. Generated IDs are used for foreign-key relationships. The loops produce distinct role names, emails, program codes, student numbers, course codes, year/semester pairs, offering course/term/section keys, student/offering enrollment pairs, and one grade per enrollment. On a fresh empty database, these values are consistent with the migration unique constraints and relationships; each offering gets 10 distinct students, matching its capacity. No invalid role assignment or duplicate-key conflict is apparent from the seed logic.

### Student profile linkage risk

- The seeder unconditionally updates the first student number (`2026-00001`) to use the `student@example.com` user's ID after `firstOrCreate`. On first seed into an empty database, this gives the demo student account one linked profile as intended. On rerun, it will overwrite a different existing `user_id` on that student. If the demo user ID is already linked to a different student, the unique `students.user_id` constraint can instead make the update fail.
- Login authentication itself uses the user's email/password/role and is not changed by the profile link. Student self-service endpoints (`my/student`, `my/enrollments`, `my/grades`) and the student authorization check for `students/{student}/academic-record` depend on `students.user_id`. A missing or reassigned link can leave the demo student without the intended profile, show another linked profile's self-service data, or deny access to the previously linked student's academic record. The other 99 seeded student profiles are not linked by this seeder.

### Factories

- `UserFactory` defines usable randomized user attributes. The other eight factories (role, program, student, course, academic term, course offering, enrollment, grade) have empty `definition()` arrays and are not ready for ordinary factory-created records without further work.

### Database state and readiness

- The preceding migration verification established 18 tables and 12 completed migrations, and no seeder was run during migration. The requested current row-count, duplicate, invalid-role, and orphan checks could not be completed: the read-only inspection command was rejected before execution. Therefore current application-table emptiness and live integrity counts are **unverified**; no claim is made that the database is currently empty.
- The seeder appears suitable for a first run on the empty schema the user confirmed before migration, subject to reconfirming application-table counts are zero immediately before seeding. Recommend correcting the unconditional student-link update (make it assign only when safe, with explicit conflict handling) before reruns or any run against non-empty data. No seeders, factories, application code, credentials, or records were changed in this review.

## Corrected Laravel seeder audit and live read-only checks (2026-10-11)

- Ran a temporary audit script from `backend/` with `C:\tools\php84\php.exe`. It bootstrapped Laravel using `__DIR__`-based paths and imported `Illuminate\Support\Facades\DB`; the script was removed after execution. Laravel's effective database was exactly `student_information_system`.
- Read-only counts confirmed all nine seed data tables are empty: roles 0, users 0, programs 0, students 0, courses 0, academic_terms 0, course_offerings 0, enrollments 0, grades 0. Roles are currently empty; the demo student user is not present, and student `2026-00001` has no linked user. No duplicate-key groups, invalid user-role assignments, or orphaned foreign-key relationships were found (all checked counts 0).
- Re-inspected `DatabaseSeeder`, `AcademicSeeder`, `RoleSeeder`, and all nine factories. `DatabaseSeeder` invokes only `AcademicSeeder`; `RoleSeeder` is empty and unused. On these empty tables, the expected result remains 4 roles, 5 users, 3 programs, 100 students, 20 courses, 2 academic terms, 20 course offerings, 200 enrollments, and 200 grades. Generated identities and keys satisfy the declared relationships and unique constraints; 10 distinct students are enrolled per offering, matching capacity.
- First-run assessment: `AcademicSeeder` is suitable for an initial run against the verified empty data tables. It links the first seeded student to `student@example.com`, enabling that account's self-service student and academic-record access. Authentication itself is independent of that link. The unconditional update is a rerun/non-empty-data risk: it can replace a different existing profile link or fail if the demo user's unique `user_id` is linked to another student. The other 99 student profiles remain unlinked.
- `UserFactory` has a populated definition; the other eight factories have empty definitions and are not ready for ordinary factory-created records. No seeder, factory, application code, credential, schema, or database record was modified; no seeder was executed.

## Corrected audit script placement and repeat verification (2026-10-11)

- Corrected and executed the read-only audit as a temporary script inside `backend/`. The `Illuminate\Support\Facades\DB` import is immediately after `<?php`, before Laravel bootstrap statements; both `vendor/autoload.php` and `bootstrap/app.php` resolve through `__DIR__`. The temporary file was removed after execution.
- Laravel confirmed database `student_information_system`. Read-only table counts were zero for roles, users, programs, students, courses, academic terms, course offerings, enrollments, and grades. Duplicate checks, invalid user-role assignments, and orphan checks all returned zero. The role list is empty; no `student@example.com` profile link exists, and student `2026-00001` currently has no linked user.
- No database changes were made. Seeder expectations and the first-student linkage/rerun risk remain as documented above. No migrations or seeders were run.

## One-time demonstration data seeding (2026-10-11)

- Read `DatabaseSeeder` and its invoked `AcademicSeeder`; `RoleSeeder` is empty and not called. The invoked seeder uses `firstOrCreate` and one student link update; it contains no reset, truncate, drop, delete, migration, or unrelated-database operation.
- Immediately before seeding, a Laravel bootstrap check confirmed the configured database was exactly `student_information_system` and all nine data tables were empty. The intended initial link was safe: no existing user/profile data was present, and the seeder creates `student@example.com` with the Student role before linking the first new student (`2026-00001`).
- The supplied `artisan db --force` is the database client command, not the seeder command. Executed the intended existing initial seeder exactly once from `backend/` with `C:\tools\php84\php.exe artisan db:seed --force`. `DatabaseSeeder` invoked `AcademicSeeder`, which completed successfully. No migration or repeated seed run occurred.
- Read-only post-seed counts match expectations: 4 roles, 5 users, 3 programs, 100 students, 20 courses, 2 academic terms, 20 course offerings, 200 enrollments, and 200 grades.
- Read-only integrity checks found zero duplicate role names, user emails, program codes, student numbers, student-user links, course codes, academic terms, offerings, enrollments, or grades; zero invalid role assignments; and zero orphaned student/program/user, offering/course/term/instructor, enrollment, or grade relationships. Role names are Admin, Instructor, Staff, and Student. `student@example.com` with the Student role is linked to student `2026-00001`.
- No database credentials, application source, migration files, or unrelated database were changed. Temporary audit scripts were removed after execution. The only database write was the explicitly authorized one-time seed.

## Frontend dependency restoration and checks (2026-10-11)

- Inspected `frontend/package.json` and lockfile v3. The lockfile root dependency specs match the manifest. Installed Node.js is v25.9.0 and npm is v11.12.1. Locked Vite 8.3.1, `@vitejs/plugin-react` 6.1.1, and oxlint 1.85.0 require Node `^20.19.0 || >=22.12.0`; Node 25.9.0 satisfies these engines. `frontend/node_modules` was initially absent.
- `npm.cmd ci` restored locked packages successfully on the network-enabled retry (72 packages added). No update/fix command was run; package manifests and lockfile were preserved. npm reported one high-severity advisory during installation; no automatic audit fix was applied.
- `npm.cmd run lint` exited successfully with 10 warnings: Fast Refresh export warning in `src/context/AuthContext.jsx`; synchronous state-in-effect warnings in `InstructorGradeRoster.jsx`, `StudentForm.jsx`, `StudentsPage.jsx`, and `ReferenceDataPage.jsx`; unused expression warnings in `StudentsPage.jsx` and `ReferenceDataPage.jsx`; unused `canAdd` in `ReferenceDataPage.jsx`; and missing `load` hook dependencies in `ReferenceDataPage.jsx`. These are source lint issues and were not changed under the requested scope.
- `npm.cmd run build` succeeded with Vite 8.3.1, transforming 114 modules and producing the production bundle. No missing component, import, or dependency errors were exposed.
- No Laravel backend files, environment credentials, database schema, or database records were changed. Only this audit report was modified.

## Frontend npm advisory and lint findings (2026-10-11)

### npm advisory

- `npm.cmd audit --json` initially could not reach the advisory endpoint in the restricted run; the approved network-enabled retry succeeded. It reports `source-map-js` **1.2.1**, **high** severity, direct=false, advisory GHSA-68fv-2mgg-jv7q (indexed source-map offsets can cause event-loop denial of service), vulnerable range `<1.2.2`.
- `npm explain source-map-js` shows it is transitive through `@tailwindcss/node` (via root `@tailwindcss/vite`) and Vite's PostCSS. It is in the installed production dependency graph because `@tailwindcss/vite` is listed under `dependencies`, but it is Node build-tool code, not browser application code; the successful static `dist` build does not ship or execute it. Risk is to build/dev environments processing attacker-controlled indexed source maps, not normal static frontend visitors. No such input or exploit was exercised.
- npm audit reports `fixAvailable: true`. The affected dependency ranges are `^1.2.1`, so patched `source-map-js` **1.2.2 or later within 1.x** is compatible and should require a lockfile-only refresh, not a direct dependency or major-version upgrade. No remediation was run. Recommend a separately reviewed lockfile-only update to at least 1.2.2, followed by audit/build verification.

### Lint warnings and minimal recommendations

`npm.cmd run lint` exited 0 with these 10 warnings:

1. `src/context/AuthContext.jsx:55` — `react/only-export-components`: exports `useAuth` beside `AuthProvider`; affects Fast Refresh tooling only. Safest cleanup: move the hook to a small separate module and update imports, or keep the warning.
2. `src/components/StudentForm.jsx:7` — `react/set-state-in-effect`: effect copies `student` props into form state. This synchronizes edits when the selected student changes; removing it could leave stale form values. Preserve behavior with a keyed remount or explicit reset path before changing it.
3. `src/components/InstructorGradeRoster.jsx:6` — `react/set-state-in-effect`: mount effect calls `load()`, which synchronously reasserts `loading` and clears an initially-null error. Likely redundant render, no observed functional failure. Keep initial-state behavior and avoid redundant synchronous setters on the first load.
4. `src/pages/StudentsPage.jsx:14` — `eslint/no-unused-expressions`: side-effect ternary toggles loading state. Replace with `if/else`; behavior is equivalent.
5. `src/pages/StudentsPage.jsx:15` — `react/set-state-in-effect`: mount effect calls `load(initialQuery)`, which synchronously sets loading/error before awaiting the API. Likely redundant render because initial loading is already true; preserve API/error handling while restructuring.
6. `src/components/ReferenceDataPage.jsx:15` — `eslint/no-unused-vars`: `canAdd` is declared but ignored. `GradesPage` passes it to hide adding when there are no ungraded enrollments, while the component only checks `canManage`; this can leave an unusable Add control visible. Smallest behavior correction: gate the Add control with both `canManage` and `canAdd`.
7. `src/components/ReferenceDataPage.jsx:19` — `eslint/no-unused-expressions`: side-effect ternary toggles loading state. Replace with `if/else`; behavior is equivalent.
8. `src/components/ReferenceDataPage.jsx:20` — `react/set-state-in-effect`: mount effect calls `load()`, which synchronously updates loading/error before awaiting. Likely redundant initial render; preserve request/error behavior during any cleanup.
9. `src/components/ReferenceDataPage.jsx:20` — `react-hooks/exhaustive-deps`: initial effect omits `load`; may retain stale `service`/query closure if those inputs change. Stabilize the loader or move request logic into the effect; do not add `load` blindly because its current closure depends on component state.
10. `src/components/ReferenceDataPage.jsx:21` — `react-hooks/exhaustive-deps`: observer effect omits `load`; can call a stale loader if service or query context changes. Use a stable callback with complete dependencies or keep the observer callback synchronized with current inputs.

The two no-unused-expression warnings are safe behavior-preserving cleanups. The Fast Refresh, set-state-in-effect, and hook-dependency warnings are tooling/design concerns; no runtime failure was demonstrated, and fixes should preserve form synchronization and request behavior. The unused `canAdd` prop is the clearest likely functional issue. No source, manifest, lockfile, backend, or database changes were made; this audit report is the only file changed.

## Targeted `source-map-js` security update (2026-10-11)

- Verified the prior npm advisory: `source-map-js` 1.2.1 was high severity (GHSA-68fv-2mgg-jv7q), with vulnerable range `<1.2.2`. It is transitive through `@tailwindcss/node` and PostCSS. Both parent constraints are `^1.2.1`, which admit patched 1.2.2 without upgrading a parent or changing `package.json`.
- Ran targeted `npm.cmd update source-map-js --package-lock-only`, then `npm.cmd ci` to install the updated lock. `source-map-js` now resolves to 1.2.2 in both parent paths. The change is lockfile-only; the manifest root dependency constraints remain matched. No `npm audit fix` or broad package upgrade was run.
- Post-update `npm.cmd audit --json` completed with zero vulnerabilities (0 info/low/moderate/high/critical). `npm.cmd run lint` exited 0 with the same 10 previously documented warnings. `npm.cmd run build` succeeded with Vite 8.3.1 and transformed 114 modules.
- No React source, Laravel backend, database, or credentials were changed. The lockfile and this audit report are the only intended file changes.

## Demonstration Instructor offering assignment (2026-10-11)

- Confirmed Laravel targets `student_information_system`. The seeded `instructor@example.com` account has the `Instructor` role (user ID 4).
- Before the change, course offering ID 1 (`IT001`, section `A`) had `instructor_id = NULL`, 10 enrollments (all with status `Enrolled`), and 10 grades. This was the preferred existing unassigned offering.
- With explicit authorization, a transaction conditionally set only `course_offerings.instructor_id` for ID 1 where `instructor_id IS NULL`. Exactly one row was affected. No enrollment or grade rows were changed; their counts remained 10 and 10.
- Post-update read-only checks confirmed the offering is assigned to user ID 4. Instructor-authenticated, in-process read-only checks returned HTTP 200 from `/api/v1/my/course-offerings` (total 1) and `/api/v1/my/teaching-enrollments` (total 10). Transient Sanctum authentication was used; `personal_access_tokens` row count remained unchanged.
- The migration defines `instructor_id` as a nullable foreign key to `users` with `nullOnDelete`; enrollments remain associated with their offering. The Instructor can now view the offering's 10 existing enrollments and grades and has endpoint authorization to update grades for those enrollments. Existing grades mean grade-create requests for them are already blocked as duplicates; no grade data was changed.
- No source code, authentication configuration, migrations, seeders, or other database records were modified.

## Isolated Instructor grade-update API tests (2026-10-12)

- Verified test isolation before running database-writing tests: `backend/phpunit.xml` sets `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`; no `.env.testing` or cached Laravel config exists, and the current shell has no `DB_CONNECTION` / `DB_DATABASE` override. PHP 8.4.26 has `pdo_sqlite` loaded. The feature tests use Laravel `RefreshDatabase`, so their schema and fixture writes are confined to the in-memory SQLite database.
- Existing tests already cover successful assigned-Instructor grade updates, invalid enrollment references on grade creation, and denial for grades belonging to another instructor. Added focused update tests for values above 100 and below 0, confirming 422 validation errors and preservation of the prior grade; strengthened the cross-Instructor denial test to assert the stored grade is unchanged; added an unauthenticated update test asserting 401 and unchanged grade.
- `C:\tools\php84\php.exe vendor/bin/phpunit --configuration phpunit.xml tests/Feature/AcademicWorkflowTest.php --filter grade`: passed, 7 tests / 29 assertions.
- `C:\tools\php84\php.exe vendor/bin/phpunit --configuration phpunit.xml tests/Feature/AuthorizationTest.php --filter 'instructor_can_access_assigned_offerings_and_update_their_grade|instructor_cannot_modify_a_grade_from_another_offering'`: passed, 2 tests / 6 assertions.
- No MySQL database was used or modified; no production authorization or API code changed. The focused test file and this audit report are the only intended changes.

## Student Management REST API tests (2026-10-12)

- Before the database-writing feature run, reconfirmed `backend/phpunit.xml` sets `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`. `backend/.env.testing` and `backend/bootstrap/cache/config.php` are absent, and no shell `DB_CONNECTION` / `DB_DATABASE` overrides were set. The PHP 8.4.26 runtime has `pdo_sqlite` loaded. All feature fixtures and `RefreshDatabase` migrations therefore used disposable in-memory SQLite; the development MySQL database was not targeted.
- Reused existing coverage in `StudentCollectionQueryTest` for collection retrieval, search, program/year/status filters, sort direction, and pagination; `AcademicWorkflowTest` and `AuthorizationTest` cover Student self-service and denial of another student's academic record. Added `StudentManagementApiTest` for Admin create/list/detail/update/delete, duplicate number, invalid email, missing required fields, invalid program, failed-update preservation, unauthenticated requests, and Student-role denial across management routes.
- Ran `C:\tools\php84\php.exe artisan test --filter=Student`: passed, 22 tests / 111 assertions. No production Laravel code or frontend code changed; no development MySQL records were accessed or modified.
- Deletion behavior verified: DELETE returns 204 and removes an unreferenced Student row. The students migration has a `deleted_at` column, but `Student` does not use Laravel's `SoftDeletes` trait, so the current API performs a physical deletion. No change was made; confirm whether this is the intended contract before changing it.

## Student DELETE and soft-delete behavior review (2026-10-12)

- `Student` does not use `Illuminate\Database\Eloquent\SoftDeletes`. `students` migration 2026_09_23_032248 adds nullable `deleted_at`, but that column is not used by the model. `Route::apiResource('students', StudentController::class)` is inside `auth:sanctum` plus `role:Admin,Staff`; `StudentController` inherits `CrudController::destroy`, which finds the row and calls Eloquent `delete()`. Without the trait, this issues a physical DELETE. Success returns 204. A restrictive enrollment foreign key prevents deleting students with enrollments; `CrudController` maps matching FK failures to 409.
- The project-local design brief says soft deletes are included only when required by laboratory briefs and excludes recovery screens for soft-deleted records. The migration column suggests soft-delete intent, while the OpenAPI says DELETE/204 and the Students UI explicitly says deletion is permanent. The form and filter also support status `Inactive`, but there is no separate deactivate control. No official laboratory handout specifying delete semantics is present in the inspected project documentation, so the required contract remains uncertain.
- If `SoftDeletes` were enabled, normal Student listing and detail/model binding would automatically exclude deleted rows; the `deleted_at` value would be retained, and the unique `student_number` would remain reserved (the SQL uniqueness constraint still applies). DELETE could keep its 204 response and the frontend service would still work, but its “permanent” confirmation would become inaccurate unless no restore mechanism is intentionally offered.
- Soft deletion would preserve enrollment rows and FK references, but existing code expects related Students to load: `Enrollment::student()` has no `withTrashed()`, `InstructorEnrollmentController` dereferences `$enrollment->student`, and academic-record routes and student self-service queries use normal scoped model lookups. A soft-deleted student could therefore disappear from linked enrollment/grade views or cause null dereferences unless historical relationships and access behavior are deliberately handled. Student self-service `/my/student` would return 404 under the default scope. No API restore path exists.
- Existing test `StudentManagementApiTest::test_admin_can_delete_an_unreferenced_student` asserts physical removal and would need `assertSoftDeleted` plus checks that deleted rows are hidden from ordinary list/detail calls if semantics change. Existing Student query and academic-record tests should remain, with added coverage for retained enrollment/grade history and deleted-profile access. No application or database changes were made during this read-only review.
- Recommendation: do not add only the `SoftDeletes` trait. Until an authoritative brief confirms soft deletion, retain the current contract and use the existing `Inactive` status for non-destructive student deactivation; current FK behavior already prevents physical deletion of students with enrollment history. If soft deletion is required, make it a coordinated change covering enrollment/academic-record relation visibility, instructor roster handling, self-service authorization, UI confirmation, and tests.

## Academic-management REST API verification (2026-10-12)

- Reconfirmed the isolated test setup before test execution: `backend/phpunit.xml` sets `APP_ENV=testing`, `DB_CONNECTION=sqlite`, and `DB_DATABASE=:memory:`; there is no `.env.testing`, cached config, or shell DB override, and PHP 8.4.26 has `pdo_sqlite` loaded. Test commands also used process-local testing/SQLite environment values. No development MySQL connection or records were used.
- Existing coverage reused: enrollment creation, enrollment search and ungraded filtering, invalid student/offering references, duplicate-enrollment conflict, restrictive foreign-key conflict, grade workflows, and student/role authorization. Added five `AcademicManagementApiTest` cases covering Program/Course/Academic Term/Course Offering CRUD and offering relations; required/invalid-field and reference validation; collection search/filter/sort/pagination for reference resources; Enrollment CRUD, eager relationship response, status change and invalid-status preservation; and 401/403/Staff access behavior with unchanged data after denied requests.
- `C:\tools\php84\php.exe artisan test --filter='AcademicManagementApiTest|AcademicWorkflowTest|AuthorizationTest'`: 26 passed, 240 assertions. The new test class alone: 5 passed, 157 assertions; reused tests in those classes: 21 passed, 83 assertions. `ApiExceptionTest`: 1 passed, 2 assertions. Total relevant run: 27 passed, 242 assertions, 0 failures.
- Verified response coverage includes 201 create, 200 reads/updates, 204 deletes, 401 unauthenticated, 403 forbidden, 404 missing resources, 409 duplicate/FK conflicts, and 422 validation errors. Duplicate and invalid updates retain the original in-memory records; authorization failures create no academic records. Student DELETE behavior was not changed.
- No production code or frontend code was changed. Review finding for a separate decision: `CourseOfferingController` validates `instructor_id` only with `exists:users,id`; the UI limits choices to Instructor-role users, but the API itself accepts a different existing user role. No test was made to assert a role restriction and no application change was made; if role-level reference validation is required, address separately.

## Course Offering Instructor Role Validation (2026-10-12)

- Root cause: CourseOfferingController used `nullable|exists:users,id`, so any existing user ID passed API validation. CrudController store and update both consume this shared rule set; the update route handles both PUT and PATCH. The course offering API remains restricted to Admin and Staff.
- Updated instructor_id validation to use Laravel Rule::exists with a role subquery requiring the referenced user's role_id to match the Instructor role. Nullable values remain accepted. No authorization, route, API response, or database schema changes were made.
- Added two focused AcademicManagementApiTest cases. They verify Instructor assignment and null assignment through create/update; reject Admin, Staff, Student, and nonexistent user IDs on create, PUT, and PATCH with HTTP 422; and confirm rejected requests preserve the offering, enrollment, and grade fixtures.
- Verified PHPUnit isolation from development MySQL before execution: phpunit.xml sets APP_ENV=testing, DB_CONNECTION=sqlite, and DB_DATABASE=:memory:; .env.testing and cached configuration are absent; PHP 8.4.26 has pdo_sqlite loaded. Test commands additionally set process-local testing/SQLite values. No development MySQL data was accessed or changed.
- Targeted new tests: 2 passed, 52 assertions. Broader affected academic suite (AcademicManagementApiTest, AcademicWorkflowTest, AuthorizationTest, InstructorTeachingEnrollmentsTest, AcademicSeederTest): 31 passed, 314 assertions, 0 failures.

## Full Laravel Backend Regression Suite (2026-10-12)

- Confirmed test isolation before running database-writing tests: backend/phpunit.xml sets APP_ENV=testing, DB_CONNECTION=sqlite, and DB_DATABASE=:memory:. backend/.env.testing and cached Laravel config are absent, no shell DB_CONNECTION/DB_DATABASE overrides were present, and PHP 8.4.26 has pdo_sqlite loaded. The full-suite command used process-local APP_ENV/SQLite settings; all fixture writes and RefreshDatabase migrations ran in isolated in-memory SQLite. The seeder test invokes AcademicSeeder only within that isolated suite. Development MySQL was not accessed.
- Ran `C:\tools\php84\php.exe artisan test`: 47 passed, 0 failed, 0 skipped, 0 risky, 387 assertions.
- Coverage includes Student management and collection queries; authentication and role authorization; Programs, Courses, Academic Terms, Course Offerings, Enrollments, Grades, academic records, Instructor teaching enrollment access, seeded relationships, API 404 handling, and the Instructor-only course-offering assignment rule. Course Offering creation/update with an Instructor and nullable assignment passed; Admin, Staff, Student, and nonexistent instructor IDs were rejected on create, PUT, and PATCH. The duplicate-enrollment and invalid-update expectations remain consistent with current API behavior.
- Instructor grade ownership is checked in both AcademicWorkflowTest and AuthorizationTest with different fixture ownership paths; these overlap in purpose but are not identical assertions. No stale expectations or significant coverage gap affecting the recent changes was identified. No additional tests were added during this audit.
- No application or test source files were changed in this regression run. This result verifies automated backend tests only; it does not verify the live MySQL database or manual browser flows.
