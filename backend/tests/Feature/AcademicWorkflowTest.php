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

class AcademicWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_enrollment(): void
    {
        [$student, $offering] = $this->studentAndOffering();
        Sanctum::actingAs($this->user('Admin'));

        $this->postJson('/api/v1/enrollments', $this->enrollmentPayload($student, $offering))
            ->assertCreated()->assertJsonPath('data.student_id', $student->id)->assertJsonPath('data.course_offering_id', $offering->id);
    }

    public function test_enrollment_search_matches_enrollment_student_and_related_course_fields(): void
    {
        [$student, $offering] = $this->studentAndOffering();
        $student->update(['student_number' => '2023301556', 'first_name' => 'KAYEL', 'last_name' => 'KAYEL']);
        $enrollment = Enrollment::create($this->enrollmentPayload($student, $offering));
        Sanctum::actingAs($this->user('Admin'));

        foreach ([$student->id, '2023301556', 'KAYEL', $enrollment->id] as $term) {
            $this->getJson('/api/v1/enrollments?search='.$term)
                ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id', $enrollment->id);
        }
        $this->getJson('/api/v1/enrollments?search=does-not-exist')->assertOk()->assertJsonPath('data.total', 0);
    }

    public function test_ungraded_enrollment_filter_excludes_existing_grades(): void
    {
        [$student, $offering] = $this->studentAndOffering();
        $ungraded = Enrollment::create($this->enrollmentPayload($student, $offering));
        [$gradedStudent, $gradedOffering] = $this->studentAndOffering();
        $graded = Enrollment::create($this->enrollmentPayload($gradedStudent, $gradedOffering));
        Grade::create(['enrollment_id' => $graded->id, 'grade' => 90]);
        Sanctum::actingAs($this->user('Admin'));

        $this->getJson('/api/v1/enrollments?without_grade=1')
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id', $ungraded->id);
    }

    public function test_enrollment_rejects_an_invalid_student_reference(): void
    {
        [, $offering] = $this->studentAndOffering();
        Sanctum::actingAs($this->user('Admin'));

        $this->postJson('/api/v1/enrollments', $this->enrollmentPayload(999999, $offering))->assertUnprocessable()->assertJsonValidationErrors('student_id');
    }

    public function test_enrollment_rejects_an_invalid_course_offering_reference(): void
    {
        [$student] = $this->studentAndOffering();
        Sanctum::actingAs($this->user('Admin'));

        $this->postJson('/api/v1/enrollments', $this->enrollmentPayload($student, 999999))->assertUnprocessable()->assertJsonValidationErrors('course_offering_id');
    }

    public function test_duplicate_enrollment_returns_a_conflict_response(): void
    {
        [$student, $offering] = $this->studentAndOffering();
        Enrollment::create($this->enrollmentPayload($student, $offering));
        Sanctum::actingAs($this->user('Admin'));

        $this->postJson('/api/v1/enrollments', $this->enrollmentPayload($student, $offering))
            ->assertConflict()->assertExactJson(['success' => false, 'message' => 'Duplicate or conflicting record.']);

        $this->assertDatabaseCount('enrollments', 1);
        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'course_offering_id' => $offering->id, 'status' => 'Enrolled']);
    }

    public function test_referenced_course_delete_returns_a_safe_conflict(): void
    {
        [, $offering] = $this->studentAndOffering();
        Sanctum::actingAs($this->user('Admin'));

        $this->deleteJson('/api/v1/courses/'.$offering->course_id)
            ->assertConflict()
            ->assertExactJson(['success' => false, 'message' => 'This record cannot be deleted because it is still in use.']);
    }

    public function test_instructor_can_submit_and_update_a_grade_for_an_assigned_offering(): void
    {
        $instructor = $this->user('Instructor');
        [$student, $offering] = $this->studentAndOffering($instructor);
        $enrollment = Enrollment::create($this->enrollmentPayload($student, $offering));
        Sanctum::actingAs($instructor);

        $grade = $this->postJson('/api/v1/my/grades', ['enrollment_id' => $enrollment->id, 'grade' => 88.5, 'remarks' => 'Good'])
            ->assertCreated()->json('data');
        $this->putJson('/api/v1/my/grades/'.$grade['id'], ['grade' => 92, 'remarks' => 'Excellent'])
            ->assertOk()->assertJsonPath('data.grade', 92)->assertJsonPath('data.remarks', 'Excellent');
    }

    public function test_grade_submission_rejects_an_invalid_enrollment(): void
    {
        Sanctum::actingAs($this->user('Instructor'));

        $this->postJson('/api/v1/my/grades', ['enrollment_id' => 999999, 'grade' => 80])
            ->assertUnprocessable()->assertJsonValidationErrors('enrollment_id');
    }

    public function test_instructor_cannot_modify_a_grade_from_another_instructors_offering(): void
    {
        $owner = $this->user('Instructor');
        [$student, $offering] = $this->studentAndOffering($owner);
        $enrollment = Enrollment::create($this->enrollmentPayload($student, $offering));
        $grade = Grade::create(['enrollment_id' => $enrollment->id, 'grade' => 75]);
        Sanctum::actingAs($this->user('Instructor'));

        $this->putJson('/api/v1/my/grades/'.$grade->id, ['grade' => 90])
            ->assertForbidden()->assertExactJson(['success' => false, 'message' => 'Forbidden.']);

        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'grade' => 75]);
    }

    public function test_grade_update_rejects_values_outside_the_allowed_range_without_changing_the_grade(): void
    {
        $instructor = $this->user('Instructor');
        [$student, $offering] = $this->studentAndOffering($instructor);
        $enrollment = Enrollment::create($this->enrollmentPayload($student, $offering));
        $grade = Grade::create(['enrollment_id' => $enrollment->id, 'grade' => 75]);
        Sanctum::actingAs($instructor);

        foreach ([100.01, -0.01] as $invalidGrade) {
            $this->putJson('/api/v1/my/grades/'.$grade->id, ['grade' => $invalidGrade])
                ->assertUnprocessable()->assertJsonValidationErrors('grade');

            $this->assertDatabaseHas('grades', ['id' => $grade->id, 'grade' => 75]);
        }
    }

    public function test_unauthenticated_user_cannot_update_a_grade(): void
    {
        $instructor = $this->user('Instructor');
        [$student, $offering] = $this->studentAndOffering($instructor);
        $enrollment = Enrollment::create($this->enrollmentPayload($student, $offering));
        $grade = Grade::create(['enrollment_id' => $enrollment->id, 'grade' => 75]);

        $this->putJson('/api/v1/my/grades/'.$grade->id, ['grade' => 90])
            ->assertUnauthorized();

        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'grade' => 75]);
    }

    public function test_staff_academic_record_contains_student_enrollment_offering_course_term_and_grade(): void
    {
        [$student, $offering] = $this->studentAndOffering();
        $enrollment = Enrollment::create($this->enrollmentPayload($student, $offering));
        Grade::create(['enrollment_id' => $enrollment->id, 'grade' => 90]);
        Sanctum::actingAs($this->user('Staff'));

        $this->getJson('/api/v1/students/'.$student->id.'/academic-record')
            ->assertOk()->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.records.0.id', $enrollment->id)
            ->assertJsonPath('data.records.0.course_offering.course.id', $offering->course_id)
            ->assertJsonPath('data.records.0.course_offering.academic_term.id', $offering->academic_term_id)
            ->assertJsonPath('data.records.0.grade.grade', 90);
    }

    public function test_student_can_retrieve_their_own_academic_record(): void
    {
        $user = $this->user('Student');
        [$student, $offering] = $this->studentAndOffering(null, $user);
        Enrollment::create($this->enrollmentPayload($student, $offering));
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/students/'.$student->id.'/academic-record')->assertOk()->assertJsonPath('data.student.id', $student->id);
    }

    public function test_student_cannot_retrieve_another_students_academic_record(): void
    {
        $user = $this->user('Student');
        [$ownStudent] = $this->studentAndOffering(null, $user);
        [$otherStudent] = $this->studentAndOffering();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/students/'.$otherStudent->id.'/academic-record')
            ->assertForbidden()->assertExactJson(['success' => false, 'message' => 'Forbidden.']);
    }

    public function test_missing_academic_record_student_returns_clean_404(): void
    {
        Sanctum::actingAs($this->user('Admin'));

        $this->getJson('/api/v1/students/999999/academic-record')
            ->assertNotFound()->assertExactJson(['success' => false, 'message' => 'Resource not found.']);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $role])->id]);
    }

    /** @return array{Student, CourseOffering} */
    private function studentAndOffering(?User $instructor = null, ?User $studentUser = null): array
    {
        $program = Program::firstOrCreate(['code' => 'BSIT'], ['name' => 'BS Information Technology']);
        $student = Student::create(['user_id' => $studentUser?->id, 'program_id' => $program->id, 'student_number' => 'STU-'.uniqid(), 'first_name' => 'Test', 'last_name' => 'Student', 'year_level' => 1]);
        $course = Course::create(['course_code' => 'IT-'.uniqid(), 'course_title' => 'Testing', 'units' => 3]);
        $term = AcademicTerm::create(['academic_year' => '2026-'.uniqid(), 'semester' => 'First', 'start_date' => '2026-06-01', 'end_date' => '2026-10-01']);
        $offering = CourseOffering::create(['course_id' => $course->id, 'academic_term_id' => $term->id, 'instructor_id' => $instructor?->id, 'section' => 'A', 'capacity' => 30]);

        return [$student, $offering];
    }

    private function enrollmentPayload(Student|int $student, CourseOffering|int $offering): array
    {
        return ['student_id' => $student instanceof Student ? $student->id : $student, 'course_offering_id' => $offering instanceof CourseOffering ? $offering->id : $offering, 'enrollment_date' => '2026-06-01', 'status' => 'Enrolled'];
    }
}
