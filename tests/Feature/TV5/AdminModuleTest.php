<?php

namespace Tests\Feature\TV5;

use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    public function test_ac_tv5_01_regular_user_cannot_access_admin_area(): void
    {
        $this->markTestSkipped('AC-TV5-01: protect /admin routes and deny access for regular users.');
    }

    public function test_ac_tv5_02_admin_can_access_dashboard(): void
    {
        $this->markTestSkipped('AC-TV5-02: allow admin role to access dashboard with HTTP 200.');
    }

    public function test_ac_tv5_03_admin_can_hide_or_disable_violating_post(): void
    {
        $this->markTestSkipped('AC-TV5-03: implement moderation endpoint to hide/disable violating post.');
    }

    public function test_ac_tv5_04_admin_can_lock_or_unlock_user_account(): void
    {
        $this->markTestSkipped('AC-TV5-04: implement user lock/unlock action with correct authorization checks.');
    }
}
