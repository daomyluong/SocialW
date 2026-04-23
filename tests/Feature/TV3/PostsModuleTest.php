<?php

namespace Tests\Feature\TV3;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac_tv3_01_authenticated_user_can_create_text_post(): void
    {
        $user = User::factory()->create([
            'username' => 'tv3_author_1',
            'display_name' => 'TV3 Author 1',
        ]);

        $this->actingAs($user)
            ->post(route('posts3.store'), [
                'content' => 'TV3 text post content',
                'visibility' => 'public',
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'TV3 text post content',
            'visibility' => 'public',
        ]);
    }

    public function test_ac_tv3_02_post_content_validation_is_enforced(): void
    {
        $user = User::factory()->create([
            'username' => 'tv3_author_2',
            'display_name' => 'TV3 Author 2',
        ]);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('posts3.store'), [
                'content' => '',
                'visibility' => 'public',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors(['content']);
    }

    public function test_ac_tv3_03_only_author_can_update_or_delete_post(): void
    {
        $owner = User::factory()->create([
            'username' => 'tv3_owner',
            'display_name' => 'TV3 Owner',
        ]);

        $other = User::factory()->create([
            'username' => 'tv3_other',
            'display_name' => 'TV3 Other',
        ]);

        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'Owner content',
            'visibility' => 'public',
            'is_deleted' => 0,
        ]);

        $this->actingAs($other)
            ->put(route('posts3.update', $post->id), [
                'content' => 'Unauthorized update',
                'visibility' => 'public',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('posts3.destroy', $post->id))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'content' => 'Owner content',
            'is_deleted' => 0,
        ]);
    }

    public function test_ac_tv3_04_visibility_rules_public_follower_private_are_applied(): void
    {
        $author = User::factory()->create([
            'username' => 'tv3_visibility_author',
            'display_name' => 'Visibility Author',
        ]);

        $follower = User::factory()->create([
            'username' => 'tv3_visibility_follower',
            'display_name' => 'Visibility Follower',
        ]);

        $stranger = User::factory()->create([
            'username' => 'tv3_visibility_stranger',
            'display_name' => 'Visibility Stranger',
        ]);

        DB::table('followers')->insert([
            'follower_user_id' => $follower->id,
            'following_user_id' => $author->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'content' => 'PUBLIC_POST_TEXT',
            'visibility' => 'public',
            'is_deleted' => 0,
        ]);

        Post::create([
            'user_id' => $author->id,
            'content' => 'FOLLOWER_POST_TEXT',
            'visibility' => 'follower',
            'is_deleted' => 0,
        ]);

        Post::create([
            'user_id' => $author->id,
            'content' => 'PRIVATE_POST_TEXT',
            'visibility' => 'private',
            'is_deleted' => 0,
        ]);

        $this->actingAs($follower)
            ->get(route('home'))
            ->assertSee('PUBLIC_POST_TEXT')
            ->assertSee('FOLLOWER_POST_TEXT')
            ->assertDontSee('PRIVATE_POST_TEXT');

        $this->actingAs($stranger)
            ->get(route('home'))
            ->assertSee('PUBLIC_POST_TEXT')
            ->assertDontSee('FOLLOWER_POST_TEXT')
            ->assertDontSee('PRIVATE_POST_TEXT');
    }
}
