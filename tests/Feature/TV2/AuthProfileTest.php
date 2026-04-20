<?php

namespace Tests\Feature\TV2;

use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    public function test_ac_tv2_01_guest_is_redirected_when_accessing_profile_requires_auth(): void
    {
        $this->markTestSkipped('AC-TV2-01: protect /profile with auth middleware, then assert guest is redirected to login.');
    }

    public function test_ac_tv2_02_authenticated_user_can_view_profile(): void
    {
        $this->markTestSkipped('AC-TV2-02: after auth integration, assert logged-in user gets HTTP 200 on /profile.');
    }

    public function test_ac_tv2_03_user_can_update_own_profile_display_name_and_bio(): void
    {
        $this->markTestSkipped('AC-TV2-03: create profile update endpoint and assert display_name/bio persist correctly.');
    }

    public function test_ac_tv2_04_user_cannot_update_other_user_profile(): void
    {
        $this->markTestSkipped('AC-TV2-04: ensure authorization prevents updating another user profile.');
    }
}
