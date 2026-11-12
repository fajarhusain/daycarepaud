<?php

namespace Tests;

use App\Support\TenantContext;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Konteks tenant bersifat statis. Di produksi satu request satu proses,
        // tetapi di test prosesnya dipakai ulang antar test.
        TenantContext::reset();
    }

    protected function tearDown(): void
    {
        TenantContext::reset();

        parent::tearDown();
    }
}
