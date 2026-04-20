<?php

namespace Tests\Feature\TV4;

use Tests\TestCase;

class SocialModuleTest extends TestCase
{
    public function test_ac_tv4_01_user_can_follow_another_user(): void
    {
        $this->markTestSkipped('AC-TV4-01: implement follow endpoint and assert follow relation is created.');
    }

    public function test_ac_tv4_02_follow_duplicate_is_idempotent(): void
    {
        $this->markTestSkipped('AC-TV4-02: repeated follow should not create duplicate follower records.');
    }

    public function test_ac_tv4_03_like_and_unlike_update_like_count_correctly(): void
    {
        $this->markTestSkipped('AC-TV4-03: implement like/unlike and assert like_count changes correctly.');
    }

    public function test_ac_tv4_04_comment_creation_updates_comment_count(): void
    {
        $this->markTestSkipped('AC-TV4-04: implement comment flow and assert comment_count increments correctly.');
    }
}
