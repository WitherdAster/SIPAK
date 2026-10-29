<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\DokumenAplikasi;
use App\Models\PerangkatDaerah;
use App\Models\PerubahanAplikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AplikasiController extends Controller
{
    /**
     * Detail satu aplikasi (untuk modal). Yang tayang publik boleh
     * dilihat semua user login; yang belum diterima hanya pemilik
     * instansi dan admin.
     */
    public function show(Aplikasi $aplikasi): JsonResponse
    {
        $user = auth()->user();

        abort_if(
            $aplikasi->verifikasi !== 'terverifikasi'
                && $user->role !== 'admin'
                && $aplikasi->perangkat_daerah_id !== $user->perangkat_daerah_id,
            403
        );

        $aplikasi->load('perangkatDaerah', 'dokumens');

        $ya = fn (?bool $v) => $v ? 'Ya' : 'Tidak';

        return response()->json([
            'id' => $aplikasi->id,
            'verifikasi' => $aplikasi->verifikasi,
            'status_value' => $aplikasi->status,
            'is_admin' => $user->role === 'admin',
            'is_owner' => (bool) ($user->perangkat_daerah_id
                && $aplikasi->perangkat_daerah_id === $user->perangkat_daerah_id),
            'nama' => $aplikasi->nama,
            'singkatan' => $aplikasi->singkatan,
            'deskripsi' => $aplikasi->deskripsi,
            'tahun' => $aplikasi->tahun,
            'platform' => ucfirst($aplikasi->platform),
            'url' => $aplikasi->url,
            'status' => ucfirst($aplikasi->status),
            'instansi' => $aplikasi->perangkatDaerah->nama ?? '-',
            'label' => $aplikasi->labelVerifikasi(),
            'warna' => $aplikasi->warnaVerifikasi(),
            'catatan' => $aplikasi->catatan_verifikasi,
            'vendor' => $aplikasi->vendor,
            'pic' => $aplikasi->pic_nama,
            'kontak' => $aplikasi->pic_kontak,
            'pengguna' => $aplikasi->jumlah_pengguna,
            'server' => $aplikasi->server_hosting,
            'teknologi' => $aplikasi->teknologi,
            'database' => $aplikasi->db_teknologi,
            'api' => $ya($aplikasi->punya_api),
            'terintegrasi' => $aplikasi->sistem_terintegrasi,
            'splp' => $ya($aplikasi->splp),
            'audit' => $ya($aplikasi->audit_keamanan),
            'backup' => $ya($aplikasi->backup_data),
            'dr' => $ya($aplikasi->disaster_recovery),
            'dokumens' => $aplikasi->dokumens->map(fn ($d) => [
                'label' => $d->labelJenis(),
                'nama' => $d->nama_asli,
                'url' => route('dokumen.lihat', $d),
                'unduh' => route('dokumen.unduh', $d),
            ])->values(),
        ]);
    }

    /**
     * Semua aplikasi: hanya yang sudah diterima (terverifikasi) admin.
     * Yang perlu perbaikan / masih menunggu tidak dipajang di sini.
     */
    public function index(Request $request): View
    {
        $aplikasi = Aplikasi::with('perangkatDaerah')
            ->terverifikasi()
            ->when($request->filled('q'), function ($query) use ($request) {
                $cari = $request->string('q')->toString();

                $query->where(fn ($q) => $q
                    ->where('nama', 'like', "%{$cari}%")
                    ->orWhere('singkatan', 'like', "%{$cari}%"));
            })
            ->when($request->filled('skpd'), fn ($q) => $q->where('perangkat_daerah_id', $request->integer('skpd')))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->integer('tahun')))
            ->when($request->filled('platform'), fn ($q) => $q->where('platform', $request->string('platform')->toString()))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('aplikasi.index', [
            'aplikasi' => $aplikasi,
            'skpdList' => PerangkatDaerah::orderBy('nama')->get(['id', 'nama']),
            'tahunList' => Aplikasi::terverifikasi()
                ->whereNotNull('tahun')
                ->select('tahun')
                ->distinct()
                ->orderByDesc('tahun')
                ->pluck('tahun'),
            'platformList' => ['web' => 'Web', 'android' => 'Android', 'ios' => 'iOS', 'desktop' => 'Desktop'],
        ]);
    }

    /**
     * Aplikasi milik instansi user yang login (yang perlu perbaikan tampil lebih dulu).
     */
    public function milikku(): View
    {
        $aplikasi = Aplikasi::with(['perangkatDaerah', 'perubahanMenunggu', 'perubahanTerakhir'])
            ->where('perangkat_daerah_id', auth()->user()->perangkat_daerah_id)
            ->orderByRaw("CASE WHEN verifikasi = 'dikembalikan' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12);

        return view('aplikasi.milikku', compact('aplikasi'));
    }

    /**
     * Form pengajuan aplikasi baru (atas nama dinas sendiri).
     */
    public function create(): View
    {
        abort_if(! auth()->user()->perangkat_daerah_id, 403, 'Akun admin tidak mengajukan aplikasi.');

        return view('aplikasi.create');
    }

    /**
     * Form edit data aplikasi.
     * Admin boleh semua aplikasi; instansi hanya miliknya sendiri.
     */
    public function edit(Aplikasi $aplikasi): View
    {
        $this->pastikanBolehEditData($aplikasi);

        $aplikasi->load('dokumens');

        $pending = $aplikasi->perubahanAplikasi()
            ->where('status', 'menunggu')
            ->latest()
            ->first();

        return view('aplikasi.edit', compact('aplikasi', 'pending'));
    }

    /**
     * Simpan perubahan data. Admin bebas mengubah semua aplikasi tanpa
     * mengubah status verifikasi. Perubahan dari instansi untuk aplikasi
     * yang sudah berjalan disimpan sebagai pengajuan perubahan dan wajib
     * disetujui admin sebelum data tayang berubah. Aplikasi yang belum
     * berjalan tetap dikirim ulang ke antrean verifikasi.
     */
    public function update(Request $request, Aplikasi $aplikasi): RedirectResponse
    {
        $this->pastikanBolehEditData($aplikasi);

        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $statusLama = $aplikasi->verifikasi;

        $validated = $request->validate($this->rules());

        $atribut = array_filter(
            $validated,
            fn ($key) => ! str_starts_with($key, 'dok_') && ! str_starts_with($key, 'hapus_dok_'),
            ARRAY_FILTER_USE_KEY
        );

        $perubahan = [
            ...$atribut,
            'punya_api' => $request->boolean('punya_api'),
            'splp' => $request->boolean('splp'),
            'audit_keamanan' => $request->boolean('audit_keamanan'),
            'backup_data' => $request->boolean('backup_data'),
            'disaster_recovery' => $request->boolean('disaster_recovery'),
        ];

        if (! $isAdmin && $statusLama === 'terverifikasi') {
            if ($aplikasi->perubahanAplikasi()->where('status', 'menunggu')->exists()) {
                return back()->withErrors([
                    'perubahan' => 'Masih ada pengajuan perubahan yang menunggu persetujuan. Batalkan dulu sebelum mengirim pengajuan baru.',
                ]);
            }

            $this->ajukanPerubahan($request, $aplikasi, $perubahan);

            return redirect()->route('aplikasi.milikku')
                ->with('success', "Pengajuan perubahan {$aplikasi->nama} dikirim. Data lama tetap tayang sampai disetujui Diskominfo.");
        }

        // Edit langsung oleh admin membatalkan pengajuan perubahan yang masih menunggu.
        if ($isAdmin) {
            foreach ($aplikasi->perubahanAplikasi()->where('status', 'menunggu')->get() as $pending) {
                $this->hapusBerkasPengajuan($pending);
                $pending->delete();
            }
        } else {
            $perubahan += [
                'verifikasi' => 'diajukan',
                'catatan_verifikasi' => null,
                'diverifikasi_at' => null,
            ];
        }

        $aplikasi->update($perubahan);

        $this->simpanDokumen($request, $aplikasi);

        if ($isAdmin) {
            return redirect()->route('aplikasi.index')
                ->with('success', "Data {$aplikasi->nama} berhasil diperbarui.");
        }

        $pesan = match ($statusLama) {
            'dikembalikan' => "Perbaikan {$aplikasi->nama} berhasil dikirim ulang, menunggu verifikasi Diskominfo.",
            default => "Perubahan data {$aplikasi->nama} disimpan, menunggu verifikasi Diskominfo.",
        };

        return redirect()->route('aplikasi.milikku')->with('success', $pesan);
    }

    /**
     * Batalkan pengajuan perubahan yang masih menunggu (data lama tetap).
     */
    public function batalkanPerubahan(Aplikasi $aplikasi): RedirectResponse
    {
        $this->pastikanBolehEditData($aplikasi);

        $pending = $aplikasi->perubahanAplikasi()->where('status', 'menunggu')->first();

        abort_if(! $pending, 404, 'Tidak ada pengajuan perubahan yang menunggu.');

        $this->hapusBerkasPengajuan($pending);
        $pending->delete();

        $tujuan = auth()->user()->role === 'admin'
            ? route('aplikasi.index')
            : route('aplikasi.milikku');

        return redirect($tujuan)->with('success', "Pengajuan perubahan {$aplikasi->nama} dibatalkan. Data lama tetap berlaku.");
    }

    /**
     * Simpan pengajuan perubahan (data + dokumen sementara) untuk aplikasi
     * yang sudah berjalan. Dokumen baru belum menyentuh data tayang.
     */
    private function ajukanPerubahan(Request $request, Aplikasi $aplikasi, array $atribut): void
    {
        unset($atribut['status']);

        $folder = 'perubahan/'.Str::uuid();
        $dokumen = [];

        foreach (DokumenAplikasi::JENIS as $jenis => $label) {
            $file = $request->file('dok_'.$jenis);

            if (! $file) {
                continue;
            }

            $dokumen[$jenis] = [
                'nama_asli' => $file->getClientOriginalName(),
                'path' => $file->store($folder, 'public'),
            ];
        }

        $hapus = collect(array_keys(DokumenAplikasi::JENIS))
            ->filter(fn ($jenis) => $request->boolean('hapus_dok_'.$jenis))
            ->reject(fn ($jenis) => isset($dokumen[$jenis]))
            ->values()
            ->all();

        PerubahanAplikasi::create([
            'aplikasi_id' => $aplikasi->id,
            'user_id' => auth()->id(),
            'data' => [
                'atribut' => $atribut,
                'dokumen' => $dokumen,
                'hapus_dokumen' => $hapus,
            ],
            'status' => 'menunggu',
        ]);
    }

    /**
     * Hapus berkas dokumen sementara milik pengajuan perubahan.
     */
    private function hapusBerkasPengajuan(PerubahanAplikasi $perubahan): void
    {
        foreach ($perubahan->dokumenBaru() as $berkas) {
            if (! empty($berkas['path'])) {
                Storage::disk('public')->delete($berkas['path']);
            }
        }
    }

    /**
     * Admin Diskominfo boleh mengubah semua aplikasi; operator hanya
     * aplikasi milik instansinya sendiri.
     */
    private function pastikanBolehEditData(Aplikasi $aplikasi): void
    {
        $user = auth()->user();

        $pemilik = $user->perangkat_daerah_id
            && $aplikasi->perangkat_daerah_id === $user->perangkat_daerah_id;

        abort_unless(
            $user->role === 'admin' || $pemilik,
            403,
            'Hanya admin Diskominfo atau instansi pemilik yang dapat mengubah data aplikasi.'
        );
    }

    /**
     * Simpan / ganti dokumen pendukung. Berkas lama dihapus bila diganti.
     */
    private function simpanDokumen(Request $request, Aplikasi $aplikasi): void
    {
        foreach (DokumenAplikasi::JENIS as $jenis => $label) {
            $file = $request->file('dok_'.$jenis);
            $lama = $aplikasi->dokumens()->where('jenis', $jenis)->first();

            if (! $file) {
                if ($lama && $request->boolean('hapus_dok_'.$jenis)) {
                    Storage::disk('public')->delete($lama->path);
                    $lama->delete();
                }

                continue;
            }

            if ($lama) {
                Storage::disk('public')->delete($lama->path);
                $lama->delete();
            }

            $aplikasi->dokumens()->create([
                'jenis' => $jenis,
                'nama_asli' => $file->getClientOriginalName(),
                'path' => $file->store('dokumen', 'public'),
            ]);
        }
    }

    /**
     * Aturan validasi form pengajuan & perbaikan.
     */
    private function rules(): array
    {
        return [
            // Identitas
            'nama' => ['required', 'string', 'max:255'],
            'singkatan' => ['nullable', 'string', 'max:30'],
            'deskripsi' => ['nullable', 'string'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'platform' => ['required', 'in:web,android,ios,desktop'],
            'url' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', 'in:aktif,nonaktif,pengembangan'],
            // Pengelolaan
            'vendor' => ['nullable', 'string', 'max:255'],
            'pic_nama' => ['nullable', 'string', 'max:255'],
            'pic_kontak' => ['nullable', 'string', 'max:255'],
            'jumlah_pengguna' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            // Infrastruktur
            'server_hosting' => ['nullable', 'string', 'max:255'],
            'teknologi' => ['nullable', 'string', 'max:255'],
            'db_teknologi' => ['nullable', 'string', 'max:255'],
            // Integrasi
            'sistem_terintegrasi' => ['nullable', 'string'],
            // Dokumen (maks 10 MB per berkas)
            'dok_sop' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'dok_manual' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'dok_teknis' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'dok_pks' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'dok_audit' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
            'dok_pendukung' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
            // Tandai hapus dokumen lama saat perbaikan
            ...collect(array_keys(DokumenAplikasi::JENIS))
                ->mapWithKeys(fn ($jenis) => ['hapus_dok_'.$jenis => ['sometimes', 'boolean']])
                ->all(),
        ];
    }

    /**
     * Simpan pengajuan aplikasi baru (status verifikasi: diajukan).
     */
    public function store(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->perangkat_daerah_id, 403, 'Akun admin tidak mengajukan aplikasi.');

        $validated = $request->validate($this->rules());

        $aplikasi = Aplikasi::create([
            ...$validated,
            'status' => $validated['status'] ?? 'aktif',
            'perangkat_daerah_id' => auth()->user()->perangkat_daerah_id,
            'created_by' => auth()->id(),
            'verifikasi' => 'diajukan',
            'punya_api' => $request->boolean('punya_api'),
            'splp' => $request->boolean('splp'),
            'audit_keamanan' => $request->boolean('audit_keamanan'),
            'backup_data' => $request->boolean('backup_data'),
            'disaster_recovery' => $request->boolean('disaster_recovery'),
        ]);

        $this->simpanDokumen($request, $aplikasi);

        return redirect()->route('aplikasi.milikku')
            ->with('success', 'Pengajuan aplikasi berhasil dikirim, menunggu verifikasi Diskominfo.');
    }

    /**
     * Ubah status operasional. Admin: semua aplikasi. Operator: hanya
     * aplikasi instansinya. Status berlaku langsung tanpa verifikasi admin.
     */
    public function updateStatus(Request $request, Aplikasi $aplikasi): RedirectResponse
    {
        $user = auth()->user();
        $isOwner = $user->perangkat_daerah_id
            && $aplikasi->perangkat_daerah_id === $user->perangkat_daerah_id;

        abort_if($user->role !== 'admin' && ! $isOwner, 403);
        abort_if($aplikasi->butuhPerbaikan(), 403, 'Aplikasi sedang dalam perbaikan. Perbarui data lalu kirim ulang.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'pengembangan'])],
        ]);

        $aplikasi->update(['status' => $validated['status']]);

        return redirect(
            $user->role === 'admin' ? route('aplikasi.index') : route('aplikasi.milikku')
        )->with('success', "Status {$aplikasi->nama} diubah menjadi {$validated['status']}.");
    }

    /**
     * Admin: hapus aplikasi beserta dokumennya.
     */
    public function destroy(Aplikasi $aplikasi): RedirectResponse
    {
        $nama = $aplikasi->nama;

        foreach ($aplikasi->dokumens as $dokumen) {
            Storage::disk('public')->delete($dokumen->path);
        }

        foreach ($aplikasi->perubahanAplikasi as $perubahan) {
            if ($perubahan->menunggu()) {
                $this->hapusBerkasPengajuan($perubahan);
            }
        }

        $aplikasi->delete();

        return redirect()->route('aplikasi.index')->with('success', "Aplikasi {$nama} berhasil dihapus.");
    }
}
