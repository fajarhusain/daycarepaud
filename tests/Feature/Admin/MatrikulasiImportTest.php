<?php

namespace Tests\Feature\Admin;

use App\Models\Lembaga;
use App\Models\Matrikulasi;
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

class MatrikulasiImportTest extends TestCase
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

        $response = $this->actingAs($fixtures['admin'])->get(route('admin.matrikulasi.import.template'));

        $response->assertOk();
        $response->assertDownload('template-import-matrikulasi.xlsx');
    }

    public function test_admin_can_import_matrikulasi(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Kognitif',
                'Indikator Import Baru',
                'Deskripsi lengkap indikator import.',
                'Tujuan pembelajaran contoh.',
                'Strategi pembelajaran contoh.',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.matrikulasi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('matrikulasis', [
            'indicator' => 'Indikator Import Baru',
            'description' => 'Deskripsi lengkap indikator import.',
            'aspek' => 'Kognitif',
            'tujuan' => 'Tujuan pembelajaran contoh.',
            'strategi' => 'Strategi pembelajaran contoh.',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
    }

    public function test_import_reports_invalid_rows(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Kognitif',
                '',
                '',
                '',
                '',
            ],
            [
                'Motorik',
                'Indikator Valid',
                'Deskripsi valid.',
                '',
                '',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.matrikulasi.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_errors');

        $errors = session('import_errors');
        $this->assertArrayHasKey(2, $errors);

        $this->assertDatabaseHas('matrikulasis', ['indicator' => 'Indikator Valid']);
        $this->assertEquals(1, Matrikulasi::where('sekolah_id', $fixtures['sekolah']->id)->count());
    }

    public function test_import_scoped_to_admin_sekolah(): void
    {
        $fixtures = $this->createFixtures();
        $otherSekolah = Sekolah::skip(1)->first() ?? Sekolah::first();

        $file = $this->makeImportFile([
            [
                'Sosial',
                'Indikator Scoped',
                'Deskripsi scoped.',
                '',
                '',
            ],
        ]);

        $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import'), [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('matrikulasis', [
            'indicator' => 'Indikator Scoped',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);

        $this->assertDatabaseMissing('matrikulasis', [
            'indicator' => 'Indikator Scoped',
            'sekolah_id' => $otherSekolah->id === $fixtures['sekolah']->id ? 0 : $otherSekolah->id,
        ]);
    }

    public function test_test_import_returns_json_for_ajax_request(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Bahasa',
                'Indikator Tes Json',
                'Deskripsi tes json.',
                '',
                '',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import.test'), [
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

        $this->assertDatabaseMissing('matrikulasis', ['indicator' => 'Indikator Tes Json']);
    }

    public function test_admin_can_test_import_without_saving_data(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Seni',
                'Indikator Tes Saja',
                'Deskripsi tes saja.',
                'Tujuan tes.',
                'Strategi tes.',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import.test'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.matrikulasi.index'));
        $response->assertSessionHas('import_test');

        $test = session('import_test');
        $this->assertTrue($test['can_import']);
        $this->assertEquals(1, $test['valid_count']);
        $this->assertEquals(0, $test['invalid_count']);

        $this->assertDatabaseMissing('matrikulasis', ['indicator' => 'Indikator Tes Saja']);
    }

    public function test_import_accepts_legacy_export_header_for_aspek(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile(
            [
                [
                    'Kognitif',
                    'Indikator Legacy Header',
                    'Deskripsi legacy header.',
                    '',
                    '',
                ],
            ],
            ['Aspek / Bidang', 'Indikator', 'Deskripsi', 'Tujuan', 'Strategi'],
        );

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.matrikulasi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('matrikulasis', [
            'indicator' => 'Indikator Legacy Header',
            'aspek' => 'Kognitif',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
    }

    public function test_import_treats_dash_as_empty_optional_fields(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                '-',
                'Indikator Tanpa Aspek',
                'Deskripsi valid.',
                '-',
                '-',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.matrikulasi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('matrikulasis', [
            'indicator' => 'Indikator Tanpa Aspek',
            'aspek' => null,
            'tujuan' => null,
            'strategi' => null,
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
    }

    public function test_test_import_reports_errors_without_saving(): void
    {
        $fixtures = $this->createFixtures();

        $file = $this->makeImportFile([
            [
                'Kognitif',
                '',
                'Deskripsi tanpa indikator.',
                '',
                '',
            ],
        ]);

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.matrikulasi.import.test'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.matrikulasi.index'));
        $response->assertSessionHas('import_test');

        $test = session('import_test');
        $this->assertFalse($test['can_import']);
        $this->assertEquals(0, $test['valid_count']);
        $this->assertEquals(1, $test['invalid_count']);

        $this->assertDatabaseCount('matrikulasis', 0);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @param  list<string>  $headers
     */
    protected function makeImportFile(array $rows, array $headers = ['aspek', 'indikator', 'deskripsi', 'tujuan', 'strategi']): UploadedFile
    {

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
     * @return array{sekolah: Sekolah, admin: User}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-import-matrikulasi@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        return compact('sekolah', 'admin');
    }
}
