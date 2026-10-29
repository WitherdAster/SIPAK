<?php

namespace Database\Seeders;

use App\Models\PerangkatDaerah;
use Illuminate\Database\Seeder;

class PerangkatDaerahSeeder extends Seeder
{
    /**
     * Daftar SKPD Kabupaten Barito Kuala (sesuai dokumen "LIST SKPD").
     */
    public function run(): void
    {
        // Samakan nama lama dengan daftar terbaru tanpa mengubah ID,
        // supaya aplikasi & akun yang sudah terhubung tidak putus.
        PerangkatDaerah::where('nama', 'Badan Perencanaan Pembangunan Daerah')->update([
            'nama' => 'Badan Perencanaan Pembangunan Penelitian dan Pengembangan',
            'singkatan' => 'Bapperida',
        ]);
        PerangkatDaerah::where('nama', 'Badan Kepegawaian dan Pengembangan SDM')->update([
            'nama' => 'Badan Kepegawaian, Pendidikan dan Pelatihan',
            'singkatan' => 'BKPP',
        ]);

        $data = [
            // Sekretariat & Inspektorat
            ['nama' => 'Sekretariat Daerah', 'singkatan' => 'Setda'],
            ['nama' => 'Sekretariat DPRD', 'singkatan' => '-'],
            ['nama' => 'Inspektorat Daerah', 'singkatan' => '-'],

            // Badan
            ['nama' => 'Badan Perencanaan Pembangunan Penelitian dan Pengembangan', 'singkatan' => 'Bapperida'],
            ['nama' => 'Badan Kepegawaian, Pendidikan dan Pelatihan', 'singkatan' => 'BKPP'],
            ['nama' => 'Badan Pengelolaan Keuangan dan Aset Daerah', 'singkatan' => 'BPKAD'],
            ['nama' => 'Satuan Polisi Pamong Praja', 'singkatan' => 'Satpol PP'],
            ['nama' => 'Badan Pengelolaan Pajak dan Retribusi Daerah', 'singkatan' => 'BPPRD'],
            ['nama' => 'Badan Kesatuan Bangsa dan Politik', 'singkatan' => 'Bakesbangpol'],
            ['nama' => 'Badan Penanggulangan Bencana Daerah', 'singkatan' => 'BPBD'],

            // Dinas & RSUD
            ['nama' => 'Dinas Kependudukan dan Pencatatan Sipil', 'singkatan' => 'Disdukcapil'],
            ['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes'],
            ['nama' => 'Dinas Pendidikan', 'singkatan' => 'Disdik'],
            ['nama' => 'Dinas Pemberdayaan Masyarakat dan Desa', 'singkatan' => 'DPMD'],
            ['nama' => 'RSUD H. Abdul Aziz Marabahan', 'singkatan' => 'RSUD'],
            ['nama' => 'Dinas Pekerjaan Umum dan Penataan Ruang', 'singkatan' => 'DPUPR'],
            ['nama' => 'Dinas Perumahan dan Kawasan Permukiman', 'singkatan' => 'Disperkim'],
            ['nama' => 'Dinas Perhubungan', 'singkatan' => 'Dishub'],
            ['nama' => 'Dinas Perpustakaan dan Kearsipan', 'singkatan' => 'Disperpusip'],
            ['nama' => 'Dinas Pertanian Tanaman Pangan dan Hortikultura', 'singkatan' => 'DistanTPH'],
            ['nama' => 'Dinas Perkebunan dan Peternakan', 'singkatan' => 'Disbunak'],
            ['nama' => 'Dinas Ketahanan Pangan dan Perikanan', 'singkatan' => 'DKPP'],
            ['nama' => 'Dinas Koperasi, Perindustrian dan Perdagangan', 'singkatan' => 'Diskoperindag'],
            ['nama' => 'Dinas Komunikasi dan Informatika', 'singkatan' => 'Diskominfo'],
            ['nama' => 'Dinas Tenaga Kerja dan Transmigrasi', 'singkatan' => 'Disnakertrans'],
            ['nama' => 'Dinas Sosial', 'singkatan' => 'Dinsos'],
            ['nama' => 'Dinas Kepemudaan, Olahraga, Budaya dan Pariwisata', 'singkatan' => 'Dispobudpar'],
            ['nama' => 'Dinas Pengendalian Penduduk, Keluarga Berencana, Pemberdayaan Perempuan dan Perlindungan Anak', 'singkatan' => 'DPPKB PPPA'],
            ['nama' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'singkatan' => 'DPMPTSP'],
            ['nama' => 'Dinas Lingkungan Hidup', 'singkatan' => 'DLH'],

            // Kecamatan
            ['nama' => 'Kecamatan Alalak', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Anjir Muara', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Anjir Pasar', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Bakumpai', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Barambai', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Belawang', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Cerbon', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Jejangkit', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Kuripan', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Mandastana', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Marabahan', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Mekarsari', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Rantau Badauh', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Tabukan', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Tabunganen', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Tamban', 'singkatan' => '-'],
            ['nama' => 'Kecamatan Wanaraya', 'singkatan' => '-'],
        ];

        foreach ($data as $row) {
            PerangkatDaerah::updateOrCreate(['nama' => $row['nama']], $row);
        }
    }
}
