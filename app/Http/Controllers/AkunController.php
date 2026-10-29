<?php

namespace App\Http\Controllers;

use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AkunController extends Controller
{
    /**
     * Daftar akun operator instansi (1 SKPD = 1 akun).
     */
    public function index(Request $request): View
    {
        $akun = User::with(['perangkatDaerah', 'perangkatDaerah.aplikasi'])
            ->where('role', 'operator')
            ->when($request->q, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('username', 'like', "%{$s}%")
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $admin = User::where('role', 'admin')->orderBy('username')->get();

        $skpdBelumAkun = PerangkatDaerah::whereDoesntHave('users', fn ($q) => $q->where('role', 'operator'))
            ->orderBy('nama')
            ->get();
        $totalSkpd = PerangkatDaerah::count();
        $skpdBerakun = $totalSkpd - $skpdBelumAkun->count();

        return view('akun.index', compact('akun', 'admin', 'skpdBelumAkun', 'totalSkpd', 'skpdBerakun'));
    }

    /**
     * Form ubah password untuk akun yang sedang login (admin maupun operator).
     */
    public function editPasswordSendiri(): View
    {
        return view('akun.password-sendiri', ['akun' => auth()->user()]);
    }

    /**
     * Simpan password baru milik sendiri. Wajib verifikasi password lama.
     */
    public function updatePasswordSendiri(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($validated['password_lama'], $user->password)) {
            throw ValidationException::withMessages([
                'password_lama' => 'Password lama tidak sesuai.',
            ]);
        }

        if (Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password baru harus berbeda dari password lama.',
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profil.password.edit')
            ->with('success', 'Password Anda berhasil diperbarui. Gunakan password baru saat login berikutnya.');
    }

    /**
     * Form ubah password akun administrator (diri sendiri atau admin lain).
     */
    public function editPassword(User $akun): View
    {
        abort_if($akun->role !== 'admin', 404);

        return view('akun.password', compact('akun'));
    }

    /**
     * Simpan password baru akun administrator.
     */
    public function updatePassword(Request $request, User $akun): RedirectResponse
    {
        abort_if($akun->role !== 'admin', 404);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $akun->password = Hash::make($validated['password']);
        $akun->save();

        return redirect()->route('akun.index')
            ->with('success', "Password akun {$akun->username} berhasil diperbarui.");
    }

    /**
     * Form akun operator baru. Bisa terisi otomatis dari tautan
     * "Buat Akun" pada daftar SKPD yang belum punya akun.
     */
    public function create(Request $request): View
    {
        $skpdTerpilih = $request->filled('skpd')
            ? PerangkatDaerah::whereDoesntHave('users', fn ($q) => $q->where('role', 'operator'))
                ->find($request->integer('skpd'))
            : null;

        return view('akun.create', [
            'skpdTersedia' => $this->skpdTersedia(),
            'skpdTerpilih' => $skpdTerpilih,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'perangkat_daerah' => ['required', 'string', 'max:255'],
        ]);

        $skpd = $this->resolveSkpd($validated['perangkat_daerah']);

        if ($this->skpdSudahPunyaAkun($skpd->id)) {
            throw ValidationException::withMessages(['perangkat_daerah' => 'Instansi ini sudah memiliki akun.']);
        }

        User::create([
            'name' => 'Operator '.$skpd->nama,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'operator',
            'perangkat_daerah_id' => $skpd->id,
        ]);

        return redirect()->route('akun.index')->with('success', 'Akun instansi berhasil dibuat.');
    }

    public function edit(User $akun): View
    {
        abort_if($akun->role !== 'operator', 404);

        return view('akun.edit', [
            'akun' => $akun->load('perangkatDaerah'),
            'skpdTersedia' => $this->skpdTersedia(),
        ]);
    }

    public function update(Request $request, User $akun): RedirectResponse
    {
        abort_if($akun->role !== 'operator', 404);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($akun->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($akun->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'perangkat_daerah' => ['required', 'string', 'max:255'],
        ]);

        $skpd = $this->resolveSkpd($validated['perangkat_daerah']);

        if ($this->skpdSudahPunyaAkun($skpd->id, $akun->id)) {
            throw ValidationException::withMessages(['perangkat_daerah' => 'Instansi ini sudah memiliki akun.']);
        }

        $akun->fill([
            'name' => 'Operator '.$skpd->nama,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'perangkat_daerah_id' => $skpd->id,
        ]);

        if (! empty($validated['password'])) {
            $akun->password = Hash::make($validated['password']);
        }

        $akun->save();

        return redirect()->route('akun.index')->with('success', 'Akun instansi berhasil diperbarui.');
    }

    public function destroy(User $akun): RedirectResponse
    {
        abort_if($akun->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($akun->role !== 'operator', 404);

        $akun->delete();

        return redirect()->route('akun.index')->with('success', 'Akun instansi berhasil dihapus.');
    }

    /**
     * SKPD yang belum punya akun (saran untuk input teks).
     */
    private function skpdTersedia()
    {
        $terpakai = User::where('role', 'operator')
            ->whereNotNull('perangkat_daerah_id')
            ->pluck('perangkat_daerah_id');

        return PerangkatDaerah::whereNotIn('id', $terpakai)->orderBy('nama')->get();
    }

    /**
     * Cari SKPD berdasarkan nama (abaikan huruf besar/kecil); buat baru
     * jika belum ada.
     */
    private function resolveSkpd(string $nama): PerangkatDaerah
    {
        $nama = trim(preg_replace('/\s+/', ' ', $nama));

        return PerangkatDaerah::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->first()
            ?? PerangkatDaerah::create(['nama' => $nama]);
    }

    /**
     * Cek aturan 1 SKPD = 1 akun.
     */
    private function skpdSudahPunyaAkun(int $skpdId, ?int $kecualiUserId = null): bool
    {
        return User::where('role', 'operator')
            ->where('perangkat_daerah_id', $skpdId)
            ->when($kecualiUserId, fn ($q) => $q->where('id', '!=', $kecualiUserId))
            ->exists();
    }
}
