<?php

namespace Tests\Feature\TV3;

use Tests\TestCase;

class PostsModuleTest extends TestCase
{
    public function test_ac_tv3_01_authenticated_user_can_create_text_post(): void
    {
        $this->markTestSkipped('AC-TV3-01: implement create post endpoint and assert post is stored successfully.');
    }

    public function test_ac_tv3_02_post_content_validation_is_enforced(): void
    {
        $this->markTestSkipped('AC-TV3-02: assert invalid content is rejected with validation errors.');
    }

    public function test_ac_tv3_03_only_author_can_update_or_delete_post(): void
    {
        $this->markTestSkipped('AC-TV3-03: enforce ownership policy for post update/delete actions.');
    }

    public function test_ac_tv3_04_visibility_rules_public_follower_private_are_applied(): void
    {
        $this->markTestSkipped('AC-TV3-04: assert visibility matrix works for public/follower/private posts.');
    }
}
