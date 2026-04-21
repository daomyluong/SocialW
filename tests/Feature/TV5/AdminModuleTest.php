<?php

namespace Tests\Feature\TV5;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac_tv5_01_regular_user_cannot_access_admin_area(): void
    {
        $user = User::factory()->create([
            'username' => 'tv5_regular_user',
            'display_name' => 'TV5 Regular User',
            'role' => 'member',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_ac_tv5_02_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'username' => 'tv5_admin_user',
            'display_name' => 'TV5 Admin User',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_ac_tv5_03_admin_can_hide_or_disable_violating_post(): void
    {
        $admin = User::factory()->create([
            'username' => 'tv5_admin_user_2',
            'display_name' => 'TV5 Admin User 2',
            'role' => 'admin',
        ]);

        $author = User::factory()->create([
            'username' => 'tv5_post_author',
            'display_name' => 'TV5 Post Author',
            'role' => 'member',
        ]);

        $post = Post::create([
            'author_user_id' => $author->id,
            'content' => 'VIOLATION_POST_TEXT',
            'visibility' => 'public',
            'is_deleted' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.posts.delete', $post->id))
            ->assertRedirect();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'is_deleted' => 1,
        ]);
    }

    public function test_ac_tv5_04_admin_can_lock_or_unlock_user_account(): void
    {
        $admin = User::factory()->create([
            'username' => 'tv5_admin_user_3',
            'display_name' => 'TV5 Admin User 3',
            'role' => 'admin',
        ]);

        $target = User::factory()->create([
            'username' => 'tv5_target_user',
            'display_name' => 'TV5 Target User',
            'role' => 'member',
            'is_active' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle_status', $target->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle_status', $target->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => 1,
        ]);
    }
}
