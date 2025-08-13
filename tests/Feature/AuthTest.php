<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * with correct data
     */

    public function test_user_can_register_successfully()
    {
        $payload = [
            'name' => "Arjun Bhati",
            'email' => 'arjun@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'name', 'email', 'created_at', 'token']
        ]);

        $this->assertDatabaseHas('users', ['email' => 'arjun@example.com']);
    }

    public function test_user_registration_fails_with_invalid_data()
    {
        $payload = [
            'name' => "",
            'email' => 'arjun',
            'password' => 'password',
            'password_confirmation' => 'yes'
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);
        $response->assertStatus(422)->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    public function test_user_login_fails_with_invalid_data()
    {
        $payload = [
            'email' => "arjun",
            'password' => '',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);
        $response->assertStatus(422)->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);
    }

    public function test_user_login_with_correct_credentials()
    {
        User::factory()->create([
            'name' => "Arjun Bhati",
            'email' => 'arjun@example.com',
            'password' => 'password',
        ]);

        $payload = [
            'email' => "arjun@example.com",
            'password' => 'password',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'name', 'email', 'created_at', 'token']
        ]);
    }

    public function test_user_login_with_incorrect_credentials()
    {
        User::factory()->create([
            'name' => "Arjun Bhati",
            'email' => 'arjun@example.com',
            'password' => 'password',
        ]);

        $payload = [
            'email' => "arjun@exaple.com",
            'password' => 'passwrd',
        ];

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertStatus(401)->assertJsonStructure([
            'status',
            'message',
        ]);
    }
}
