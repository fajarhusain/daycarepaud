<?php

namespace Tests\Feature\Api\V1;

use App\Models\Anak;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AuthSessionTest extends ApiTestCase
{
    public function test_password_login_returns_envelope_and_schools(): void
    {
        $lembaga = $this->makeLembaga();
        $schoolA = $this->makeSekolah($lembaga, 'PAUD A');
        $schoolB = $this->makeSekolah($lembaga, 'PAUD B');
        $ortu = $this->makeOrtu([$schoolA, $schoolB]);

        $response = $this->postJson('/api/v1/auth/sessions/password', [
            'email' => strtoupper($ortu->email),
            'password' => 'password',
            'device_name' => 'Samsung A54',
            'platform' => 'android',
            'app_version' => '1.0.0',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.user.email', $ortu->email)
            ->assertJsonPath('data.user.roles.0', 'Orang Tua')
            ->assertJsonPath('data.user.mobile_roles.0', 'orang-tua')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('message', 'Berhasil masuk.')
            ->assertJsonCount(2, 'data.sekolah')
            ->assertJsonPath('data.sekolah.0.jumlah_anak', 1)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'roles', 'mobile_roles'],
                    'sekolah',
                ],
                'message',
            ]);

        $token = $ortu->tokens()->first();
        $this->assertNotNull($token);
        $this->assertContains('mobile', $token->abilities);
        $this->assertContains('role:orang-tua', $token->abilities);
    }

    public function test_password_login_rejects_wrong_password(): void
    {
        $ortu = $this->makeOrtu([$this->makeSekolah($this->makeLembaga())]);

        $this->postJson('/api/v1/auth/sessions/password', [
            'email' => $ortu->email,
            'password' => 'kata-sandi-salah',
        ])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'KREDENSIAL_SALAH')
            ->assertJsonPath('error.message', 'Email atau kata sandi tidak sesuai.')
            ->assertJsonStructure(['error' => ['code', 'message', 'request_id']]);
    }

    public function test_password_login_rejects_user_without_mobile_role(): void
    {
        $user = User::factory()->create([
            'lembaga_id' => $this->makeLembaga()->id,
            'sekolah_id' => $this->makeSekolah($this->makeLembaga(), 'PAUD Lain')->id,
        ]);

        $this->postJson('/api/v1/auth/sessions/password', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'ROLE_TIDAK_SESUAI');
    }

    public function test_password_login_rejects_orang_tua_without_approved_child(): void
    {
        $user = User::factory()->create([
            'lembaga_id' => null,
            'sekolah_id' => null,
        ]);
        $user->assignRole('Orang Tua');

        $this->postJson('/api/v1/auth/sessions/password', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'AKUN_BELUM_AKTIF');
    }

    public function test_google_login_accepts_verified_existing_user(): void
    {
        config(['api.google.client_ids' => ['test-client']]);
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'test-client',
                'iss' => 'https://accounts.google.com',
                'exp' => time() + 3600,
                'email' => 'ortu-google@example.com',
                'email_verified' => 'true',
                'sub' => 'google-subject',
                'name' => 'Orang Tua Google',
            ]),
        ]);

        $lembaga = $this->makeLembaga();
        $sekolah = $this->makeSekolah($lembaga, 'PAUD Google');
        $ortu = $this->makeOrtu([$sekolah], ['email' => 'ortu-google@example.com']);

        $this->postJson('/api/v1/auth/sessions/google', [
            'id_token' => 'valid-google-token',
            'device_name' => 'Pixel 8',
            'platform' => 'android',
            'app_version' => '1.0.0',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email', $ortu->email)
            ->assertJsonPath('message', 'Berhasil masuk dengan Google.')
            ->assertJsonCount(1, 'data.sekolah');
    }

    public function test_google_login_does_not_create_unknown_user(): void
    {
        config(['api.google.client_ids' => ['test-client']]);
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'aud' => 'test-client',
                'iss' => 'accounts.google.com',
                'exp' => time() + 3600,
                'email' => 'tidak-dikenal@example.com',
                'email_verified' => true,
                'sub' => 'google-subject-unknown',
            ]),
        ]);

        $this->postJson('/api/v1/auth/sessions/google', [
            'id_token' => 'valid-google-token',
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'AKUN_TIDAK_DAPAT_DIGUNAKAN');

        $this->assertDatabaseMissing('users', [
            'email' => 'tidak-dikenal@example.com',
        ]);
    }

    public function test_logout_revokes_current_token(): void
    {
        $ortu = $this->makeOrtu([$this->makeSekolah($this->makeLembaga())]);
        $headers = $this->authHeaders($ortu);

        $this->withHeaders($headers)
            ->deleteJson('/api/v1/auth/sessions/current')
            ->assertOk()
            ->assertJsonPath('message', 'Berhasil keluar.')
            ->assertJsonPath('data', null);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withHeaders($headers)
            ->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'SESI_BERAKHIR');
    }

    public function test_me_returns_profile_schools_and_parent_data(): void
    {
        $lembaga = $this->makeLembaga();
        $sekolah = $this->makeSekolah($lembaga, 'PAUD Profil');
        $ortu = $this->makeOrtu([$sekolah]);

        Anak::withoutSekolahScope()
            ->where('user_id', $ortu->id)
            ->update(['parent_name' => 'Bapak Adi']);

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $ortu->id)
            ->assertJsonPath('data.email', $ortu->email)
            ->assertJsonPath('data.mobile_roles.0', 'orang-tua')
            ->assertJsonPath('data.default_sekolah_id', $sekolah->id)
            ->assertJsonPath('data.sekolah.0.id', $sekolah->id)
            ->assertJsonPath('data.orang_tua.parent_name', 'Bapak Adi');
    }

    public function test_orang_tua_profile_update_applies_to_all_children(): void
    {
        $lembaga = $this->makeLembaga();
        $schoolA = $this->makeSekolah($lembaga, 'PAUD A');
        $schoolB = $this->makeSekolah($lembaga, 'PAUD B');
        $ortu = $this->makeOrtu([$schoolA, $schoolB]);

        $this->withHeaders($this->authHeaders($ortu))
            ->patchJson('/api/v1/me/orang-tua', [
                'parent_name' => 'Bapak Adi',
                'nama_bapak' => 'Adi',
                'nik_bapak' => '3201000000000001',
                'nama_ibu' => 'Siti',
                'nik_ibu' => '3201000000000002',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Data orang tua berhasil diperbarui.')
            ->assertJsonPath('data.parent_name', 'Bapak Adi')
            ->assertJsonPath('data.nama_bapak', 'Adi');

        $this->assertSame(
            2,
            Anak::withoutSekolahScope()
                ->where('user_id', $ortu->id)
                ->where('parent_name', 'Bapak Adi')
                ->where('nama_ibu', 'Siti')
                ->count(),
        );
    }

    public function test_me_sekolah_returns_accessible_schools(): void
    {
        $lembaga = $this->makeLembaga();
        $schoolA = $this->makeSekolah($lembaga, 'PAUD A');
        $schoolB = $this->makeSekolah($lembaga, 'PAUD B');
        $ortu = $this->makeOrtu([$schoolA, $schoolB]);

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/me/sekolah')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $schoolA->id])
            ->assertJsonFragment(['id' => $schoolB->id]);
    }

    public function test_registration_school_list_only_contains_active_schools(): void
    {
        $lembaga = $this->makeLembaga();
        $active = $this->makeSekolah($lembaga, 'PAUD Aktif');
        $suspended = $this->makeSekolah($lembaga, 'PAUD Suspend', Sekolah::STATUS_SUSPENDED);
        $ortu = $this->makeOrtu([$active]);

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/sekolah?akses=pendaftaran')
            ->assertOk()
            ->assertJsonFragment(['id' => $active->id])
            ->assertJsonMissing(['id' => $suspended->id]);
    }
}
