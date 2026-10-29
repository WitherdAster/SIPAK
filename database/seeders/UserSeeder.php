<?php

namespace Database\Seeders;

use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun awal untuk login SIPAK (1 instansi = 1 akun).
     */
    public function run(): void
    {
        $disdik = PerangkatDaerah::where('singkatan', 'Disdik')->value('id');

        $this->seedAkun('admin1', [
            'name' => 'Administrator 1',
            'email' => 'admin1@sipak.batolakab.go.id',
            'role' => 'admin',
            'perangkat_daerah_id' => null,
        ]);

        $this->seedAkun('admin2', [
            'name' => 'Administrator 2',
            'email' => 'admin2@sipak.batolakab.go.id',
            'role' => 'admin',
            'perangkat_daerah_id' => null,
        ]);

        $this->seedAkun('disdik', [
            'name' => 'Operator Dinas Pendidikan',
            'email' => 'disdik@sipak.batolakab.go.id',
            'role' => 'operator',
            'perangkat_daerah_id' => $disdik,
        ]);

        // Perbaiki akun lama (versi awal) yang belum terikat instansi
        User::where('username', 'operator')->update(['perangkat_daerah_id' => $disdik]);
    }

    /**
     * Buat akun bila belum ada. Password hanya diisi saat akun baru dibuat,
     * supaya password yang sudah diganti admin tidak ter-reset saat seeding ulang.
     */
    private function seedAkun(string $username, array $data): void
    {
        $user = User::firstOrNew(['username' => $username]);

        $user->fill($data);

        if (! $user->exists) {
            $user->password = Hash::make('sipak123');
        }

        $user->save();
    }
}
