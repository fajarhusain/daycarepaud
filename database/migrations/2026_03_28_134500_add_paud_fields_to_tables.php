<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('pendiri')->nullable()->after('phone');
            $table->string('organisasi')->nullable()->after('pendiri');
            $table->string('no_akta')->nullable()->after('organisasi');
            $table->string('no_pengesahan')->nullable()->after('no_akta');
        });

        Schema::table('sekolahs', function (Blueprint $table) {
            $table->string('nisn')->nullable()->after('phone');
            $table->string('location_coordinate')->nullable()->after('nisn');
            $table->string('photo')->nullable()->after('location_coordinate');
        });

        Schema::table('pengajars', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('name');
            $table->text('alamat')->nullable()->after('jabatan');
            $table->string('phone')->nullable()->after('alamat');
            $table->string('pendidikan')->nullable()->after('phone');
            $table->string('jenis_kelamin')->nullable()->after('pendidikan');
            $table->string('photo')->nullable()->after('jenis_kelamin');
        });

        Schema::table('anaks', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('name');
            $table->text('alamat')->nullable()->after('dob');
            $table->string('jenis_kelamin')->nullable()->after('alamat');
            $table->string('nik_bapak')->nullable()->after('jenis_kelamin');
            $table->string('nama_bapak')->nullable()->after('nik_bapak');
            $table->string('nik_ibu')->nullable()->after('nama_bapak');
            $table->string('nama_ibu')->nullable()->after('nik_ibu');
        });

        Schema::table('saranas', function (Blueprint $table) {
            $table->string('lokasi')->nullable()->after('quantity');
            $table->string('jenis')->nullable()->after('lokasi');
            $table->string('photo')->nullable()->after('jenis');
        });

        Schema::table('matrikulasis', function (Blueprint $table) {
            $table->string('aspek')->nullable()->after('indicator');
            $table->text('tujuan')->nullable()->after('aspek');
            $table->text('strategi')->nullable()->after('tujuan');
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->foreignId('matrikulasi_id')->nullable()->after('pengajar_id')->constrained('matrikulasis')->nullOnDelete();
        });

        Schema::table('pencapaians', function (Blueprint $table) {
            $table->foreignId('kegiatan_id')->nullable()->after('matrikulasi_id')->constrained('kegiatans')->cascadeOnDelete();
            $table->text('capaian')->nullable()->after('score');
            $table->text('indikator_keberhasilan')->nullable()->after('capaian');
            $table->string('photo')->nullable()->after('indikator_keberhasilan');
        });

        Schema::table('menu_makanans', function (Blueprint $table) {
            $table->string('photo_kegiatan')->nullable()->after('photo');
        });

        Schema::table('kritik_sarans', function (Blueprint $table) {
            $table->string('nik_bapak')->nullable()->after('message');
            $table->string('nama_bapak')->nullable()->after('nik_bapak');
            $table->string('nama_anak')->nullable()->after('nama_bapak');
            $table->text('umpan_balik')->nullable()->after('nama_anak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn(['pendiri', 'organisasi', 'no_akta', 'no_pengesahan']);
        });

        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn(['nisn', 'location_coordinate', 'photo']);
        });

        Schema::table('pengajars', function (Blueprint $table) {
            $table->dropColumn(['nik', 'alamat', 'phone', 'pendidikan', 'jenis_kelamin', 'photo']);
        });

        Schema::table('anaks', function (Blueprint $table) {
            $table->dropColumn(['nik', 'alamat', 'jenis_kelamin', 'nik_bapak', 'nama_bapak', 'nik_ibu', 'nama_ibu']);
        });

        Schema::table('saranas', function (Blueprint $table) {
            $table->dropColumn(['lokasi', 'jenis', 'photo']);
        });

        Schema::table('matrikulasis', function (Blueprint $table) {
            $table->dropColumn(['aspek', 'tujuan', 'strategi']);
        });

        Schema::table('kegiatans', function (Blueprint $table) {
            $table->dropForeign(['matrikulasi_id']);
            $table->dropColumn('matrikulasi_id');
        });

        Schema::table('pencapaians', function (Blueprint $table) {
            $table->dropForeign(['kegiatan_id']);
            $table->dropColumn(['kegiatan_id', 'capaian', 'indikator_keberhasilan', 'photo']);
        });

        Schema::table('menu_makanans', function (Blueprint $table) {
            $table->dropColumn('photo_kegiatan');
        });

        Schema::table('kritik_sarans', function (Blueprint $table) {
            $table->dropColumn(['nik_bapak', 'nama_bapak', 'nama_anak', 'umpan_balik']);
        });
    }
};
