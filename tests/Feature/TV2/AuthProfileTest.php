<?php

namespace Tests\Feature\TV2;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac_tv2_01_guest_is_redirected_when_accessing_profile_requires_auth(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
    }

    public function test_ac_tv2_02_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create([
            'username' => 'tv2_user_1',
            'display_name' => 'TV2 User 1',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('profile.show', $user->id));

        $this->actingAs($user)
            ->get(route('profile.show', $user->id))
            ->assertOk();
    }

    public function test_ac_tv2_03_user_can_update_own_profile_display_name_and_bio(): void
    {
        $user = User::factory()->create([
            'username' => 'tv2_user_2',
            'display_name' => 'Old Name',
            'bio' => 'Old bio',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'display_name' => 'New Name',
                'bio' => 'New bio text',
            ])
            ->assertRedirect(route('profile.show', $user->id));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'display_name' => 'New Name',
            'bio' => 'New bio text',
        ]);
    }

    public function test_ac_tv2_04_user_cannot_update_other_user_profile(): void
    {
        $owner = User::factory()->create([
            'username' => 'tv2_owner',
            'display_name' => 'Owner',
        ]);

        $other = User::factory()->create([
            'username' => 'tv2_other',
            'display_name' => 'Other',
        ]);

        $this->actingAs($other)
            ->put(route('profile.update.user', $owner->id), [
                'display_name' => 'Hacked Name',
                'bio' => 'Hacked bio',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'id' => $owner->id,
            'display_name' => 'Hacked Name',
        ]);
    }
}
