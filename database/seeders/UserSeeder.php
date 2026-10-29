<?php

namespace Database\Seeders;

use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Password awal untuk setiap akun operator SKPD.
     */
    private const PASSWORD_DEFAULT = 'sipak123';

    /**
     * Akun awal untuk login SIPAK (1 instansi = 1 akun).
     */
    public function run(): void
    {
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

        $this->pindahkanAkunOperatorLama();

        PerangkatDaerah::orderBy('id')->each(fn (PerangkatDaerah $skpd) => $this->seedAkunSkpd($skpd));
    }

    /**
     * Pastikan setiap SKPD memiliki satu akun operator. Password default
     * hanya dipakai saat akun dibuat, sehingga password yang sudah diubah
     * tidak ter-reset ketika seeder dijalankan ulang.
     */
    private function seedAkunSkpd(PerangkatDaerah $skpd): void
    {
        $sudahPunyaAkun = User::where('role', 'operator')
            ->where('perangkat_daerah_id', $skpd->id)
            ->exists();

        if ($sudahPunyaAkun) {
            return;
        }

        $dasar = Str::slug(
            $skpd->singkatan && $skpd->singkatan !== '-' ? $skpd->singkatan : $skpd->nama
        );

        $username = $this->usernameUnik($dasar ?: 'skpd-'.$skpd->id);

        User::create([
            'name' => 'Operator '.$skpd->nama,
            'username' => $username,
            'email' => $this->emailUnik($username),
            'password' => Hash::make(self::PASSWORD_DEFAULT),
            'role' => 'operator',
            'perangkat_daerah_id' => $skpd->id,
        ]);
    }

    /**
     * Akun lama "operator" (sebelum ada pengikatan instansi) dipindahkan ke
     * Disdik bila instansi tersebut belum memiliki akun.
     */
    private function pindahkanAkunOperatorLama(): void
    {
        $legacy = User::where('username', 'operator')
            ->whereNull('perangkat_daerah_id')
            ->first();

        $disdik = PerangkatDaerah::where('singkatan', 'Disdik')->first();

        if (! $legacy || ! $disdik) {
            return;
        }

        $disdikSudahPunyaAkun = User::where('role', 'operator')
            ->where('perangkat_daerah_id', $disdik->id)
            ->exists();

        if (! $disdikSudahPunyaAkun) {
            $legacy->update([
                'role' => 'operator',
                'perangkat_daerah_id' => $disdik->id,
            ]);
        }
    }

    private function usernameUnik(string $dasar): string
    {
        $username = $dasar;
        $urut = 2;

        while (User::where('username', $username)->exists()) {
            $username = $dasar.'-'.$urut;
            $urut++;
        }

        return $username;
    }

    private function emailUnik(string $username): string
    {
        $email = $username.'@sipak.batolakab.go.id';
        $urut = 2;

        while (User::where('email', $email)->exists()) {
            $email = $username.'-'.$urut.'@sipak.batolakab.go.id';
            $urut++;
        }

        return $email;
    }

    /**
     * Buat akun admin bila belum ada. Password hanya diisi saat akun baru
     * dibuat, supaya password yang sudah diganti tidak ter-reset.
     */
    private function seedAkun(string $username, array $data): void
    {
        $user = User::firstOrNew(['username' => $username]);

        $user->fill($data);

        if (! $user->exists) {
            $user->password = Hash::make(self::PASSWORD_DEFAULT);
        }

        $user->save();
    }
}
