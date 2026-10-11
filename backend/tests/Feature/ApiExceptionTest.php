<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_api_resource_returns_safe_json_404(): void
    {
        Sanctum::actingAs(User::factory()->create(['role_id' => Role::create(['name' => 'Admin'])->id]));
        $this->getJson('/api/v1/students/999999')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Resource not found.']);
    }
}
