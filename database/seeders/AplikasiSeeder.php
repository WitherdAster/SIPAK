<?php

namespace Database\Seeders;

use App\Models\Aplikasi;
use App\Models\PerangkatDaerah;
use Illuminate\Database\Seeder;

class AplikasiSeeder extends Seeder
{
    public function run(): void
    {
        // [nama, singkatan, skpd_singkatan, platform, status, verifikasi, tahun]
        $data = [
            ['SIMPUS Batola', 'SIMPUS', 'Dinkes', 'web', 'aktif', 'terverifikasi', 2021],
            ['E-Puskesmas Anjir', 'E-Puskes', 'Dinkes', 'android', 'aktif', 'terverifikasi', 2022],
            ['SIKDA Generik', 'SIKDA', 'Dinkes', 'web', 'aktif', 'terverifikasi', 2020],
            ['Stunting Monitoring', 'SiMonting', 'Dinkes', 'web', 'aktif', 'diajukan', 2024],
            ['Farmasi Stock', 'SiFarm', 'Dinkes', 'web', 'pengembangan', 'diajukan', 2024],
            ['Ambulance Tracker', 'SiAmbul', 'Dinkes', 'android', 'aktif', 'terverifikasi', 2023],
            ['Dapodikdasmen Sync', 'Dapodik', 'Disdik', 'web', 'aktif', 'terverifikasi', 2020],
            ['E-Rapor Batola', 'E-Rapor', 'Disdik', 'web', 'aktif', 'terverifikasi', 2022],
            ['PPDB Online', 'PPDB', 'Disdik', 'web', 'aktif', 'diajukan', 2024],
            ['BOS Reporting', 'SiBOS', 'Disdik', 'web', 'pengembangan', 'dikembalikan', 2024],
            ['Identitas Kependudukan Digital', 'IKD', 'Disdukcapil', 'android', 'aktif', 'terverifikasi', 2023],
            ['Antrean Dukcapil', 'SiAntre', 'Disdukcapil', 'web', 'aktif', 'terverifikasi', 2022],
            ['Lahir Mati Terpadu', 'SiLMT', 'Disdukcapil', 'web', 'aktif', 'diajukan', 2024],
            ['SIPAK', 'SIPAK', 'Diskominfo', 'web', 'pengembangan', 'diajukan', 2025],
            ['Portal Batola', 'Portal', 'Diskominfo', 'web', 'aktif', 'terverifikasi', 2021],
            ['Command Center', 'CC', 'Diskominfo', 'web', 'aktif', 'terverifikasi', 2023],
            ['E-Jalan & Jembatan', 'SiJalan', 'DPUPR', 'web', 'aktif', 'terverifikasi', 2022],
            ['Irigasi Monitoring', 'SiIrigasi', 'DPUPR', 'web', 'diajukan', 'diajukan', 2024],
            ['E-Musrenbang', 'E-Musren', 'Bapperida', 'web', 'aktif', 'terverifikasi', 2021],
            ['SIPD Planning Sync', 'SIPD', 'Bapperida', 'web', 'aktif', 'diajukan', 2023],
            ['E-Kinerja ASN', 'E-Kin', 'BKPP', 'web', 'aktif', 'terverifikasi', 2022],
            ['DTKS Verifikasi', 'SiDTKS', 'Dinsos', 'web', 'aktif', 'diajukan', 2023],
        ];

        foreach ($data as [$nama, $singkatan, $skpd, $platform, $status, $verifikasi, $tahun]) {
            $skpdId = PerangkatDaerah::where('singkatan', $skpd)->value('id');

            if (! $skpdId) {
                continue;
            }

            Aplikasi::updateOrCreate(
                ['nama' => $nama],
                [
                    'perangkat_daerah_id' => $skpdId,
                    'singkatan' => $singkatan,
                    'deskripsi' => "Aplikasi {$nama} yang dikelola {$skpd} Kabupaten Barito Kuala.",
                    'platform' => $platform,
                    'status' => $status,
                    'verifikasi' => $verifikasi,
                    'diverifikasi_at' => $verifikasi === 'diajukan' ? null : now(),
                    'catatan_verifikasi' => $verifikasi === 'dikembalikan'
                        ? 'Dokumen SOP belum dilampirkan dan deskripsi aplikasi masih terlalu umum. Lengkapi SOP lalu perjelas fungsi utama aplikasi, kemudian kirim ulang.'
                        : null,
                    'tahun' => $tahun,
                ]
            );
        }
    }
}
