# Student Information Management System — Minimal Design

## Purpose

Build a defense-ready Student Information Management System that meets the mandatory requirements of the REST API and frontend laboratory activities. The backend is the sole authority for data, validation, authentication, and authorization. The frontend is a separate browser client that consumes the backend API only.

## Scope

The deliverable contains two clearly separated applications in one repository:

- `backend/`: Laravel 12 REST API using MySQL, Laravel Sanctum bearer tokens, Swagger/OpenAPI, PHPUnit, and a Postman collection.
- `frontend/`: React + Vite browser application using Axios, React Router, Tailwind CSS, and Context API.

The API is versioned at `/api/v1`. Authentication uses email and password. Fixed roles are Admin, Staff, Student, and Instructor; the instructor role is stored in the database. The mandatory resources are users/roles, programs, students, courses, academic terms, course offerings, enrollments, and grades. The frontend supplies the required authenticated, role-aware CRUD and academic-record screens.

## Architecture

Laravel will use its standard maintainable boundaries: Eloquent models and migrations for persistence; controllers for HTTP orchestration; Form Requests for server-side validation; API Resources for response shaping; Sanctum and middleware/policies for authentication and authorization; factories/seeders for demonstration data; and PHPUnit feature tests for critical flows. Requests and responses use a consistent JSON envelope and the documented status codes.

The frontend will have routes/pages, reusable UI components, an Axios service layer, authentication context, protected route handling, and feature-level forms/tables. It has no database connection, server routes, or business-rule authority. It sends tokens to the API and displays backend validation and authorization outcomes.

## Data and Security Rules

Relationships, foreign keys, indexes, uniqueness rules, soft deletes, duplicate-enrollment prevention, pagination, filtering, search, sorting, role checks, and object-level access control are implemented only where required by the laboratory briefs. Secrets are environment variables and only safe `.env.example` files are committed.

## Explicit Exclusions

To keep the submission minimal, this project excludes every optional enhancement: Docker, email verification, password reset, refresh tokens, rate limiting, audit trails, CSV/PDF export, Redis, CI/CD, recovery screens for soft-deleted records, extra permissions administration, caching, deployment infrastructure, and non-required UI preferences.

## Verification and Documentation

The final project includes required migrations, factories/seeders, OpenAPI/Swagger documentation, a Postman collection, a README, a concise AI development log, and automated test evidence for the mandatory authentication, validation, authorization, enrollment, grade, and collection behaviors.
