<?php

namespace Tests\Feature;

use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelolaAkunTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'username' => 'admin', 'email' => 'admin@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'admin',
        ]);
    }

    public function test_admin_crud_akun_skpd(): void
    {
        $admin = $this->admin();
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes']);

        // Tambah (nama instansi diketik manual, nama akun otomatis)
        $this->actingAs($admin)->post(route('akun.store'), [
            'username' => 'dinkes',
            'email' => 'dinkes@uji.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'perangkat_daerah' => 'Dinas Kesehatan',
        ])->assertRedirect(route('akun.index'));

        $akun = User::where('username', 'dinkes')->first();
        $this->assertNotNull($akun);
        $this->assertSame('operator', $akun->role);
        $this->assertSame('Operator Dinas Kesehatan', $akun->name);
        $this->assertTrue(Hash::check('rahasia123', $akun->password));

        // Daftar tampil
        $this->actingAs($admin)->get(route('akun.index'))->assertOk()->assertSee('dinkes');

        // Form tidak meminta nama akun
        $this->actingAs($admin)->get(route('akun.create'))
            ->assertOk()
            ->assertDontSee('Nama Akun');

        // Edit tanpa ganti password
        $this->actingAs($admin)->put(route('akun.update', $akun), [
            'username' => 'dinkes',
            'email' => 'dinkes@uji.id',
            'perangkat_daerah' => 'Dinas Kesehatan',
        ])->assertRedirect(route('akun.index'));
        $this->assertSame('Operator Dinas Kesehatan', $akun->fresh()->name);
        $this->assertTrue(Hash::check('rahasia123', $akun->fresh()->password));

        // Hapus
        $this->actingAs($admin)->delete(route('akun.destroy', $akun))->assertRedirect(route('akun.index'));
        $this->assertNull(User::where('username', 'dinkes')->first());
    }

    public function test_satu_skpd_satu_akun(): void
    {
        $admin = $this->admin();
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Kesehatan', 'singkatan' => 'Dinkes']);
        User::create([
            'name' => 'Op', 'username' => 'dinkes', 'email' => 'dinkes@uji.id',
            'password' => Hash::make('x'), 'role' => 'operator', 'perangkat_daerah_id' => $skpd->id,
        ]);

        $this->actingAs($admin)->post(route('akun.store'), [
            'name' => 'Op Kedua',
            'username' => 'dinkes2',
            'email' => 'dinkes2@uji.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'perangkat_daerah' => 'dinas kesehatan',
        ])->assertSessionHasErrors('perangkat_daerah');

        $this->assertNull(User::where('username', 'dinkes2')->first());
    }

    public function test_instansi_baru_otomatis_dibuat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('akun.store'), [
            'username' => 'baru',
            'email' => 'baru@uji.id',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'perangkat_daerah' => 'Dinas Perhubungan',
        ])->assertRedirect(route('akun.index'));

        $skpd = PerangkatDaerah::where('nama', 'Dinas Perhubungan')->first();
        $this->assertNotNull($skpd);
        $this->assertSame($skpd->id, User::where('username', 'baru')->first()->perangkat_daerah_id);
    }

    public function test_bukan_admin_ditolak_dan_tidak_bisa_hapus_sendiri(): void
    {
        $admin = $this->admin();
        $skpd = PerangkatDaerah::create(['nama' => 'Disdik', 'singkatan' => 'Disdik']);
        $op = User::create([
            'name' => 'Op', 'username' => 'disdik', 'email' => 'disdik@uji.id',
            'password' => Hash::make('x'), 'role' => 'operator', 'perangkat_daerah_id' => $skpd->id,
        ]);

        $this->get(route('akun.index'))->assertRedirect(route('login'));
        $this->actingAs($op)->get(route('akun.index'))->assertForbidden();
        $this->actingAs($op)->get(route('akun.create'))->assertForbidden();
        $this->actingAs($op)->get(route('akun.password.edit', $admin))->assertForbidden();
        $this->actingAs($op)->put(route('akun.password.update', $admin), [
            'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertForbidden();

        $this->actingAs($admin)->delete(route('akun.destroy', $admin))->assertForbidden();
        $this->assertNotNull(User::find($admin->id));
    }

    public function test_admin_bisa_ubah_password_admin_lain_lalu_login(): void
    {
        $admin = $this->admin();
        $admin2 = User::create([
            'name' => 'Admin 2', 'username' => 'admin2', 'email' => 'admin2@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'admin',
        ]);

        $this->actingAs($admin)->get(route('akun.index'))
            ->assertOk()->assertSee('admin2')->assertSee('Akun Administrator');
        $this->actingAs($admin)->get(route('akun.password.edit', $admin2))
            ->assertOk()->assertSee('admin2');

        // Konfirmasi tidak cocok → gagal
        $this->actingAs($admin)->put(route('akun.password.update', $admin2), [
            'password' => 'sandibaru123', 'password_confirmation' => 'beda123456',
        ])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('rahasia', $admin2->fresh()->password));

        // Password admin lain diganti
        $this->actingAs($admin)->put(route('akun.password.update', $admin2), [
            'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('akun.index'));
        $this->assertTrue(Hash::check('sandibaru123', $admin2->fresh()->password));

        // Login dengan password baru berhasil
        $this->actingAs($admin)->post(route('logout'));
        $this->post(route('login.process'), [
            'username' => 'admin2',
            'password' => 'sandibaru123',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin2->fresh());
    }

    public function test_admin_bisa_ubah_password_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('akun.password.edit', $admin))
            ->assertOk()->assertSee('Akun Anda');

        $this->actingAs($admin)->put(route('akun.password.update', $admin), [
            'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('akun.index'));

        $this->assertTrue(Hash::check('sandibaru123', $admin->fresh()->password));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_password_hanya_untuk_akun_admin(): void
    {
        $admin = $this->admin();
        $skpd = PerangkatDaerah::create(['nama' => 'Disdik', 'singkatan' => 'Disdik']);
        $op = User::create([
            'name' => 'Op', 'username' => 'disdik', 'email' => 'disdik@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator', 'perangkat_daerah_id' => $skpd->id,
        ]);

        $this->actingAs($admin)->get(route('akun.password.edit', $op))->assertNotFound();
        $this->actingAs($admin)->put(route('akun.password.update', $op), [
            'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertNotFound();

        $this->assertTrue(Hash::check('rahasia', $op->fresh()->password));
    }

    public function test_halaman_akun_menampilkan_cakupan_skpd_dan_isi_otomatis(): void
    {
        $admin = $this->admin();
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Perhubungan', 'singkatan' => 'Dishub']);
        PerangkatDaerah::create(['nama' => 'Dinas Lingkungan Hidup', 'singkatan' => 'DLH']);

        $this->actingAs($admin)->get(route('akun.index'))
            ->assertOk()
            ->assertSee('SKPD terdaftar (master)')
            ->assertSee('SKPD Belum Punya Akun')
            ->assertSee('Dinas Perhubungan')
            ->assertSee('Dinas Lingkungan Hidup');

        // Tombol "Buat Akun" membawa ID SKPD → form terisi otomatis
        $this->actingAs($admin)->get(route('akun.create', ['skpd' => $skpd->id]))
            ->assertOk()
            ->assertSee('value="Dinas Perhubungan"', false);

        // Setelah SKPD punya akun, hilang dari daftar "belum punya akun"
        User::create([
            'name' => 'Operator Dishub', 'username' => 'dishub', 'email' => 'dishub@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator', 'perangkat_daerah_id' => $skpd->id,
        ]);

        $this->actingAs($admin)->get(route('akun.index'))
            ->assertOk()
            ->assertSee('Dinas Lingkungan Hidup')
            ->assertDontSee('skpd='.$skpd->id);
    }

    public function test_dashboard_menampilkan_cakupan_skpd_dan_akun(): void
    {
        $admin = $this->admin();
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Perhubungan', 'singkatan' => 'Dishub']);

        User::create([
            'name' => 'Operator Dishub', 'username' => 'dishub', 'email' => 'dishub@uji.id',
            'password' => Hash::make('rahasia'), 'role' => 'operator', 'perangkat_daerah_id' => $skpd->id,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('SKPD Terdaftar')
            ->assertSee('1 berakun')
            ->assertSee('0 belum');
    }

    public function test_setiap_pengguna_bisa_mengubah_password_sendiri(): void
    {
        $skpd = PerangkatDaerah::create(['nama' => 'Dinas Pendidikan', 'singkatan' => 'Disdik']);
        $operator = User::create([
            'name' => 'Operator Disdik', 'username' => 'disdik', 'email' => 'disdik@uji.id',
            'password' => Hash::make('lama12345'), 'role' => 'operator', 'perangkat_daerah_id' => $skpd->id,
        ]);
        $admin = $this->admin();

        // Operator bisa membuka halaman password sendiri
        $this->actingAs($operator)->get(route('profil.password.edit'))
            ->assertOk()
            ->assertSee('Password Lama')
            ->assertSee('Operator Instansi');

        // Password lama salah → ditolak
        $this->actingAs($operator)->put(route('profil.password.update'), [
            'password_lama' => 'salah',
            'password' => 'baru12345',
            'password_confirmation' => 'baru12345',
        ])->assertSessionHasErrors('password_lama');
        $this->assertTrue(Hash::check('lama12345', $operator->fresh()->password));

        // Password baru sama dengan lama → ditolak
        $this->actingAs($operator)->put(route('profil.password.update'), [
            'password_lama' => 'lama12345',
            'password' => 'lama12345',
            'password_confirmation' => 'lama12345',
        ])->assertSessionHasErrors('password');

        // Berhasil
        $this->actingAs($operator)->put(route('profil.password.update'), [
            'password_lama' => 'lama12345',
            'password' => 'baru12345',
            'password_confirmation' => 'baru12345',
        ])->assertRedirect(route('profil.password.edit'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('baru12345', $operator->fresh()->password));

        // Admin juga bisa mengubah password miliknya sendiri
        $this->actingAs($admin)->put(route('profil.password.update'), [
            'password_lama' => 'rahasia',
            'password' => 'adminbaru123',
            'password_confirmation' => 'adminbaru123',
        ])->assertRedirect(route('profil.password.edit'));

        $this->assertTrue(Hash::check('adminbaru123', $admin->fresh()->password));

        // Tamu tidak bisa mengakses halaman password
        $this->post(route('logout'));
        $this->get(route('profil.password.edit'))->assertRedirect(route('login'));
    }
}
