<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\AcademicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_seeder_creates_required_records_and_relationships(): void
    {
        $this->seed(AcademicSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('programs', 3);
        $this->assertDatabaseCount('students', 100);
        $this->assertDatabaseCount('courses', 20);
        $this->assertDatabaseCount('academic_terms', 2);
        $this->assertDatabaseCount('course_offerings', 20);
        $this->assertDatabaseCount('enrollments', 200);
        $this->assertDatabaseCount('grades', 200);
        $this->assertTrue(Student::where('user_id', User::where('email', 'student@example.com')->value('id'))->exists());
        $this->assertSame(0, CourseOffering::whereDoesntHave('enrollments', null, '=', 10)->count());
        $this->assertSame(0, Enrollment::whereDoesntHave('student')->orWhereDoesntHave('courseOffering')->count());
        $this->assertSame(0, Grade::whereDoesntHave('enrollment')->count());
    }
}
