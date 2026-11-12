<?php

namespace Tests\Unit;

use App\Support\DashboardCompactMenu;
use PHPUnit\Framework\TestCase;

class DashboardCompactMenuTest extends TestCase
{
    public function test_empty_when_null_or_too_short(): void
    {
        $this->assertTrue(DashboardCompactMenu::isEmpty(null));
        $this->assertTrue(DashboardCompactMenu::isEmpty('  '));
        $this->assertTrue(DashboardCompactMenu::isEmpty('ab'));
        $this->assertSame([], DashboardCompactMenu::items('tes'));
    }

    public function test_splits_by_newline_and_comma(): void
    {
        $items = DashboardCompactMenu::items("Nasi goreng\nSayur bayam, Buah pepaya");
        $this->assertSame(['Nasi goreng', 'Sayur bayam', 'Buah pepaya'], $items);
    }

    public function test_single_valid_line(): void
    {
        $this->assertSame(['Nasi uduk lengkap'], DashboardCompactMenu::items('Nasi uduk lengkap'));
    }
}
