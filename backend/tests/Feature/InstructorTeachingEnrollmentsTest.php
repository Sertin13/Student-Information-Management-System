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

class InstructorTeachingEnrollmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_receives_only_enrollments_for_assigned_offerings_with_safe_relationship_fields(): void
    {
        $instructor = $this->user('Instructor');
        $own = $this->enrollment($instructor);
        $this->enrollment($this->user('Instructor'));
        Grade::create(['enrollment_id' => $own->id, 'grade' => 90, 'remarks' => 'Passed']);
        Sanctum::actingAs($instructor);

        $this->getJson('/api/v1/my/teaching-enrollments?per_page=1')
            ->assertOk()->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.id', $own->id)
            ->assertJsonPath('data.data.0.student.student_number', 'STU-'.str_pad($own->id, 4, '0', STR_PAD_LEFT))
            ->assertJsonPath('data.data.0.course_offering.course.course_code', 'IT'.$own->id)
            ->assertJsonPath('data.data.0.grade.grade', 90)
            ->assertJsonMissingPath('data.data.0.student.email');
    }

    public function test_non_instructor_roles_receive_forbidden(): void
    {
        foreach (['Admin', 'Staff', 'Student'] as $role) {
            Sanctum::actingAs($this->user($role));
            $this->getJson('/api/v1/my/teaching-enrollments')->assertForbidden();
        }
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role_id' => Role::firstOrCreate(['name' => $role])->id]);
    }

    private function enrollment(User $instructor): Enrollment
    {
        $id = Course::count() + 1;
        $program = Program::firstOrCreate(['code' => 'BSIT'], ['name' => 'BSIT']);
        $student = Student::create(['program_id' => $program->id, 'student_number' => 'STU-'.str_pad($id, 4, '0', STR_PAD_LEFT), 'first_name' => 'Test', 'last_name' => 'Student', 'year_level' => 1]);
        $course = Course::create(['course_code' => 'IT'.$id, 'course_title' => 'Test '.$id, 'units' => 3]);
        $term = AcademicTerm::create(['academic_year' => '2026-'.uniqid(), 'semester' => 'First', 'start_date' => '2026-06-01', 'end_date' => '2026-10-01']);
        $offering = CourseOffering::create(['course_id' => $course->id, 'academic_term_id' => $term->id, 'instructor_id' => $instructor->id, 'section' => 'A', 'capacity' => 20]);
        return Enrollment::create(['student_id' => $student->id, 'course_offering_id' => $offering->id, 'enrollment_date' => '2026-06-01']);
    }
}
