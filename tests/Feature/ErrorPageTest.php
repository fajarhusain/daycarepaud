<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test/error/{status}', function (int $status) {
            $headers = $status === 429 ? ['Retry-After' => '30'] : [];

            abort($status, $status === 403 ? 'Anda tidak memiliki akses ke data ini.' : '', $headers);
        });
    }

    public function test_common_http_errors_use_the_branded_page(): void
    {
        foreach ([400, 403, 404, 405, 422, 429, 500, 503] as $status) {
            $response = $this->get("/test/error/{$status}");

            $response->assertStatus($status)
                ->assertSee((string) $status)
                ->assertSee('Buka halaman utama');
        }
    }

    public function test_abort_message_is_not_exposed_to_the_parent_facing_page(): void
    {
        $this->get('/test/error/403')
            ->assertForbidden()
            ->assertSee('Halaman ini belum dapat dibuka')
            ->assertDontSee('Anda tidak memiliki akses ke data ini.');
    }

    public function test_server_error_details_are_not_displayed(): void
    {
        Route::get('/test/server-error-message', fn () => abort(500, 'Detail internal yang tidak boleh tampil.'));

        $this->get('/test/server-error-message')
            ->assertStatus(500)
            ->assertSee('Kami sedang memperbaiki kendala')
            ->assertDontSee('Detail internal yang tidak boleh tampil.');
    }

    public function test_rate_limit_headers_are_preserved(): void
    {
        $this->get('/test/error/429')
            ->assertTooManyRequests()
            ->assertHeader('Retry-After', '30');
    }

    public function test_json_requests_keep_a_json_error_response(): void
    {
        $this->getJson('/test/error/403')
            ->assertForbidden()
            ->assertJson(['message' => 'Anda tidak memiliki akses ke data ini.'])
            ->assertDontSee('Buka halaman utama');
    }

    public function test_expired_session_response_keeps_the_existing_login_redirect(): void
    {
        $this->get('/test/error/419')
            ->assertRedirect(route('login'))
            ->assertSessionHas('warning', 'Sesi Anda telah berakhir, silakan login kembali.');
    }
}
