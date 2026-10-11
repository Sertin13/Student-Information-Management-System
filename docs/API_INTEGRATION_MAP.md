# API Integration Map

The frontend uses the centralized Axios client configured by VITE_API_BASE_URL. Its default local value is http://127.0.0.1:8000/api/v1. Protected requests carry the stored Sanctum bearer token. Laravel route middleware, validation, and ownership checks remain authoritative.

## Screen and service map

| Frontend screen or service | API methods | Access |
| --- | --- | --- |
| Login and session restoration | POST /auth/login; GET /auth/me; POST /auth/logout | Login is public; session endpoints require authentication |
| Profile | GET /auth/me | Any authenticated role |
| Admin/Staff Dashboard | GET /students, /programs, /courses, /academic-terms, /course-offerings, /enrollments | Admin, Staff |
| Students | GET/POST /students; GET/PUT/DELETE /students/{id} | Admin, Staff |
| Programs | GET/POST /programs; GET/PUT/DELETE /programs/{id} | Admin, Staff |
| Courses | GET/POST /courses; GET/PUT/DELETE /courses/{id} | Admin, Staff |
| Academic Terms | GET/POST /academic-terms; GET/PUT/DELETE /academic-terms/{id} | Admin, Staff |
| Course Offerings | GET/POST /course-offerings; GET/PUT/DELETE /course-offerings/{id}; GET /courses; GET /academic-terms; GET /instructors | Admin, Staff; an assigned instructor reference must belong to an Instructor-role user |
| Enrollments | GET/POST /enrollments; GET/PUT/DELETE /enrollments/{id}; GET /students; GET /course-offerings | Admin, Staff |
| Admin/Staff Grades | GET/POST /grades; GET/PUT /grades/{id}; GET /enrollments?without_grade=1 | Admin, Staff |
| Instructor Dashboard and roster | GET /my/course-offerings; GET /my/teaching-enrollments | Instructor; results are scoped to assigned offerings |
| Instructor Grade Entry | POST /my/grades; PUT /my/grades/{id} | Instructor; writes are limited to enrollments in that instructor's offerings |
| Student Dashboard | GET /my/student; GET /my/enrollments; GET /my/grades | Student; results are scoped to the linked student |
| Academic Record | GET /students/{id}/academic-record | Admin/Staff or the Student linked to that record |

Collection services unwrap the Laravel success/data envelope and preserve pagination metadata. The Student service also removes empty optional filters before making requests.

## Known integration limits

- Course Offering selectors load the first two pages of courses and terms (up to 40 each). Enrollment selectors load up to 200 students and 40 offerings. These cover the current seeded catalog (20 courses, 2 terms, 100 students, and 20 offerings), but larger catalogs may need paginated option loading.
- Admin/Staff grade entry loads only the first page of ungraded enrollments (20 by default), so later pages are not available in that selector when more than 20 records need grading.
- The frontend build and lint checks pass. Manual browser flows are recorded separately in the final verification report; automated build checks do not prove browser interactions.

## API references

- [OpenAPI 3.0 specification](../backend/docs/openapi.json)
- [Postman collection](../backend/docs/postman/Student-Information-System.postman_collection.json)
- [Laravel route definitions](../backend/routes/api.php)
