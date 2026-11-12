<?php

namespace Tests\Feature\Admin;

use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AnakImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_download_import_template(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])->get(route('admin.anak.import.template'));

        $response->assertOk();
        $response->assertDownload('template-import-siswa.xlsx');
    }

    public function test_admin_can_import_anak_with_new_orang_tua(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Anak Import Baru',
                '',
                'Kelas Import',
                '',
                'Perempuan',
                '2021-03-10',
                '',
                '',
                '',
                '',
                '',
                'Wali Baru',
                'wali-baru-import@test.com',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'wali-baru-import@test.com',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
        $this->assertDatabaseHas('anaks', [
            'name' => 'Anak Import Baru',
            'status' => 'approved',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
    }

    public function test_admin_can_import_anak_linked_to_existing_orang_tua(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Anak Import Kedua',
                '',
                '',
                '',
                'Laki-laki',
                '2019-01-01',
                '',
                '',
                '',
                '',
                '',
                'Siti Ortu',
                $fixtures['ortu']->email,
            ],
        ]);

        $beforeCount = User::where('email', $fixtures['ortu']->email)->count();

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('success');

        $this->assertEquals($beforeCount, User::where('email', $fixtures['ortu']->email)->count());
        $this->assertEquals(2, Anak::where('user_id', $fixtures['ortu']->id)->count());
        $this->assertDatabaseHas('anaks', [
            'user_id' => $fixtures['ortu']->id,
            'name' => 'Anak Import Kedua',
            'status' => 'approved',
        ]);
    }

    public function test_import_reports_invalid_rows(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                '',
                '',
                'Kelas Tidak Ada',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Wali',
                'wali-invalid@test.com',
            ],
            [
                'Anak Valid',
                '',
                'Kelas Import',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Wali Valid',
                'wali-valid-import@test.com',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_errors');

        $errors = session('import_errors');
        $this->assertArrayHasKey(2, $errors);

        $this->assertDatabaseHas('anaks', ['name' => 'Anak Valid']);
        $this->assertDatabaseMissing('anaks', ['name' => '']);
    }

    public function test_import_rejects_unknown_kelas(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Anak Kelas Salah',
                '',
                'Kelas Tidak Ada',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Wali',
                'wali-kelas-salah@test.com',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('import_errors');

        $this->assertDatabaseMissing('anaks', ['name' => 'Anak Kelas Salah']);
    }

    public function test_test_import_returns_json_for_ajax_request(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Anak Tes Json',
                '',
                'Kelas Import',
                '',
                'Perempuan',
                '2021-03-10',
                '',
                '',
                '',
                '',
                '',
                'Wali Json',
                'wali-json-import@test.com',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import.test'), [
            'file' => $file,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid_count' => 1,
            'invalid_count' => 0,
            'can_import' => true,
        ]);
        $response->assertJsonStructure([
            'valid_count',
            'invalid_count',
            'valid_rows',
            'invalid_rows',
            'can_import',
            'message',
        ]);

        $this->assertDatabaseMissing('anaks', ['name' => 'Anak Tes Json']);
    }

    public function test_admin_can_test_import_without_saving_data(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Anak Tes Saja',
                '',
                'Kelas Import',
                '',
                'Perempuan',
                '2021-03-10',
                '',
                '',
                '',
                '',
                '',
                'Wali Tes',
                'wali-tes-import@test.com',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import.test'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('import_test');

        $test = session('import_test');
        $this->assertTrue($test['can_import']);
        $this->assertEquals(1, $test['valid_count']);
        $this->assertEquals(0, $test['invalid_count']);

        $this->assertDatabaseMissing('users', ['email' => 'wali-tes-import@test.com']);
        $this->assertDatabaseMissing('anaks', ['name' => 'Anak Tes Saja']);
    }

    public function test_test_import_reports_errors_without_saving(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Wali',
                'wali-tes-error@test.com',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.import.test'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('import_test');

        $test = session('import_test');
        $this->assertFalse($test['can_import']);
        $this->assertEquals(0, $test['valid_count']);
        $this->assertEquals(1, $test['invalid_count']);

        $this->assertDatabaseMissing('users', ['email' => 'wali-tes-error@test.com']);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    protected function makeImportFile(array $rows): UploadedFile
    {
        $headers = [
            'nama_lengkap_anak',
            'nama_panggilan',
            'kelas',
            'nik_anak',
            'jenis_kelamin',
            'tanggal_lahir',
            'alamat',
            'nik_bapak',
            'nama_bapak',
            'nik_ibu',
            'nama_ibu',
            'nama_wali',
            'email_wali',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        $rowIndex = 2;
        foreach ($rows as $row) {
            $sheet->fromArray($row, null, 'A'.$rowIndex);
            $rowIndex++;
        }

        $path = storage_path('framework/testing/import-'.uniqid().'.xlsx');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'import-test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * @return array{sekolah: Sekolah, admin: User, ortu: User, kelas: Kelas}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-import-anak@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Import',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Import',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-import-anak@test.com',
            'name' => 'Siti Ortu',
            'sekolah_id' => $sekolah->id,
        ]);
        $ortu->assignRole('Orang Tua');

        Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Anak Pertama',
            'status' => 'approved',
        ]);

        return compact('sekolah', 'admin', 'ortu', 'kelas');
    }
}
