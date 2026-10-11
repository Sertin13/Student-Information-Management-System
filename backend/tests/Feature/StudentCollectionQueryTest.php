<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentCollectionQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs(User::factory()->create(['role_id' => Role::create(['name' => 'Admin'])->id]));
    }

    public function test_students_can_be_searched_across_identity_fields(): void
    {
        $match = $this->student(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);
        $this->student(['first_name' => 'Maria', 'last_name' => 'Santos']);

        $this->getJson('/api/v1/students?search=Juan')
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id', $match->id);
    }

    public function test_empty_filter_parameters_do_not_filter_out_students(): void
    {
        $this->student(['student_number' => 'EMPTY-FILTER-1']);
        $this->student(['student_number' => 'EMPTY-FILTER-2']);

        $this->getJson('/api/v1/students?search=&program_id=&year_level=&status=&sort=last_name&direction=asc')
            ->assertOk()->assertJsonPath('data.total', 2);
    }

    public function test_non_empty_filters_remain_applied(): void
    {
        $program = $this->program('FILTER-PROGRAM');
        $match = $this->student(['program_id' => $program->id, 'year_level' => 3, 'status' => 'Regular']);
        $this->student(['program_id' => $program->id, 'year_level' => 2, 'status' => 'Regular']);

        $this->getJson("/api/v1/students?program_id={$program->id}&year_level=3&status=Regular")
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id', $match->id);
    }

    public function test_students_can_be_filtered_and_combined(): void
    {
        $it = $this->program('BSIT');
        $cs = $this->program('BSCS');
        $match = $this->student(['program_id' => $it->id, 'year_level' => 2, 'status' => 'Regular', 'first_name' => 'Juan']);
        $this->student(['program_id' => $it->id, 'year_level' => 1, 'status' => 'Regular', 'first_name' => 'Juan']);
        $this->student(['program_id' => $cs->id, 'year_level' => 2, 'status' => 'Irregular', 'first_name' => 'Juan']);

        $this->getJson("/api/v1/students?search=Juan&program_id={$it->id}&year_level=2&status=Regular")
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.id', $match->id);
    }

    public function test_students_can_be_sorted_ascending_and_descending(): void
    {
        $this->student(['last_name' => 'Zulueta']);
        $this->student(['last_name' => 'Alvarez']);

        $this->getJson('/api/v1/students?sort=last_name&direction=asc')->assertOk()->assertJsonPath('data.data.0.last_name', 'Alvarez');
        $this->getJson('/api/v1/students?sort=last_name&direction=desc')->assertOk()->assertJsonPath('data.data.0.last_name', 'Zulueta');
    }

    public function test_students_are_paginated_with_a_bounded_custom_page_size(): void
    {
        foreach (range(1, 3) as $number) $this->student(['student_number' => "STU-{$number}"]);

        $this->getJson('/api/v1/students?page=2&per_page=2')
            ->assertOk()->assertJsonPath('data.current_page', 2)->assertJsonPath('data.per_page', 2)->assertJsonCount(1, 'data.data');
        $this->getJson('/api/v1/students?per_page=999')->assertOk()->assertJsonPath('data.per_page', 100);
    }

    public function test_invalid_sort_and_direction_are_rejected(): void
    {
        $this->getJson('/api/v1/students?sort=password')->assertUnprocessable();
        $this->getJson('/api/v1/students?direction=sideways')->assertUnprocessable();
    }

    private function program(string $code): Program
    {
        return Program::firstOrCreate(['code' => $code], ['name' => $code]);
    }

    private function student(array $attributes = []): Student
    {
        return Student::create(array_merge([
            'program_id' => $this->program('BSIT')->id,
            'student_number' => 'STU-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => uniqid().'@example.test',
            'year_level' => 1,
            'status' => 'Regular',
        ], $attributes));
    }
}
