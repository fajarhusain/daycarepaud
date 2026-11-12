<?php

namespace Tests\Feature\Api\V1;

use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    protected function makeLembaga(string $name = 'Yayasan API'): Lembaga
    {
        return Lembaga::create([
            'name' => $name,
            'status' => Lembaga::STATUS_ACTIVE,
        ]);
    }

    protected function makeSekolah(
        Lembaga $lembaga,
        string $name = 'PAUD API',
        string $status = Sekolah::STATUS_ACTIVE,
    ): Sekolah {
        return Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => $name,
            'address' => 'Jl. Pengujian No. 1',
            'phone' => '021-123456',
            'status' => $status,
        ]);
    }

    /**
     * @param  list<Sekolah>  $schools
     */
    protected function makeOrtu(array $schools, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'lembaga_id' => null,
            'sekolah_id' => null,
        ], $attributes));
        $user->assignRole('Orang Tua');

        foreach ($schools as $school) {
            Anak::withoutSekolahScope()->create([
                'user_id' => $user->id,
                'sekolah_id' => $school->id,
                'name' => 'Anak '.$school->name,
                'dob' => now()->subYears(4)->toDateString(),
                'status' => 'approved',
            ]);
        }

        return $user;
    }

    protected function makeKelas(Sekolah $school, string $name = 'Kelas API'): Kelas
    {
        return Kelas::create([
            'sekolah_id' => $school->id,
            'name' => $name,
        ]);
    }

    protected function makePengajar(Sekolah $school, string $name = 'Guru API'): Pengajar
    {
        $user = User::factory()->create([
            'name' => $name,
            'lembaga_id' => $school->lembaga_id,
            'sekolah_id' => $school->id,
        ]);

        return Pengajar::create([
            'user_id' => $user->id,
            'sekolah_id' => $school->id,
            'name' => $name,
        ]);
    }

    protected function makeAnakFor(User $parent, Sekolah $school, array $attributes = []): Anak
    {
        return Anak::withoutSekolahScope()->create(array_merge([
            'user_id' => $parent->id,
            'sekolah_id' => $school->id,
            'name' => 'Anak '.$parent->id,
            'dob' => now()->subYears(4)->toDateString(),
            'status' => 'approved',
        ], $attributes));
    }

    protected function makeMatrikulasi(Sekolah $school, string $aspek = 'Kognitif'): Matrikulasi
    {
        return Matrikulasi::create([
            'sekolah_id' => $school->id,
            'aspek' => $aspek,
            'indicator' => 'Indikator '.$aspek,
            'description' => 'Deskripsi '.$aspek,
        ]);
    }

    protected function makeAdmin(Sekolah $school): User
    {
        $user = User::factory()->create([
            'lembaga_id' => $school->lembaga_id,
            'sekolah_id' => $school->id,
        ]);
        $user->assignRole('Admin Sekolah');

        return $user;
    }

    /**
     * @return array{Authorization: string}
     */
    protected function authHeaders(User $user): array
    {
        $token = $user->createToken('test-device', ['mobile'])->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }
}
