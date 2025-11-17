<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for authentication
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
    }

    protected function headers()
    {
        $token = $this->adminUser->createToken('TestToken')->accessToken;
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }

    public function test_user_list_with_pagination_and_filtering()
    {
        User::factory()->count(15)->create();

        $response = $this->getJson('/api/admin/users?per_page=5', $this->headers());
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links', 'meta']);
        $this->assertCount(5, $response->json('data'));
    }

    public function test_create_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/admin/users', $data, $this->headers());
        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Test User', 'email' => 'testuser@example.com']);
    }

    public function test_show_user_detail()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/admin/users/{$user->id}", $this->headers());
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $user->id]);
    }

    public function test_update_user()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/admin/users/{$user->id}", $data, $this->headers());
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Updated Name', 'email' => 'updated@example.com']);
    }

    public function test_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/admin/users/{$user->id}", [], $this->headers());
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'User deleted successfully']);
    }
}
