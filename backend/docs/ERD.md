# Entity Relationship Diagram

```mermaid
erDiagram
    ROLES ||--o{ USERS : assigns
    USERS ||--o| STUDENTS : links
    PROGRAMS ||--o{ STUDENTS : contains
    USERS ||--o{ COURSE_OFFERINGS : instructs
    COURSES ||--o{ COURSE_OFFERINGS : scheduled_as
    ACADEMIC_TERMS ||--o{ COURSE_OFFERINGS : contains
    STUDENTS ||--o{ ENROLLMENTS : has
    COURSE_OFFERINGS ||--o{ ENROLLMENTS : receives
    ENROLLMENTS ||--o| GRADES : has

    ROLES {
        bigint id PK
        string name UK
    }
    USERS {
        bigint id PK
        bigint role_id FK
        string email UK
        string status
    }
    PROGRAMS {
        bigint id PK
        string code UK
        string name
        string status
    }
    STUDENTS {
        bigint id PK
        bigint user_id FK, UK
        bigint program_id FK
        string student_number UK
        string first_name
        string middle_name
        string last_name
        string email
        int year_level
        string status
    }
    ACADEMIC_TERMS {
        bigint id PK
        string academic_year
        string semester
        date start_date
        date end_date
        string status
    }
    COURSES {
        bigint id PK
        string course_code UK
        string course_title
        int units
        string status
    }
    COURSE_OFFERINGS {
        bigint id PK
        bigint course_id FK
        bigint academic_term_id FK
        bigint instructor_id FK
        string section
        int capacity
        string status
    }
    ENROLLMENTS {
        bigint id PK
        bigint student_id FK
        bigint course_offering_id FK
        date enrollment_date
        string status
    }
    GRADES {
        bigint id PK
        bigint enrollment_id FK, UK
        decimal grade
        string remarks
    }
```

`academic_terms` is unique on `(academic_year, semester)`, `course_offerings` on `(course_id, academic_term_id, section)`, and `enrollments` on `(student_id, course_offering_id)`.
