<?php

namespace Tests\Unit;

use App\Support\DashboardIcons;
use PHPUnit\Framework\TestCase;

class DashboardIconsTest extends TestCase
{
    public function test_known_icon_returns_path(): void
    {
        $this->assertNotNull(DashboardIcons::path('users'));
    }

    public function test_unknown_icon_returns_null(): void
    {
        $this->assertNull(DashboardIcons::path('not-an-icon'));
    }
}
