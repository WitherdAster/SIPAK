<?php

namespace Tests\Feature;

use App\Models\Aplikasi;
use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function siapkan(): array
    {
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Pendidikan', 'singkatan' => 'Disdik']);

        $admin = User::create([
            'name' => 'Admin', 'username' => 'admin', 'email' => 'admin@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'admin',
        ]);
        $op = User::create([
            'name' => 'Op', 'username' => 'disdik', 'email' => 'disdik@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator',
            'perangkat_daerah_id' => $skpd->id,
        ]);

        Aplikasi::create([
            'perangkat_daerah_id' => $skpd->id, 'nama' => 'App Web Aktif',
            'platform' => 'web', 'status' => 'aktif', 'tahun' => 2023,
            'verifikasi' => 'terverifikasi', 'diverifikasi_at' => now(),
        ]);
        Aplikasi::create([
            'perangkat_daerah_id' => $skpd->id, 'nama' => 'App Android Mati',
            'platform' => 'android', 'status' => 'nonaktif', 'tahun' => 2024,
            'verifikasi' => 'diajukan',
        ]);

        return [$admin, $op];
    }

    public function test_unduh_pdf_semua_tanpa_filter(): void
    {
        [$admin] = $this->siapkan();

        $res = $this->actingAs($admin)->get(route('laporan.unduh'))->assertOk();
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('.pdf', $res->headers->get('Content-Disposition'));

        $isi = $res->getContent();
        $this->assertStringStartsWith('%PDF', $isi);
        $this->assertGreaterThan(1000, strlen($isi));
    }

    public function test_filter_status_tahun_platform(): void
    {
        [$admin] = $this->siapkan();

        // Jumlah pada halaman filter mencerminkan filter
        $this->actingAs($admin)->get(route('laporan.index', ['status' => 'aktif']))
            ->assertOk()->assertSee('Unduh PDF (1 data)', false);
        $this->actingAs($admin)->get(route('laporan.index', ['tahun' => 2024]))
            ->assertOk()->assertSee('Unduh PDF (1 data)', false);
        $this->actingAs($admin)->get(route('laporan.index', ['platform' => 'web']))
            ->assertOk()->assertSee('Unduh PDF (1 data)', false);
        $this->actingAs($admin)->get(route('laporan.index'))
            ->assertOk()->assertSee('Unduh PDF (2 data)', false);

        // Unduhan terfilter tetap menghasilkan PDF valid
        foreach ([['status' => 'aktif'], ['tahun' => 2024], ['platform' => 'web']] as $params) {
            $res = $this->actingAs($admin)->get(route('laporan.unduh', $params))->assertOk();
            $this->assertStringStartsWith('%PDF', $res->getContent());
        }
    }

    public function test_laporan_hanya_admin(): void
    {
        [, $op] = $this->siapkan();

        $this->get(route('laporan.index'))->assertRedirect(route('login'));
        $this->get(route('laporan.unduh'))->assertRedirect(route('login'));
        $this->actingAs($op)->get(route('laporan.index'))->assertForbidden();
        $this->actingAs($op)->get(route('laporan.unduh'))->assertForbidden();
    }

    public function test_operator_unduh_laporan_miliknya_saja(): void
    {
        // Tamu dilempar ke login (dicek sebelum actingAs menempel)
        $this->get(route('laporan.milikku'))->assertRedirect(route('login'));

        [$admin, $op] = $this->siapkan();

        Aplikasi::create([
            'perangkat_daerah_id' => PerangkatDaerah::create(['nama' => 'Dinas Lain', 'singkatan' => 'Lain'])->id,
            'nama' => 'App Orang Lain', 'platform' => 'web', 'status' => 'aktif',
            'verifikasi' => 'terverifikasi',
        ]);

        // Halaman milikku hanya hitung 2 miliknya (bukan 3 total)
        $this->actingAs($op)->get(route('laporan.milikku'))
            ->assertOk()->assertSee('Unduh PDF (2 data)', false);

        // Unduhan milikku valid PDF
        $res = $this->actingAs($op)->get(route('laporan.unduhMilikku'))->assertOk();
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $res->getContent());

        // Admin (tanpa instansi) tidak bisa pakai jalur milikku
        $this->actingAs($admin)->get(route('laporan.milikku'))->assertForbidden();
    }
}
