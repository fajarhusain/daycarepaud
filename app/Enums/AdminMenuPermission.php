<?php

namespace App\Enums;

enum AdminMenuPermission: string
{
    case Siswa = 'menu.siswa';
    case Pendaftaran = 'menu.pendaftaran';
    case KelolakKelas = 'menu.kelola-kelas';
    case PresensiSiswa = 'menu.presensi-siswa';
    case KesehatanSiswa = 'menu.kesehatan-siswa';
    case Matrikulasi = 'menu.matrikulasi';
    case SkalaCapaian = 'menu.skala-capaian';
    case AgendaBelajar = 'menu.agenda-belajar';
    case KegiatanRutin = 'menu.kegiatan-rutin';
    case PencapaianSiswa = 'menu.pencapaian-siswa';
    case Monev = 'menu.monev';
    case DataPengajar = 'menu.data-pengajar';
    case PresensiGuru = 'menu.presensi-guru';
    case PengaturanPresensiGuru = 'menu.pengaturan-presensi-guru';
    case MonevGuru = 'menu.monev-guru';
    case MonevGuruKriteria = 'menu.monev-guru-kriteria';
    case Sarana = 'menu.sarana';
    case MenuMakanan = 'menu.menu-makanan';
    case AkunCoa = 'menu.akun-coa';
    case Cashflow = 'menu.cashflow';
    case TabunganInvestasi = 'menu.tabungan-investasi';
    case JurnalUmum = 'menu.jurnal-umum';
    case LaporanKeuangan = 'menu.laporan-keuangan';
    case SumberDana = 'menu.sumber-dana';
    case Rkas = 'menu.rkas';
    case LaporanRkas = 'menu.laporan-rkas';
    case BiayaHarian = 'menu.biaya-harian';
    case Diskon = 'menu.diskon';
    case RekapPembayaran = 'menu.rekap-pembayaran';
    case GajiGuru = 'menu.gaji-guru';
    case KritikSaran = 'menu.kritik-saran';
    case Pengumuman = 'menu.pengumuman';
    case ChatOrangtua = 'menu.chat-orangtua';
    case Role = 'menu.role';
    case Pengguna = 'menu.pengguna';
    case SettingAkuntansi = 'menu.setting-akuntansi';
    case PengaturanAi = 'menu.pengaturan-ai';
    case LogAktivitas = 'menu.log-aktivitas';
}
