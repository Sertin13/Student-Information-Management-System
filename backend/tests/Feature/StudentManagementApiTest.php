<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_student_and_retrieve_list_and_details(): void
    {
        Sanctum::actingAs($this->user('Admin'));
        $payload = $this->studentPayload();

        $student = $this->postJson('/api/v1/students', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.student_number', $payload['student_number'])
            ->json('data');

        $this->getJson('/api/v1/students')
            ->assertOk()->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.id', $student['id']);

        $this->getJson('/api/v1/students/'.$student['id'])
            ->assertOk()->assertJsonPath('data.id', $student['id'])
            ->assertJsonPath('data.email', $payload['email']);
    }

    public function test_admin_can_update_a_student_and_invalid_update_preserves_existing_values(): void
    {
        $student = $this->student();
        Sanctum::actingAs($this->user('Admin'));
        $payload = $this->studentPayload([
            'student_number' => $student->student_number,
            'first_name' => 'Updated',
            'email' => $student->email,
        ]);

        $this->putJson('/api/v1/students/'.$student->id, $payload)
            ->assertOk()->assertJsonPath('data.first_name', 'Updated');

        $this->putJson('/api/v1/students/'.$student->id, array_merge($payload, ['email' => 'invalid-email']))
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
            'email' => $student->email,
        ]);
    }

    public function test_admin_can_delete_an_unreferenced_student(): void
    {
        $student = $this->student();
        Sanctum::actingAs($this->user('Admin'));

        $this->deleteJson('/api/v1/students/'.$student->id)->assertNoContent();

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_creation_rejects_duplicates_invalid_email_missing_fields_and_invalid_program_without_changes(): void
    {
        $existing = $this->student();
        Sanctum::actingAs($this->user('Admin'));

        $this->postJson('/api/v1/students', $this->studentPayload(['student_number' => $existing->student_number]))
            ->assertUnprocessable()->assertJsonValidationErrors('student_number');
        $this->postJson('/api/v1/students', $this->studentPayload(['email' => 'invalid-email']))
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/api/v1/students', $this->studentPayload(['program_id' => 999999]))
            ->assertUnprocessable()->assertJsonValidationErrors('program_id');
        $this->postJson('/api/v1/students', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['student_number', 'first_name', 'last_name', 'program_id', 'year_level', 'status']);

        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('students', ['id' => $existing->id, 'student_number' => $existing->student_number]);
    }

    public function test_student_management_requires_authentication(): void
    {
        $this->getJson('/api/v1/students')->assertUnauthorized();
        $this->postJson('/api/v1/students', $this->studentPayload())->assertUnauthorized();

        $this->assertDatabaseCount('students', 0);
    }

    public function test_student_role_cannot_manage_student_records(): void
    {
        $student = $this->student();
        Sanctum::actingAs($this->user('Student'));

        $this->getJson('/api/v1/students')->assertForbidden();
        $this->getJson('/api/v1/students/'.$student->id)->assertForbidden();
        $this->postJson('/api/v1/students', $this->studentPayload())->assertForbidden();
        $this->putJson('/api/v1/students/'.$student->id, $this->studentPayload(['student_number' => $student->student_number]))->assertForbidden();
        $this->deleteJson('/api/v1/students/'.$student->id)->assertForbidden();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'first_name' => $student->first_name]);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role_id' => Role::create(['name' => $role])->id]);
    }

    private function student(array $attributes = []): Student
    {
        return Student::create(array_merge($this->studentPayload(), $attributes));
    }

    private function studentPayload(array $overrides = []): array
    {
        $program = Program::firstOrCreate(['code' => 'STUDENT-TEST'], ['name' => 'Student Test']);

        return array_merge([
            'student_number' => 'STU-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => uniqid().'@example.test',
            'program_id' => $program->id,
            'year_level' => 1,
            'status' => 'Regular',
        ], $overrides);
    }
}
