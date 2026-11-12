<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class DateLocaleTest extends TestCase
{
    public function test_carbon_uses_indonesian_day_and_month_names(): void
    {
        $formatted = Carbon::parse('2026-10-29')->translatedFormat('l, d F Y');

        $this->assertStringContainsString('Kamis', $formatted);
        $this->assertStringContainsString('Oktober', $formatted);
        $this->assertStringNotContainsString('Thursday', $formatted);
    }
}
