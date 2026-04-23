<?php

namespace Tests\Feature\TV4;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SocialModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac_tv4_01_user_can_follow_another_user(): void
    {
        $me = User::factory()->create([
            'username' => 'tv4_me_1',
            'display_name' => 'TV4 Me 1',
        ]);

        $target = User::factory()->create([
            'username' => 'tv4_target_1',
            'display_name' => 'TV4 Target 1',
        ]);

        $this->actingAs($me)
            ->postJson(route('users.follow', $target->id))
            ->assertOk()
            ->assertJson(['status' => 'followed']);

        $this->assertDatabaseHas('followers', [
            'follower_user_id' => $me->id,
            'following_user_id' => $target->id,
        ]);
    }

    public function test_ac_tv4_02_follow_duplicate_is_idempotent(): void
    {
        $me = User::factory()->create([
            'username' => 'tv4_me_2',
            'display_name' => 'TV4 Me 2',
        ]);

        $target = User::factory()->create([
            'username' => 'tv4_target_2',
            'display_name' => 'TV4 Target 2',
        ]);

        $this->actingAs($me)->postJson(route('users.follow', $target->id))->assertOk();
        $countAfterFirst = DB::table('followers')
            ->where('follower_user_id', $me->id)
            ->where('following_user_id', $target->id)
            ->count();

        $this->actingAs($me)->postJson(route('users.follow', $target->id))->assertOk();
        $countAfterSecond = DB::table('followers')
            ->where('follower_user_id', $me->id)
            ->where('following_user_id', $target->id)
            ->count();

        $this->assertTrue($countAfterFirst <= 1);
        $this->assertTrue($countAfterSecond <= 1);
    }

    public function test_ac_tv4_03_like_and_unlike_update_like_count_correctly(): void
    {
        $author = User::factory()->create([
            'username' => 'tv4_like_author',
            'display_name' => 'TV4 Like Author',
        ]);

        $liker = User::factory()->create([
            'username' => 'tv4_like_user',
            'display_name' => 'TV4 Like User',
        ]);

        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'LIKE_POST_TEXT',
            'visibility' => 'public',
            'is_deleted' => 0,
            'like_count' => 0,
        ]);

        $this->actingAs($liker)
            ->post(route('posts.like', $post->id))
            ->assertRedirect();

        $post->refresh();
        $this->assertSame(1, (int) $post->like_count);

        $this->actingAs($liker)
            ->post(route('posts.like', $post->id))
            ->assertRedirect();

        $post->refresh();
        $this->assertSame(0, (int) $post->like_count);
    }

    public function test_ac_tv4_04_comment_creation_updates_comment_count(): void
    {
        $author = User::factory()->create([
            'username' => 'tv4_comment_author',
            'display_name' => 'TV4 Comment Author',
        ]);

        $commenter = User::factory()->create([
            'username' => 'tv4_comment_user',
            'display_name' => 'TV4 Comment User',
        ]);

        $post = Post::create([
            'user_id' => $author->id,
            'content' => 'COMMENT_POST_TEXT',
            'visibility' => 'public',
            'is_deleted' => 0,
            'comment_count' => 0,
        ]);

        $this->actingAs($commenter)
            ->post(route('comments.store', $post->id), [
                'content' => 'TV4 comment content',
            ])
            ->assertRedirect();

        $post->refresh();
        $this->assertSame(1, (int) $post->comment_count);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'content' => 'TV4 comment content',
        ]);
    }
}
