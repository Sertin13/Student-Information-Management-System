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

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_students(): void
    {
        Sanctum::actingAs($this->user('Admin'));

        $this->getJson('/api/v1/students')->assertOk()->assertJsonPath('success', true);
    }

    public function test_staff_can_manage_students_and_academic_records(): void
    {
        $student = $this->student();
        Sanctum::actingAs($this->user('Staff'));

        $this->getJson('/api/v1/students')->assertOk();
        $this->getJson("/api/v1/students/{$student->id}/academic-record")->assertOk();
    }

    public function test_instructor_can_access_assigned_offerings_and_update_their_grade(): void
    {
        $instructor = $this->user('Instructor');
        [, $grade] = $this->enrollmentWithGrade($instructor);
        Sanctum::actingAs($instructor);

        $this->getJson('/api/v1/my/course-offerings')->assertOk()->assertJsonPath('data.total', 1);
        $this->putJson("/api/v1/my/grades/{$grade->id}", ['grade' => 91.50, 'remarks' => 'Passed'])
            ->assertOk()->assertJsonPath('data.grade', 91.5);
    }

    public function test_instructor_cannot_modify_a_grade_from_another_offering(): void
    {
        $authorizedInstructor = $this->user('Instructor');
        [, $grade] = $this->enrollmentWithGrade($this->user('Admin'));
        Sanctum::actingAs($authorizedInstructor);

        $this->putJson("/api/v1/my/grades/{$grade->id}", ['grade' => 80])
            ->assertForbidden()
            ->assertExactJson(['success' => false, 'message' => 'Forbidden.']);
    }

    public function test_student_can_access_only_their_own_profile_and_academic_record(): void
    {
        $user = $this->user('Student');
        $ownStudent = $this->student(['user_id' => $user->id]);
        [$enrollment] = $this->enrollmentWithGrade($this->user('Instructor'), $ownStudent);
        $otherStudent = $this->student();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/my/student')->assertOk()->assertJsonPath('data.id', $ownStudent->id);
        $this->getJson('/api/v1/my/enrollments')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/v1/my/grades')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson("/api/v1/students/{$ownStudent->id}/academic-record")->assertOk();
        $this->getJson("/api/v1/students/{$otherStudent->id}/academic-record")
            ->assertForbidden()
            ->assertExactJson(['success' => false, 'message' => 'Forbidden.']);
    }

    private function user(string $role): User
    {
        $roleId = Role::firstOrCreate(['name' => $role])->id;

        return User::factory()->create(['role_id' => $roleId]);
    }

    private function student(array $attributes = []): Student
    {
        return Student::create(array_merge([
            'program_id' => Program::firstOrCreate(['code' => 'BSIT'], ['name' => 'BS Information Technology'])->id,
            'student_number' => 'STU-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'year_level' => 1,
        ], $attributes));
    }

    /** @return array{Enrollment, Grade} */
    private function enrollmentWithGrade(User $instructor, ?Student $student = null): array
    {
        $course = Course::create(['course_code' => 'IT-'.uniqid(), 'course_title' => 'Authorization Test', 'units' => 3]);
        $term = AcademicTerm::create(['academic_year' => '2026-2027', 'semester' => 'First', 'start_date' => '2026-06-01', 'end_date' => '2026-10-01']);
        $offering = CourseOffering::create(['course_id' => $course->id, 'academic_term_id' => $term->id, 'instructor_id' => $instructor->id, 'section' => 'A', 'capacity' => 30]);
        $enrollment = Enrollment::create(['student_id' => ($student ?? $this->student())->id, 'course_offering_id' => $offering->id, 'enrollment_date' => '2026-06-01']);

        return [$enrollment, Grade::create(['enrollment_id' => $enrollment->id, 'grade' => 75])];
    }
}
