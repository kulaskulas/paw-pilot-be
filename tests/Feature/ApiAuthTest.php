<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;

class ApiAuthTest extends TestCase
{
    private const CHANGE_PASSWORD_URL = '/api/change-password';

    use RefreshDatabase, AuthenticatesUsers;

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
        // $user = User::factory()->create();
        // $token = $user->createToken('postman')->plainTextToken;
        $auth = $this->authenticateUser();
        $token = $auth['token'];

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
        // $user = User::factory()->create();
        // $token = $user->createToken('postman')->plainTextToken;

        $auth = $this->authenticateUser();
        $token = $auth['token'];
        $user = $auth['user'];

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

    public function test_user_can_change_password(): void
    {
        $attribute = [
            'name' => 'choks',
            'email' => 'choks@gmail.com',
            'password' => Hash::make('password1234')
        ];

        $this->authenticateUser($attribute);

        $changePasswordResponse = $this->post(self::CHANGE_PASSWORD_URL, [
            'current_password' => 'password1234',
            'password' => 'new-password',
            'password_confirmation' => 'new-password'
        ]);

        $changePasswordResponse->assertOk()
            ->assertExactJson([
                'message' => 'Password changed successfully.',
            ]);
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $attribute = [
            'name' => 'choks',
            'email' => 'choks@gmail.com',
            'password' => Hash::make('password1234')
        ];

        $auth = $this->authenticateUser($attribute);
        $user = $auth['user'];

        $changePasswordResponse = $this->post(self::CHANGE_PASSWORD_URL, [
            'current_password' => 'incorrect-current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password'
        ]);

        $changePasswordResponse->assertUnprocessable()
           ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(
            Hash::check('password1234', $user->fresh()->password)
        );
    }

    public function test_user_cannot_change_password_with_new_password_not_match(): void
    {
        $attribute = [
            'name' => 'choks',
            'email' => 'choks@gmail.com',
            'password' => Hash::make('password1234')
        ];

        $auth = $this->authenticateUser($attribute);
        $user = $auth['user'];

        $changePasswordResponse = $this->post(self::CHANGE_PASSWORD_URL, [
            'current_password' => 'password1234',
            'password' => 'new-password',
            'password_confirmation' => 'new-password-not-match'
        ]);

        $changePasswordResponse->assertUnprocessable()
           ->assertJsonValidationErrors(['password']);

        $this->assertTrue(
            Hash::check('password1234', $user->fresh()->password)
        );
    }


}


