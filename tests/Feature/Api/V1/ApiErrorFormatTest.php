<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Route;

class ApiErrorFormatTest extends ApiTestCase
{
    public function test_validation_error_uses_final_envelope_and_indonesian_messages(): void
    {
        $this->postJson('/api/v1/auth/sessions/password', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDASI_GAGAL')
            ->assertJsonPath('error.message', 'Data yang dikirim belum lengkap atau tidak valid.')
            ->assertJsonPath('error.fields.email.0', 'Email wajib diisi.')
            ->assertJsonPath('error.fields.password.0', 'Kata sandi wajib diisi.')
            ->assertJsonStructure(['error' => ['code', 'message', 'fields', 'request_id']]);
    }

    public function test_unknown_api_route_uses_final_envelope(): void
    {
        $this->getJson('/api/v1/tidak-ada')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN')
            ->assertJsonPath('error.message', 'Data yang Anda cari tidak ditemukan.');
    }

    public function test_method_not_allowed_uses_final_envelope(): void
    {
        $this->postJson('/api/v1/me')
            ->assertStatus(405)
            ->assertJsonPath('error.code', 'METODE_TIDAK_DIDUKUNG');
    }

    public function test_rate_limit_uses_final_envelope_and_retry_header(): void
    {
        $payload = [
            'email' => 'tidak-ada@example.com',
            'password' => 'salah',
        ];

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->postJson('/api/v1/auth/sessions/password', $payload)
                ->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/sessions/password', $payload)
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJsonPath('error.code', 'TERLALU_BANYAK_PERMINTAAN');
    }

    public function test_server_error_does_not_leak_exception_message(): void
    {
        Route::middleware('api.request-id')
            ->get('/api/v1/_test/server-error', function () {
                throw new \RuntimeException('Rahasia server yang tidak boleh bocor');
            });

        $response = $this->getJson('/api/v1/_test/server-error')
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'KESALAHAN_SERVER')
            ->assertJsonPath('error.message', 'Terjadi kendala di server. Silakan coba lagi beberapa saat lagi.');

        $this->assertStringNotContainsString('Rahasia server', $response->getContent());
    }

    public function test_request_id_is_generated_and_client_request_id_is_echoed(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertOk()
            ->assertHeader('X-Request-ID');

        $this->withHeader('X-Request-ID', 'client-request-123')
            ->getJson('/api/v1/ping')
            ->assertOk()
            ->assertHeader('X-Request-ID', 'client-request-123')
            ->assertJsonPath('data.ok', true);
    }
}
