<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Aplikasi extends Model
{
    use HasFactory;

    public const PLATFORMS = ['web' => 'Web', 'android' => 'Android', 'ios' => 'iOS', 'desktop' => 'Desktop'];

    protected $table = 'aplikasi';

    protected $fillable = [
        'perangkat_daerah_id',
        'created_by',
        'nama',
        'singkatan',
        'deskripsi',
        'tahun',
        'platform',
        'url',
        'status',
        'verifikasi',
        'catatan_verifikasi',
        'diverifikasi_at',
        'vendor',
        'pic_nama',
        'pic_kontak',
        'jumlah_pengguna',
        'server_hosting',
        'teknologi',
        'db_teknologi',
        'punya_api',
        'sistem_terintegrasi',
        'splp',
        'audit_keamanan',
        'backup_data',
        'disaster_recovery',
    ];

    protected $casts = [
        'punya_api' => 'boolean',
        'splp' => 'boolean',
        'audit_keamanan' => 'boolean',
        'backup_data' => 'boolean',
        'disaster_recovery' => 'boolean',
        'diverifikasi_at' => 'datetime',
    ];

    /**
     * Platform disimpan sebagai daftar JSON agar satu aplikasi bisa
     * berjalan di beberapa platform sekaligus.
     */
    protected function platform(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    $nilai = $value;
                } elseif (! is_string($value) || $value === '') {
                    $nilai = [];
                } else {
                    $decoded = json_decode($value, true);
                    $nilai = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
                }

                $nilai = is_array($nilai) ? $nilai : [$nilai];

                return array_values(array_filter(
                    array_map(fn ($platform) => (string) $platform, $nilai),
                    fn ($platform) => $platform !== ''
                ));
            },
            set: function ($value) {
                $nilai = is_array($value) ? $value : [$value];

                $nilai = array_values(array_unique(array_filter(
                    array_map(fn ($platform) => (string) $platform, $nilai),
                    fn ($platform) => array_key_exists($platform, self::PLATFORMS)
                )));

                return json_encode($nilai);
            },
        );
    }

    /**
     * Label platform untuk ditampilkan, mis. "Web, Android".
     */
    public function platformLabel(): string
    {
        $label = collect($this->platform)
            ->map(fn ($platform) => self::PLATFORMS[$platform] ?? ucfirst($platform))
            ->implode(', ');

        return $label !== '' ? $label : '—';
    }

    public function perangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(PerangkatDaerah::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');

    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(DokumenAplikasi::class);
    }

    public function perubahanAplikasi(): HasMany
    {
        return $this->hasMany(PerubahanAplikasi::class);
    }

    public function perubahanMenunggu(): HasOne
    {
        return $this->hasOne(PerubahanAplikasi::class)->where('status', 'menunggu')->latestOfMany();
    }

    public function perubahanTerakhir(): HasOne
    {
        return $this->hasOne(PerubahanAplikasi::class)->latestOfMany();
    }

    /**
     * Hanya yang diterima admin yang boleh tayang publik.
     */
    public function scopeTerverifikasi($query)
    {
        return $query->where('verifikasi', 'terverifikasi');
    }

    /**
     * Label status pengajuan untuk dipantau instansi.
     */
    public function labelVerifikasi(): string
    {
        return match ($this->verifikasi) {
            'terverifikasi' => 'Diterima',
            'dikembalikan' => 'Perlu Perbaikan',
            default => 'Menunggu',
        };
    }

    public function warnaVerifikasi(): string
    {
        return match ($this->verifikasi) {
            'terverifikasi' => 'bg-emerald-100 text-emerald-700',
            'dikembalikan' => 'bg-orange-100 text-orange-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    /**
     * Cek apakah pengajuan sedang menunggu perbaikan dari instansi.
     */
    public function butuhPerbaikan(): bool
    {
        return $this->verifikasi === 'dikembalikan';
    }

    /**
     * Warna badge status operasional (aktif/nonaktif/pengembangan).
     */
    public function warnaStatus(): string
    {
        return match ($this->status) {
            'aktif' => 'bg-blue-100 text-blue-700',
            'nonaktif' => 'bg-slate-200 text-slate-600',
            'pengembangan' => 'bg-purple-100 text-purple-700',
            default => 'bg-slate-100 text-slate-500',
        };
    }
}
