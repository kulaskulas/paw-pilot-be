<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUsers;

use App\Models\User;
use App\Models\UserProfile;

class UserProfileTest extends TestCase
{
    private const TESTURL = '/api/user-profile/store';

    use RefreshDatabase, AuthenticatesUsers;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // public function test_user_cannot_create_duplicate_profile(): void
    // {
    //     //
    // }

    // public function test_user_can_view_own_profile(): void
    // {
    //     //
    // }

    public function test_user_can_update_own_profile(): void
    {
        $auth = $this->authenticateUser();
        $user = $auth['user'];

        $user->profile()->create([
            'first_name' => 'Karl',
            'last_name' => 'Cabangon',
            'phone_number' => '09123456789',
            'address' => 'Philippines',
        ]);

         $updateProfile = $this->post('/api/user-profile/update', [
            // 'user_id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Updated',
            'phone_number' => null,
            'address' => null,
        ]);

        $updateProfile->assertOk()
            ->assertExactJson([
                'message' => 'Profile updated successfully.',
            ]);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Updated',
        ]);
    }

    public function test_user_can_create_profile_data(): void
    {
        $auth = $this->authenticateUser();
        $user = $auth['user'];

        $storeProfile = $this->post(self::TESTURL, [
            'user_id' => $user->id,
            'first_name' => 'test',
            'last_name' => 'test',
            'phone_number' => '094543635',
            'address' => 'address',
        ]);

        $storeProfile->assertCreated()
            ->assertExactJson([
                'message' => 'User profile created successfully.',
            ]);
    }

    public function test_user_cannot_submit_html_in_profile(): void
    {
        $auth = $this->authenticateUser();
        $user = $auth['user'];

        $storeProfile = $this->post(self::TESTURL, [
            'user_id' => $user->id,
            'first_name' => '<script>test<script>',
            'last_name' => '<script>test</script>',
            'phone_number' => '094543635',
            'address' => null,
        ]);

        $storeProfile->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
            ]);
    }

    public function test_user_cannot_create_duplicate_profile(): void
    {
        $auth = $this->authenticateUser();
        $user = $auth['user'];

        $user->profile()->create([
            'first_name' => 'Karl',
            'last_name' => 'Cabangon',
            'phone_number' => '094543635',
        ]);

        $response = $this->postJson(self::TESTURL, [
            'first_name' => 'Another',
            'last_name' => 'Profile',
            'phone_number' => '09123456789',
        ]);

        $response->assertConflict();

        $this->assertDatabaseCount('user_profiles', 1);
    }
}
