<?php

namespace Tests\Feature;

use App\Models\Aplikasi;
use App\Models\PerangkatDaerah;
use App\Models\PerubahanAplikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlurPengajuanTest extends TestCase
{
    use RefreshDatabase;

    private function buatAkun(): array
    {
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Pendidikan', 'singkatan' => 'Disdik']);

        $skpdUser = User::create([
            'name' => 'Operator Disdik',
            'username' => 'disdik',
            'email' => 'disdik@uji.id',
            'password' => Hash::make('rahasia'),
            'role' => 'operator',
            'perangkat_daerah_id' => $skpd->id,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@uji.id',
            'password' => Hash::make('rahasia'),
            'role' => 'admin',
        ]);

        return [$skpdUser, $admin];
    }

    public function test_pengajuan_baru_berstatus_menunggu_dan_tidak_tayang(): void
    {
        [$skpdUser] = $this->buatAkun();

        $this->actingAs($skpdUser)->post(route('aplikasi.store'), [
            'nama' => 'E-Rapor Uji',
            'platform' => ['web'],
        ])->assertRedirect(route('aplikasi.milikku'));

        $app = Aplikasi::where('nama', 'E-Rapor Uji')->first();
        $this->assertNotNull($app);
        $this->assertSame('aktif', $app->status); // default otomatis aktif
        $this->assertSame('diajukan', $app->verifikasi);
        $this->assertSame('Menunggu', $app->labelVerifikasi());

        // Belum tayang di Semua Aplikasi, tapi terpantau di Aplikasiku
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))->assertDontSee('E-Rapor Uji');
        $this->actingAs($skpdUser)->get(route('aplikasi.milikku'))->assertSee('E-Rapor Uji')->assertSee('Menunggu');
    }

    public function test_admin_terima_maka_tayang(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id,
            'nama' => 'App Terima',
            'platform' => ['web'],
            'status' => 'aktif',
            'verifikasi' => 'diajukan',
        ]);

        $this->actingAs($admin)->post(route('verifikasi.terima', $app))->assertRedirect();

        $this->assertSame('terverifikasi', $app->fresh()->verifikasi);
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))->assertSee('App Terima');
    }

    public function test_admin_minta_perbaikan_wajib_catatan_dan_tidak_tayang(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id,
            'nama' => 'App Perbaikan',
            'platform' => ['web'],
            'status' => 'aktif',
            'verifikasi' => 'diajukan',
        ]);

        // Tanpa catatan → gagal validasi
        $this->actingAs($admin)->post(route('verifikasi.perbaikan', $app), [])
            ->assertSessionHasErrors('catatan_verifikasi');
        $this->assertSame('diajukan', $app->fresh()->verifikasi);

        // Dengan catatan → perlu perbaikan + catatan tampil di Aplikasiku, tidak tayang
        $this->actingAs($admin)->post(route('verifikasi.perbaikan', $app), [
            'catatan_verifikasi' => 'SOP belum dilampirkan.',
        ])->assertRedirect();

        $fresh = $app->fresh();
        $this->assertSame('dikembalikan', $fresh->verifikasi);
        $this->assertSame('Perlu Perbaikan', $fresh->labelVerifikasi());
        $this->assertTrue($fresh->butuhPerbaikan());

        $this->actingAs($skpdUser)->get(route('aplikasi.index'))
            ->assertOk()
            ->assertDontSee('<p class="font-bold">App Perbaikan', false);
        $this->actingAs($skpdUser)->get(route('aplikasi.milikku'))
            ->assertSee('App Perbaikan')->assertSee('Perlu Perbaikan')->assertSee('SOP belum dilampirkan.');
    }

    public function test_middleware_melindungi_halaman(): void
    {
        [$skpdUser] = $this->buatAkun();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('verifikasi.index'))->assertRedirect(route('login'));
        $this->actingAs($skpdUser)->get(route('verifikasi.index'))->assertForbidden();
    }

    public function test_logout_tuntas_dan_login_ulang_sebagai_skpd(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        // Login sebagai admin, lalu logout → sesi benar-benar kosong
        $this->actingAs($admin)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        // Login ulang sebagai SKPD → form pengajuan bisa dibuka
        $this->actingAs($skpdUser)->get(route('aplikasi.create'))->assertOk();
    }

    public function test_login_wajib_mengisi_captcha_dengan_benar(): void
    {
        [$skpdUser] = $this->buatAkun();

        $this->get(route('login'))->assertOk()->assertSee('name="captcha"', false);
        $this->get(route('captcha'))->assertOk()->assertHeader('Content-Type', 'image/png');

        // Kode salah ditolak
        $this->withSession(['captcha_code' => 'ABCDE'])->post(route('login.process'), [
            'username' => 'disdik',
            'password' => 'rahasia',
            'captcha' => 'XXXXX',
        ])->assertSessionHasErrors('captcha');
        $this->assertGuest();

        // Kode benar (huruf besar/kecil tidak dibedakan) diterima
        $this->withSession(['captcha_code' => 'ABCDE'])->post(route('login.process'), [
            'username' => 'disdik',
            'password' => 'rahasia',
            'captcha' => 'abcde',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($skpdUser);
    }

    public function test_detail_modal_hanya_untuk_yang_berhak(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();
        $lain = User::create([
            'name' => 'Dinkes', 'username' => 'dinkes', 'email' => 'dinkes@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator',
            'perangkat_daerah_id' => PerangkatDaerah::create(['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes'])->id,
        ]);

        $diterima = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);
        $menunggu = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Menunggu',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'diajukan',
        ]);

        // Yang diterima: instansi lain bisa lihat
        $this->actingAs($lain)->getJson(route('aplikasi.show', $diterima))
            ->assertOk()->assertJson(['nama' => 'App Tayang', 'label' => 'Diterima']);

        // Yang menunggu: instansi lain ditolak, pemilik dan admin boleh
        $this->actingAs($lain)->getJson(route('aplikasi.show', $menunggu))->assertForbidden();
        $this->actingAs($skpdUser)->getJson(route('aplikasi.show', $menunggu))->assertOk();
        $this->actingAs($admin)->getJson(route('aplikasi.show', $menunggu))->assertOk();
    }

    public function test_pengajuan_lengkap_dengan_dokumen(): void
    {
        [$skpdUser] = $this->buatAkun();
        Storage::fake('public');

        $this->actingAs($skpdUser)->post(route('aplikasi.store'), [
            'nama' => 'App Lengkap',
            'platform' => ['web'],
            'status' => 'aktif',
            'vendor' => 'PT Maju',
            'pic_nama' => 'Budi',
            'pic_kontak' => '081234',
            'jumlah_pengguna' => 150,
            'server_hosting' => 'VPS Diskominfo',
            'teknologi' => 'Laravel',
            'db_teknologi' => 'MySQL',
            'punya_api' => '1',
            'sistem_terintegrasi' => 'SIPD',
            'splp' => '1',
            'audit_keamanan' => '1',
            'backup_data' => '1',
            'dok_sop' => UploadedFile::fake()->create('sop.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('aplikasi.milikku'));

        $app = Aplikasi::where('nama', 'App Lengkap')->first();
        $this->assertNotNull($app);
        $this->assertSame('PT Maju', $app->vendor);
        $this->assertTrue($app->punya_api);
        $this->assertFalse($app->disaster_recovery);
        $this->assertCount(1, $app->dokumens);
        Storage::disk('public')->assertExists($app->dokumens->first()->path);

        $this->actingAs($skpdUser)->getJson(route('aplikasi.show', $app))
            ->assertOk()
            ->assertJsonPath('vendor', 'PT Maju')
            ->assertJsonPath('api', 'Ya')
            ->assertJsonCount(1, 'dokumens');

        // Unduh boleh oleh pemilik
        $this->actingAs($skpdUser)->get(route('dokumen.unduh', $app->dokumens->first()))
            ->assertOk()->assertHeader('content-disposition');

        // Pratinjau inline (tanpa paksa unduh) juga boleh oleh pemilik
        $lihat = $this->actingAs($skpdUser)->get(route('dokumen.lihat', $app->dokumens->first()));
        $lihat->assertOk();
        $this->assertStringContainsString('inline', $lihat->headers->get('content-disposition'));
    }

    public function test_admin_kelola_status_dan_hapus_dari_modal(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Kelola',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        // JSON detail memuat penanda admin untuk modal
        $this->actingAs($admin)->getJson(route('aplikasi.show', $app))
            ->assertOk()->assertJson(['is_admin' => true, 'status_value' => 'aktif']);
        $this->actingAs($skpdUser)->getJson(route('aplikasi.show', $app))
            ->assertOk()->assertJson(['is_admin' => false, 'is_owner' => true]);

        // Ubah status
        $this->actingAs($admin)->put(route('aplikasi.status', $app), ['status' => 'nonaktif'])
            ->assertRedirect(route('aplikasi.index'));
        $this->assertSame('nonaktif', $app->fresh()->status);

        // Operator boleh ubah miliknya, termasuk status pengembangan,
        // dan berlaku langsung tanpa membuat pengajuan perubahan.
        $this->actingAs($skpdUser)->put(route('aplikasi.status', $app), ['status' => 'aktif'])
            ->assertRedirect(route('aplikasi.milikku'));
        $this->assertSame('aktif', $app->fresh()->status);

        $this->actingAs($skpdUser)->put(route('aplikasi.status', $app), ['status' => 'pengembangan'])
            ->assertRedirect(route('aplikasi.milikku'));
        $this->assertSame('pengembangan', $app->fresh()->status);
        $this->assertSame('terverifikasi', $app->fresh()->verifikasi);
        $this->assertSame(0, PerubahanAplikasi::count());

        // Operator tidak boleh ubah milik instansi lain & tidak boleh hapus
        $lain = User::create([
            'name' => 'Lain', 'username' => 'lain', 'email' => 'lain@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator',
            'perangkat_daerah_id' => PerangkatDaerah::create(['nama' => 'Dinas Lain', 'singkatan' => 'Lain'])->id,
        ]);
        $this->actingAs($lain)->put(route('aplikasi.status', $app), ['status' => 'nonaktif'])
            ->assertForbidden();
        $this->actingAs($skpdUser)->delete(route('aplikasi.destroy', $app))->assertForbidden();

        // Hapus oleh admin
        $this->actingAs($admin)->delete(route('aplikasi.destroy', $app))
            ->assertRedirect(route('aplikasi.index'));
        $this->assertNull(Aplikasi::find($app->id));
    }

    public function test_status_terupdate_di_tampilan_list_dan_modal(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tampil',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($admin)->put(route('aplikasi.status', $app), ['status' => 'nonaktif'])
            ->assertRedirect();

        // List harus render badge status baru di samping badge verifikasi
        $this->actingAs($admin)->get(route('aplikasi.index'))
            ->assertOk()
            ->assertSee('>Nonaktif<', false)
            ->assertDontSee('>Aktif<', false);

        // JSON modal juga harus baru
        $this->actingAs($admin)->getJson(route('aplikasi.show', $app))
            ->assertOk()->assertJson(['status' => 'Nonaktif', 'status_value' => 'nonaktif']);
    }

    public function test_terima_dan_perbaikan_tercatat_otomatis_di_riwayat(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $diterima = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Riwayat Terima',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'diajukan',
        ]);
        $perbaikan = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Riwayat Perbaikan',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'diajukan',
        ]);

        $this->actingAs($admin)->post(route('verifikasi.terima', $diterima))->assertRedirect();
        $this->actingAs($admin)->post(route('verifikasi.perbaikan', $perbaikan), [
            'catatan_verifikasi' => 'Belum lengkap.',
        ])->assertRedirect();

        $this->assertNotNull($diterima->fresh()->diverifikasi_at);
        $this->assertNotNull($perbaikan->fresh()->diverifikasi_at);

        // Keduanya langsung tampil di riwayat dengan hasil masing-masing
        $res = $this->actingAs($admin)->get(route('verifikasi.index'))->assertOk();
        $res->assertSee('App Riwayat Terima')->assertSee('App Riwayat Perbaikan');
        $res->assertSee('Diterima')->assertSee('Perlu Perbaikan');
    }

    public function test_aplikasi_perlu_perbaikan_tidak_bisa_ubah_status(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Perbaikan',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'dikembalikan',
            'catatan_verifikasi' => 'Belum lengkap.',
        ]);

        $this->actingAs($skpdUser)->put(route('aplikasi.status', $app), ['status' => 'nonaktif'])
            ->assertForbidden();
        $this->actingAs($admin)->put(route('aplikasi.status', $app), ['status' => 'nonaktif'])
            ->assertForbidden();
        $this->assertSame('aktif', $app->fresh()->status);
    }

    public function test_perbaikan_hanya_bisa_diminta_saat_menunggu_verifikasi(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($admin)->post(route('verifikasi.perbaikan', $app), [
            'catatan_verifikasi' => 'Data tidak sesuai.',
        ])->assertForbidden();

        $this->assertSame('terverifikasi', $app->fresh()->verifikasi);
    }

    public function test_pemilik_bisa_perbaiki_lalu_kirim_ulang(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();
        $lain = User::create([
            'name' => 'Lain', 'username' => 'lain', 'email' => 'lain@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator',
            'perangkat_daerah_id' => PerangkatDaerah::create(['nama' => 'Dinas Lain', 'singkatan' => 'Lain'])->id,
        ]);

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Lama',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'dikembalikan',
            'catatan_verifikasi' => 'Lengkapi SOP.', 'diverifikasi_at' => now(),
        ]);

        // Admin juga boleh membuka form, instansi lain tidak
        $this->actingAs($lain)->get(route('aplikasi.edit', $app))->assertForbidden();
        $this->actingAs($admin)->get(route('aplikasi.edit', $app))->assertOk();
        $this->actingAs($skpdUser)->get(route('aplikasi.edit', $app))
            ->assertOk()->assertSee('Lengkapi SOP.');

        // Kirim ulang → kembali menunggu verifikasi, catatan dibersihkan
        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'App Baru',
            'platform' => ['android'],
            'deskripsi' => 'Sudah diperbaiki.',
        ])->assertRedirect(route('aplikasi.milikku'));

        $fresh = $app->fresh();
        $this->assertSame('App Baru', $fresh->nama);
        $this->assertSame(['android'], $fresh->platform);
        $this->assertSame('diajukan', $fresh->verifikasi);
        $this->assertNull($fresh->catatan_verifikasi);
        $this->assertNull($fresh->diverifikasi_at);
        $this->assertSame('aktif', $fresh->status);

        // Belum tayang sampai admin menerima
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))
            ->assertDontSee('<p class="font-bold">App Baru', false);
    }

    public function test_pemilik_edit_aplikasi_tayang_menjadi_pengajuan_perubahan_tanpa_mengubah_data_lama(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();
        $lain = User::create([
            'name' => 'Lain', 'username' => 'lain', 'email' => 'lain@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator',
            'perangkat_daerah_id' => PerangkatDaerah::create(['nama' => 'Dinas Lain', 'singkatan' => 'Lain'])->id,
        ]);

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        // Pemilik boleh membuka form; instansi lain tidak
        $this->actingAs($lain)->get(route('aplikasi.edit', $app))->assertForbidden();
        $this->actingAs($skpdUser)->get(route('aplikasi.edit', $app))->assertOk();

        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'App Tayang Revisi',
            'platform' => ['android'],
            'deskripsi' => 'Data baru.',
        ])->assertRedirect(route('aplikasi.milikku'));

        // Data tayang belum berubah
        $fresh = $app->fresh();
        $this->assertSame('App Tayang', $fresh->nama);
        $this->assertSame(['web'], $fresh->platform);
        $this->assertSame('terverifikasi', $fresh->verifikasi);

        // Tersimpan sebagai pengajuan perubahan
        $pending = PerubahanAplikasi::where('aplikasi_id', $app->id)->first();
        $this->assertNotNull($pending);
        $this->assertTrue($pending->menunggu());
        $this->assertSame('App Tayang Revisi', $pending->atribut()['nama']);

        // Tetap tayang & statusnya terlihat oleh pemilik
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))->assertSee('App Tayang');
        $this->actingAs($skpdUser)->get(route('aplikasi.milikku'))
            ->assertSee('App Tayang')
            ->assertSee('Perubahan Menunggu ACC');

        // Admin melihat pengajuan perubahan terpisah dari pengajuan baru
        $this->actingAs($admin)->get(route('verifikasi.index'))
            ->assertOk()
            ->assertSee('Pengajuan Aplikasi Baru')
            ->assertSee('Pengajuan Perubahan Data')
            ->assertSee('App Tayang');
    }

    public function test_pengajuan_perubahan_ganda_ditolak_dan_bisa_dibatalkan(): void
    {
        [$skpdUser] = $this->buatAkun();
        Storage::fake('public');

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'Revisi Satu', 'platform' => ['web'],
            'dok_sop' => UploadedFile::fake()->create('sop-baru.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('aplikasi.milikku'));

        $pending = PerubahanAplikasi::first();
        $path = $pending->dokumenBaru()['sop']['path'];
        Storage::disk('public')->assertExists($path);

        // Tidak boleh menumpuk pengajuan baru
        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'Revisi Dua', 'platform' => ['web'],
        ])->assertSessionHasErrors('perubahan');
        $this->assertSame(1, PerubahanAplikasi::count());

        // Halaman edit terkunci & menampilkan tombol batal
        $this->actingAs($skpdUser)->get(route('aplikasi.edit', $app))
            ->assertOk()
            ->assertSee('Pengajuan Perubahan Sedang Direview')
            ->assertSee('Batalkan Pengajuan');

        // Batal → pengajuan & berkas sementara hilang, data lama tetap
        $this->actingAs($skpdUser)->post(route('aplikasi.perubahan.batal', $app))->assertRedirect(route('aplikasi.milikku'));
        $this->assertSame(0, PerubahanAplikasi::count());
        Storage::disk('public')->assertMissing($path);
        $this->assertSame('App Tayang', $app->fresh()->nama);
    }

    public function test_admin_setujui_perubahan_menerapkan_data_dan_dokumen(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();
        Storage::fake('public');

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);
        $dokumenLama = $app->dokumens()->create([
            'jenis' => 'sop', 'nama_asli' => 'sop-lama.pdf', 'path' => 'dokumen/sop-lama.pdf',
        ]);
        Storage::disk('public')->put($dokumenLama->path, 'lama');

        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'App Baru',
            'platform' => ['android'],
            'status' => 'aktif',
            'dok_sop' => UploadedFile::fake()->create('sop-baru.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $pending = PerubahanAplikasi::first();
        $sopBaru = $pending->dokumenBaru()['sop']['path'];

        // Dokumen pengajuan bisa dipratinjau admin tanpa mengunduh
        $lihat = $this->actingAs($admin)->get(route('verifikasi.perubahan.dokumen.lihat', [$pending, 'sop']));
        $lihat->assertOk();
        $this->assertStringContainsString('inline', $lihat->headers->get('content-disposition'));
        $this->actingAs($skpdUser)->get(route('verifikasi.perubahan.dokumen.lihat', [$pending, 'sop']))->assertForbidden();

        // Halaman review menampilkan perbandingan
        $this->actingAs($admin)->get(route('verifikasi.perubahan.show', $pending))
            ->assertOk()
            ->assertSee('Perbandingan Data')
            ->assertSee('App Tayang')
            ->assertSee('App Baru')
            ->assertSee('sop-baru.pdf');

        $this->actingAs($admin)->post(route('verifikasi.perubahan.setujui', $pending), [
            'catatan_admin' => 'Data sudah sesuai.',
        ])->assertRedirect(route('verifikasi.index'));

        $fresh = $app->fresh();
        $this->assertSame('App Baru', $fresh->nama);
        $this->assertSame(['android'], $fresh->platform);
        $this->assertSame('terverifikasi', $fresh->verifikasi);

        // Dokumen lama diganti dokumen pengajuan
        $this->assertDatabaseMissing('dokumen_aplikasi', ['id' => $dokumenLama->id]);
        Storage::disk('public')->assertMissing($dokumenLama->path);
        $this->assertDatabaseHas('dokumen_aplikasi', ['aplikasi_id' => $app->id, 'jenis' => 'sop', 'nama_asli' => 'sop-baru.pdf']);
        Storage::disk('public')->assertExists($sopBaru);

        $this->assertSame('disetujui', $pending->fresh()->status);
        $this->assertNotNull($pending->fresh()->diputuskan_at);

        // Data baru tayang
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))->assertSee('App Baru');
    }

    public function test_admin_tolak_perubahan_wajib_catatan_dan_data_lama_tetap(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();
        Storage::fake('public');

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'App Salah', 'platform' => ['web'],
            'dok_sop' => UploadedFile::fake()->create('sop-baru.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $pending = PerubahanAplikasi::first();
        $path = $pending->dokumenBaru()['sop']['path'];

        // Tanpa catatan → gagal
        $this->actingAs($admin)->post(route('verifikasi.perubahan.tolak', $pending), [])
            ->assertSessionHasErrors('catatan_admin');
        $this->assertTrue($pending->fresh()->menunggu());

        $this->actingAs($admin)->post(route('verifikasi.perubahan.tolak', $pending), [
            'catatan_admin' => 'Nama aplikasi tidak sesuai.',
        ])->assertRedirect(route('verifikasi.index'));

        $this->assertSame('ditolak', $pending->fresh()->status);
        $this->assertSame('App Tayang', $app->fresh()->nama);
        $this->assertSame('terverifikasi', $app->fresh()->verifikasi);
        Storage::disk('public')->assertMissing($path);

        // Instansi melihat catatan penolakan, data lama tetap
        $this->actingAs($skpdUser)->get(route('aplikasi.milikku'))
            ->assertSee('Perubahan Ditolak')
            ->assertSee('Nama aplikasi tidak sesuai.');
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))->assertSee('App Tayang');
    }

    public function test_admin_edit_langsung_membatalkan_pengajuan_perubahan(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();
        Storage::fake('public');

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'Revisi Instansi', 'platform' => ['web'],
            'dok_sop' => UploadedFile::fake()->create('sop-baru.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $pending = PerubahanAplikasi::first();
        $path = $pending->dokumenBaru()['sop']['path'];

        $this->actingAs($admin)->put(route('aplikasi.update', $app), [
            'nama' => 'Edit Admin', 'platform' => ['web'],
        ])->assertRedirect(route('aplikasi.index'));

        $this->assertSame('Edit Admin', $app->fresh()->nama);
        $this->assertSame(0, PerubahanAplikasi::count());
        Storage::disk('public')->assertMissing($path);
        $this->assertSame('terverifikasi', $app->fresh()->verifikasi);
    }

    public function test_operator_lain_tidak_bisa_membatalkan_pengajuan_perubahan(): void
    {
        [$skpdUser] = $this->buatAkun();
        $lain = User::create([
            'name' => 'Lain', 'username' => 'lain', 'email' => 'lain@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator',
            'perangkat_daerah_id' => PerangkatDaerah::create(['nama' => 'Dinas Lain', 'singkatan' => 'Lain'])->id,
        ]);

        $app = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Tayang',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);

        $this->actingAs($skpdUser)->put(route('aplikasi.update', $app), [
            'nama' => 'Revisi', 'platform' => ['web'],
        ])->assertRedirect();

        $this->actingAs($lain)->post(route('aplikasi.perubahan.batal', $app))->assertForbidden();
        $this->assertSame(1, PerubahanAplikasi::count());
    }

    public function test_filter_semua_aplikasi_berdasarkan_skpd_tahun_dan_platform(): void
    {
        [$skpdUser] = $this->buatAkun();
        $dinkes = PerangkatDaerah::create(['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes']);

        $dasar = ['platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi', 'tahun' => 2024];

        Aplikasi::create([...$dasar, 'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Disdik Web', 'singkatan' => 'ADW']);
        Aplikasi::create([...$dasar, 'perangkat_daerah_id' => $dinkes->id, 'nama' => 'App Dinkes Android', 'tahun' => 2025, 'platform' => ['android']]);
        Aplikasi::create([...$dasar, 'perangkat_daerah_id' => $dinkes->id, 'nama' => 'App Dinkes Web', 'tahun' => 2025]);
        Aplikasi::create([...$dasar, 'perangkat_daerah_id' => $dinkes->id, 'nama' => 'App Dinkes Lama', 'tahun' => 2023]);

        // Filter SKPD
        $this->actingAs($skpdUser)->get(route('aplikasi.index', ['skpd' => $dinkes->id]))
            ->assertOk()
            ->assertSee('App Dinkes Web')
            ->assertDontSee('App Disdik Web');

        // Filter tahun
        $this->actingAs($skpdUser)->get(route('aplikasi.index', ['tahun' => 2024]))
            ->assertOk()
            ->assertSee('App Disdik Web')
            ->assertDontSee('App Dinkes Web');

        // Filter platform
        $this->actingAs($skpdUser)->get(route('aplikasi.index', ['platform' => 'android']))
            ->assertOk()
            ->assertSee('App Dinkes Android')
            ->assertDontSee('App Disdik Web');

        // Kombinasi filter
        $this->actingAs($skpdUser)->get(route('aplikasi.index', [
            'skpd' => $dinkes->id, 'tahun' => 2025, 'platform' => ['web'],
        ]))->assertOk()
            ->assertSee('App Dinkes Web')
            ->assertDontSee('App Dinkes Android')
            ->assertDontSee('App Dinkes Lama');

        // Pencarian nama/singkatan
        $this->actingAs($skpdUser)->get(route('aplikasi.index', ['q' => 'ADW']))
            ->assertOk()
            ->assertSee('App Disdik Web')
            ->assertDontSee('App Dinkes Web');
    }

    public function test_admin_bisa_edit_semua_aplikasi_tanpa_mengubah_verifikasi(): void
    {
        [$skpdUser, $admin] = $this->buatAkun();

        $tayang = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Admin Edit',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'terverifikasi',
        ]);
        $perbaikan = Aplikasi::create([
            'perangkat_daerah_id' => $skpdUser->perangkat_daerah_id, 'nama' => 'App Admin Perbaikan',
            'platform' => ['web'], 'status' => 'aktif', 'verifikasi' => 'dikembalikan',
            'catatan_verifikasi' => 'Lengkapi SOP.',
        ]);

        // Admin boleh membuka dan menyimpan perubahan aplikasi instansi mana pun
        $this->actingAs($admin)->get(route('aplikasi.edit', $tayang))->assertOk();
        $this->actingAs($admin)->put(route('aplikasi.update', $tayang), [
            'nama' => 'App Admin Edit Baru',
            'platform' => ['android'],
            'deskripsi' => 'Diperbarui admin.',
        ])->assertRedirect(route('aplikasi.index'));

        $fresh = $tayang->fresh();
        $this->assertSame('App Admin Edit Baru', $fresh->nama);
        $this->assertSame(['android'], $fresh->platform);
        $this->assertSame('terverifikasi', $fresh->verifikasi);

        // Tetap tayang & data baru terlihat
        $this->actingAs($skpdUser)->get(route('aplikasi.index'))
            ->assertSee('App Admin Edit Baru');

        // Status perlu perbaikan & catatan tidak ikut berubah
        $this->actingAs($admin)->put(route('aplikasi.update', $perbaikan), [
            'nama' => 'App Admin Perbaikan Baru',
            'platform' => ['web'],
        ])->assertRedirect(route('aplikasi.index'));

        $freshPerbaikan = $perbaikan->fresh();
        $this->assertSame('dikembalikan', $freshPerbaikan->verifikasi);
        $this->assertSame('Lengkapi SOP.', $freshPerbaikan->catatan_verifikasi);
    }
}
