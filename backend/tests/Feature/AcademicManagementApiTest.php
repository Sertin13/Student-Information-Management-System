<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademicManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_programs_courses_terms_and_related_offerings(): void
    {
        Sanctum::actingAs($this->user('Admin'));

        $program = $this->postJson('/api/v1/programs', ['code' => 'CRUD-P', 'name' => 'Program One', 'status' => 'Active'])
            ->assertCreated()->assertJsonPath('success', true)->json('data');
        $this->getJson('/api/v1/programs')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/v1/programs/'.$program['id'])->assertOk()->assertJsonPath('data.id', $program['id']);
        $this->putJson('/api/v1/programs/'.$program['id'], ['code' => 'CRUD-P', 'name' => 'Updated Program', 'status' => 'Active'])
            ->assertOk()->assertJsonPath('data.name', 'Updated Program');

        $course = $this->postJson('/api/v1/courses', ['course_code' => 'CRUD-C', 'course_title' => 'Course One', 'units' => 3, 'status' => 'Active'])
            ->assertCreated()->json('data');
        $this->getJson('/api/v1/courses')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/v1/courses/'.$course['id'])->assertOk()->assertJsonPath('data.id', $course['id']);
        $this->putJson('/api/v1/courses/'.$course['id'], ['course_code' => 'CRUD-C', 'course_title' => 'Updated Course', 'units' => 4, 'status' => 'Active'])
            ->assertOk()->assertJsonPath('data.course_title', 'Updated Course');

        $term = $this->postJson('/api/v1/academic-terms', ['academic_year' => '2090-2091', 'semester' => 'First', 'start_date' => '2090-08-01', 'end_date' => '2090-12-01', 'status' => 'Active'])
            ->assertCreated()->json('data');
        $this->getJson('/api/v1/academic-terms')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/v1/academic-terms/'.$term['id'])->assertOk()->assertJsonPath('data.id', $term['id']);
        $this->putJson('/api/v1/academic-terms/'.$term['id'], ['academic_year' => '2090-2091', 'semester' => 'Second', 'start_date' => '2091-01-01', 'end_date' => '2091-05-01', 'status' => 'Active'])
            ->assertOk()->assertJsonPath('data.semester', 'Second');

        $instructor = $this->user('Instructor');
        $offering = $this->postJson('/api/v1/course-offerings', [
            'course_id' => $course['id'], 'academic_term_id' => $term['id'], 'instructor_id' => $instructor->id,
            'section' => 'A', 'capacity' => 20, 'status' => 'Active',
        ])->assertCreated()->json('data');
        $this->getJson('/api/v1/course-offerings')->assertOk()
            ->assertJsonPath('data.data.0.course.id', $course['id'])
            ->assertJsonPath('data.data.0.academic_term.id', $term['id'])
            ->assertJsonPath('data.data.0.instructor.id', $instructor->id);
        $this->getJson('/api/v1/course-offerings/'.$offering['id'])->assertOk()->assertJsonPath('data.course_id', $course['id']);
        $this->putJson('/api/v1/course-offerings/'.$offering['id'], [
            'course_id' => $course['id'], 'academic_term_id' => $term['id'], 'instructor_id' => $instructor->id,
            'section' => 'B', 'capacity' => 20, 'status' => 'Inactive',
        ])->assertOk()->assertJsonPath('data.section', 'B');
        $this->deleteJson('/api/v1/course-offerings/'.$offering['id'])->assertNoContent();

        $this->deleteJson('/api/v1/courses/'.$course['id'])->assertNoContent();
        $this->deleteJson('/api/v1/academic-terms/'.$term['id'])->assertNoContent();
        $this->deleteJson('/api/v1/programs/'.$program['id'])->assertNoContent();
    }

    public function test_reference_data_validation_rejects_invalid_requests_without_creating_records(): void
    {
        $program = $this->program();
        $course = $this->course();
        $term = $this->term();
        Sanctum::actingAs($this->user('Admin'));

        $this->postJson('/api/v1/programs', [])->assertUnprocessable()->assertJsonValidationErrors(['code', 'name', 'status']);
        $this->postJson('/api/v1/programs', ['code' => $program->code, 'name' => 'Duplicate', 'status' => 'Active'])
            ->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->postJson('/api/v1/courses', ['course_code' => 'BAD-UNITS', 'course_title' => 'Bad Units', 'units' => 0, 'status' => 'Active'])
            ->assertUnprocessable()->assertJsonValidationErrors('units');
        $this->postJson('/api/v1/courses', [])->assertUnprocessable()->assertJsonValidationErrors(['course_code', 'course_title', 'units', 'status']);
        $this->postJson('/api/v1/academic-terms', ['academic_year' => '2092-2093', 'semester' => 'First', 'start_date' => '2092-12-01', 'end_date' => '2092-08-01', 'status' => 'Active'])
            ->assertUnprocessable()->assertJsonValidationErrors('end_date');
        $this->postJson('/api/v1/academic-terms', [])->assertUnprocessable()->assertJsonValidationErrors(['academic_year', 'semester', 'start_date', 'end_date', 'status']);
        $this->postJson('/api/v1/course-offerings', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['course_id', 'academic_term_id', 'section', 'capacity', 'status']);
        $this->postJson('/api/v1/course-offerings', [
            'course_id' => 999999, 'academic_term_id' => 999999, 'instructor_id' => 999999,
            'section' => 'A', 'capacity' => 10, 'status' => 'Active',
        ])->assertUnprocessable()->assertJsonValidationErrors(['course_id', 'academic_term_id', 'instructor_id']);

        $this->assertDatabaseCount('programs', 1);
        $this->assertDatabaseHas('programs', ['id' => $program->id, 'name' => $program->name]);
        $this->assertDatabaseCount('courses', 1);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'course_title' => $course->course_title]);
        $this->assertDatabaseCount('academic_terms', 1);
        $this->assertDatabaseHas('academic_terms', ['id' => $term->id, 'semester' => $term->semester]);
        $this->assertDatabaseCount('course_offerings', 0);
    }

    public function test_course_offerings_accept_instructors_and_nullable_assignments_on_create_and_update(): void
    {
        Sanctum::actingAs($this->user('Admin'));
        $course = $this->course();
        $term = $this->term();
        $instructor = $this->user('Instructor');
        $payload = [
            'course_id' => $course->id, 'academic_term_id' => $term->id, 'section' => 'A',
            'capacity' => 20, 'status' => 'Active',
        ];

        $assigned = $this->postJson('/api/v1/course-offerings', $payload + ['instructor_id' => $instructor->id])
            ->assertCreated()->assertJsonPath('data.instructor_id', $instructor->id)->json('data');
        $unassigned = $this->postJson('/api/v1/course-offerings', array_merge($payload, ['section' => 'B', 'instructor_id' => null]))
            ->assertCreated()->assertJsonPath('data.instructor_id', null)->json('data');

        $this->putJson('/api/v1/course-offerings/'.$unassigned['id'], array_merge($payload, ['section' => 'B', 'instructor_id' => $instructor->id]))
            ->assertOk()->assertJsonPath('data.instructor_id', $instructor->id);
        $this->patchJson('/api/v1/course-offerings/'.$unassigned['id'], array_merge($payload, ['section' => 'B', 'instructor_id' => null]))
            ->assertOk()->assertJsonPath('data.instructor_id', null);

        $this->assertDatabaseHas('course_offerings', ['id' => $assigned['id'], 'instructor_id' => $instructor->id]);
        $this->assertDatabaseHas('course_offerings', ['id' => $unassigned['id'], 'instructor_id' => null]);
    }

    public function test_course_offerings_reject_non_instructor_assignments_without_changing_existing_academic_data(): void
    {
        $admin = $this->user('Admin');
        Sanctum::actingAs($admin);
        $course = $this->course();
        $term = $this->term();
        $program = $this->program();
        $student = $this->student($program);
        $offering = $this->offering($course, $term);
        $enrollment = Enrollment::create([
            'student_id' => $student->id, 'course_offering_id' => $offering->id,
            'enrollment_date' => '2097-08-01', 'status' => 'Enrolled',
        ]);
        $grade = Grade::create(['enrollment_id' => $enrollment->id, 'grade' => 88, 'remarks' => 'Preserve']);
        $payload = [
            'course_id' => $course->id, 'academic_term_id' => $term->id, 'section' => $offering->section,
            'capacity' => $offering->capacity, 'status' => $offering->status,
        ];
        $invalidInstructors = collect([
            User::factory()->create(['role_id' => $admin->role_id])->id,
            $this->user('Staff')->id,
            $this->user('Student')->id,
        ])
            ->push(999999);

        foreach ($invalidInstructors as $userId) {
            $this->postJson('/api/v1/course-offerings', array_merge($payload, ['section' => 'INVALID-'.$userId, 'instructor_id' => $userId]))
                ->assertUnprocessable()->assertJsonValidationErrors('instructor_id');
            $this->putJson('/api/v1/course-offerings/'.$offering->id, $payload + ['instructor_id' => $userId])
                ->assertUnprocessable()->assertJsonValidationErrors('instructor_id');
            $this->patchJson('/api/v1/course-offerings/'.$offering->id, $payload + ['instructor_id' => $userId])
                ->assertUnprocessable()->assertJsonValidationErrors('instructor_id');
        }

        $this->assertDatabaseCount('course_offerings', 1);
        $this->assertDatabaseHas('course_offerings', ['id' => $offering->id, 'instructor_id' => null]);
        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id, 'course_offering_id' => $offering->id]);
        $this->assertDatabaseCount('grades', 1);
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'enrollment_id' => $enrollment->id, 'grade' => 88, 'remarks' => 'Preserve']);
    }

    public function test_reference_collections_support_search_filter_sort_and_pagination(): void
    {
        Sanctum::actingAs($this->user('Admin'));
        $programA = $this->program(['code' => 'QUERY-PA', 'name' => 'Query Alpha']);
        $programB = $this->program(['code' => 'QUERY-PB', 'name' => 'Query Zulu']);
        $courseA = $this->course(['course_code' => 'QUERY-CA', 'course_title' => 'Query Alpha']);
        $courseB = $this->course(['course_code' => 'QUERY-CB', 'course_title' => 'Query Zulu']);
        $termA = $this->term(['academic_year' => '2094-2095', 'semester' => 'First']);
        $termB = $this->term(['academic_year' => '2094-2095', 'semester' => 'Second']);
        $offeringA = $this->offering($courseA, $termA, ['section' => 'QUERY-A']);
        $offeringB = $this->offering($courseB, $termA, ['section' => 'QUERY-B']);
        $this->program(['code' => 'OTHER-P', 'name' => 'Other', 'status' => 'Inactive']);
        $this->course(['course_code' => 'OTHER-C', 'course_title' => 'Other', 'status' => 'Inactive']);

        $this->getJson('/api/v1/programs?search=Query&status=Active&sort=name&direction=desc&per_page=1&page=1')
            ->assertOk()->assertJsonPath('data.total', 2)->assertJsonPath('data.per_page', 1)->assertJsonPath('data.data.0.id', $programB->id);
        $this->getJson('/api/v1/programs?search=Query&status=Active&sort=name&direction=desc&per_page=1&page=2')
            ->assertOk()->assertJsonPath('data.data.0.id', $programA->id);
        $this->getJson('/api/v1/courses?search=Query&status=Active&sort=course_title&direction=desc&per_page=1&page=1')
            ->assertOk()->assertJsonPath('data.total', 2)->assertJsonPath('data.data.0.id', $courseB->id);
        $this->getJson('/api/v1/courses?search=Query&status=Active&sort=course_title&direction=desc&per_page=1&page=2')
            ->assertOk()->assertJsonPath('data.data.0.id', $courseA->id);
        $this->getJson('/api/v1/academic-terms?search=2094-2095&status=Active&sort=semester&direction=desc&per_page=1')
            ->assertOk()->assertJsonPath('data.total', 2)->assertJsonPath('data.data.0.id', $termB->id);
        $this->getJson('/api/v1/course-offerings?search=QUERY&academic_term_id='.$termA->id.'&status=Active&sort=section&direction=desc&per_page=1')
            ->assertOk()->assertJsonPath('data.total', 2)->assertJsonPath('data.data.0.id', $offeringB->id)
            ->assertJsonPath('data.data.0.course.id', $courseB->id)
            ->assertJsonPath('data.data.0.academic_term.id', $termA->id);
        $this->assertSame($courseA->id, $offeringA->fresh()->course_id);
    }

    public function test_enrollment_crud_relationships_and_invalid_status_preserve_data(): void
    {
        Sanctum::actingAs($this->user('Admin'));
        $program = $this->program();
        $studentA = $this->student($program);
        $studentB = $this->student($program);
        $course = $this->course();
        $term = $this->term();
        $offering = $this->offering($course, $term);

        $this->postJson('/api/v1/enrollments', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['student_id', 'course_offering_id', 'enrollment_date', 'status']);
        $this->assertDatabaseCount('enrollments', 0);

        $enrollmentA = $this->postJson('/api/v1/enrollments', [
            'student_id' => $studentA->id, 'course_offering_id' => $offering->id, 'enrollment_date' => '2096-08-01', 'status' => 'Enrolled',
        ])->assertCreated()->json('data');
        $enrollmentB = $this->postJson('/api/v1/enrollments', [
            'student_id' => $studentB->id, 'course_offering_id' => $offering->id, 'enrollment_date' => '2096-08-02', 'status' => 'Enrolled',
        ])->assertCreated()->json('data');

        $this->getJson('/api/v1/enrollments?status=Enrolled&sort=enrollment_date&direction=desc&per_page=1')
            ->assertOk()->assertJsonPath('data.total', 2)->assertJsonPath('data.per_page', 1)
            ->assertJsonPath('data.data.0.id', $enrollmentB['id'])
            ->assertJsonPath('data.data.0.student.id', $studentB->id)
            ->assertJsonPath('data.data.0.course_offering.course.id', $course->id)
            ->assertJsonPath('data.data.0.course_offering.academic_term.id', $term->id);
        $this->getJson('/api/v1/enrollments/'.$enrollmentA['id'])->assertOk()->assertJsonPath('data.id', $enrollmentA['id']);
        $this->putJson('/api/v1/enrollments/'.$enrollmentA['id'], [
            'student_id' => $studentA->id, 'course_offering_id' => $offering->id, 'enrollment_date' => '2096-08-01', 'status' => 'Dropped',
        ])->assertOk()->assertJsonPath('data.status', 'Dropped');
        $this->putJson('/api/v1/enrollments/'.$enrollmentA['id'], [
            'student_id' => $studentA->id, 'course_offering_id' => $offering->id, 'enrollment_date' => '2096-08-01', 'status' => 'Suspended',
        ])->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertDatabaseHas('enrollments', ['id' => $enrollmentA['id'], 'status' => 'Dropped']);
        $this->deleteJson('/api/v1/enrollments/'.$enrollmentA['id'])->assertNoContent();
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollmentA['id']]);
        $this->assertDatabaseHas('enrollments', ['id' => $enrollmentB['id'], 'status' => 'Enrolled']);
    }

    public function test_grade_management_resources_allow_admin_and_staff_and_reject_other_access(): void
    {
        $program = $this->program();
        $student = $this->student($program);
        $offering = $this->offering($this->course(), $this->term());
        $enrollment = Enrollment::create([
            'student_id' => $student->id, 'course_offering_id' => $offering->id,
            'enrollment_date' => '2097-08-01', 'status' => 'Enrolled',
        ]);
        $payload = ['enrollment_id' => $enrollment->id, 'grade' => 76, 'remarks' => 'Initial'];

        $this->getJson('/api/v1/grades')->assertUnauthorized();
        $this->postJson('/api/v1/grades', $payload)->assertUnauthorized();

        Sanctum::actingAs($this->user('Student'));
        $this->getJson('/api/v1/grades')->assertForbidden();
        $this->postJson('/api/v1/grades', $payload)->assertForbidden();
        $this->assertDatabaseCount('grades', 0);

        Sanctum::actingAs($this->user('Instructor'));
        $this->getJson('/api/v1/grades')->assertForbidden();
        $this->postJson('/api/v1/grades', $payload)->assertForbidden();
        $this->assertDatabaseCount('grades', 0);

        Sanctum::actingAs($this->user('Admin'));
        $grade = $this->postJson('/api/v1/grades', $payload)
            ->assertCreated()->assertJsonPath('data.grade', 76)->json('data');
        $this->getJson('/api/v1/grades')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/v1/grades/'.$grade['id'])->assertOk()->assertJsonPath('data.id', $grade['id']);
        $this->putJson('/api/v1/grades/'.$grade['id'], array_merge($payload, ['grade' => 101]))
            ->assertUnprocessable()->assertJsonValidationErrors('grade');
        $this->assertDatabaseHas('grades', ['id' => $grade['id'], 'grade' => 76, 'remarks' => 'Initial']);

        Sanctum::actingAs($this->user('Staff'));
        $this->getJson('/api/v1/grades')->assertOk()->assertJsonPath('data.total', 1);
        $this->putJson('/api/v1/grades/'.$grade['id'], array_merge($payload, ['grade' => 86, 'remarks' => 'Reviewed']))
            ->assertOk()->assertJsonPath('data.grade', 86)->assertJsonPath('data.remarks', 'Reviewed');
        $this->assertDatabaseHas('grades', ['id' => $grade['id'], 'grade' => 86, 'remarks' => 'Reviewed']);
    }

    public function test_academic_management_requires_admin_or_staff_and_failed_requests_do_not_change_data(): void
    {
        foreach (['programs', 'courses', 'academic-terms', 'course-offerings', 'enrollments'] as $resource) {
            $this->getJson('/api/v1/'.$resource)->assertUnauthorized();
        }
        $this->postJson('/api/v1/programs', ['code' => 'UNAUTH', 'name' => 'Unauthorized', 'status' => 'Active'])->assertUnauthorized();
        $this->assertDatabaseCount('programs', 0);

        Sanctum::actingAs($this->user('Student'));
        foreach (['programs', 'courses', 'academic-terms', 'course-offerings', 'enrollments'] as $resource) {
            $this->getJson('/api/v1/'.$resource)->assertForbidden();
        }
        $this->postJson('/api/v1/programs', ['code' => 'FORBIDDEN', 'name' => 'Forbidden', 'status' => 'Active'])->assertForbidden();
        $this->assertDatabaseCount('programs', 0);

        Sanctum::actingAs($this->user('Staff'));
        foreach (['programs', 'courses', 'academic-terms', 'course-offerings', 'enrollments'] as $resource) {
            $this->getJson('/api/v1/'.$resource)->assertOk()->assertJsonPath('success', true);
        }
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role_id' => Role::create(['name' => $role])->id]);
    }

    private function program(array $overrides = []): Program
    {
        return Program::create(array_merge(['code' => 'P-'.uniqid(), 'name' => 'Test Program', 'status' => 'Active'], $overrides));
    }

    private function course(array $overrides = []): Course
    {
        return Course::create(array_merge(['course_code' => 'C-'.uniqid(), 'course_title' => 'Test Course', 'units' => 3, 'status' => 'Active'], $overrides));
    }

    private function term(array $overrides = []): AcademicTerm
    {
        return AcademicTerm::create(array_merge([
            'academic_year' => '2097-2098', 'semester' => 'First', 'start_date' => '2097-08-01', 'end_date' => '2097-12-01', 'status' => 'Active',
        ], $overrides));
    }

    private function offering(Course $course, AcademicTerm $term, array $overrides = []): CourseOffering
    {
        return CourseOffering::create(array_merge([
            'course_id' => $course->id, 'academic_term_id' => $term->id, 'section' => 'A', 'capacity' => 20, 'status' => 'Active',
        ], $overrides));
    }

    private function student(Program $program): Student
    {
        return Student::create([
            'program_id' => $program->id, 'student_number' => 'STU-'.uniqid(), 'first_name' => 'Test', 'last_name' => 'Student', 'year_level' => 1,
        ]);
    }
}
