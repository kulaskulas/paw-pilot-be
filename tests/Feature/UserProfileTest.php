<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AuthenticatesUsers;

use App\Models\User;
use App\Models\UserProfile;

class UserProfileTest extends TestCase
{

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

    // public function test_user_can_update_own_profile(): void
    // {
    //     //
    // }

    public function test_user_can_create_profile_data(): void
    {
        $user = $this->authenticateUser();

        $storeProfile = $this->post('/api/user-profile/store', [
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
        $user = $this->authenticateUser();

        $storeProfile = $this->post('/api/user-profile/store', [
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
}
