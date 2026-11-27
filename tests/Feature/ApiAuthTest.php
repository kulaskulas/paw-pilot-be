<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_json_with_token(): void
    {
        $response = $this->post('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'Jane@Example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ])
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_register_validation_error_returns_json_without_accept_header(): void
    {
        $response = $this->withHeaders(['Accept' => '*/*'])->post('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_and_fetch_authenticated_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'TEST@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ]);

        $token = $loginResponse->json('token');

        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/user');

        $profileResponse->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'test@example.com');
    }

    public function test_authenticated_user_without_profile_gets_403_json(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('postman')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/user');

        $response->assertForbidden()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson([
                'message' => 'Please complete your profile first.',
                'code' => 'PROFILE_INCOMPLETE',
            ]);
    }

    public function test_login_with_invalid_credentials_returns_401_json(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_unauthenticated_api_request_returns_401_json_without_accept_header(): void
    {
         $response = $this->get('/api/user');

        $response->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_can_logout_and_revoke_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('postman')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $logoutResponse->assertOk()
            ->assertExactJson([
                'message' => 'Logged out successfully.',
            ]);


        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'postman',
        ]);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/user')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json');
    }
}
