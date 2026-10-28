<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public const STATUS = ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'pengembangan' => 'Pengembangan'];

    public const PLATFORM = ['web' => 'Web', 'android' => 'Android', 'ios' => 'iOS', 'desktop' => 'Desktop'];

    /**
     * Halaman filter laporan. Kosongkan semua = unduh seluruh data.
     */
    public function index(Request $request): View
    {
        return $this->halaman($request, null, 'Laporan Aplikasi', 'laporan.index', 'laporan.unduh');
    }

    /**
     * Halaman filter laporan khusus aplikasiku (instansi sendiri).
     */
    public function milikku(Request $request): View
    {
        abort_if(! auth()->user()->perangkat_daerah_id, 403);

        return $this->halaman(
            $request,
            auth()->user()->perangkat_daerah_id,
            'Laporan Aplikasiku',
            'laporan.milikku',
            'laporan.unduhMilikku',
            'Rekap aplikasi milik '.auth()->user()->perangkatDaerah->nama.'.'
        );
    }

    private function halaman(Request $request, ?int $skpdId, string $judul, string $ruteFilter, string $ruteUnduh, string $sub = ''): View
    {
        $filter = $request->validate([
            'status' => ['nullable', 'in:aktif,nonaktif,pengembangan'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'platform' => ['nullable', 'in:web,android,ios,desktop'],
        ]);

        $tahunList = $this->query([], $skpdId)->whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');
        $jumlah = $this->query($filter, $skpdId)->count();

        return view('laporan.index', [
            'filter' => $filter,
            'tahunList' => $tahunList,
            'jumlah' => $jumlah,
            'statusList' => self::STATUS,
            'platformList' => self::PLATFORM,
            'judul' => $judul,
            'subjudul' => $sub ?: 'Unduh rekap keseluruhan aplikasi (PDF). Kosongkan filter untuk mengunduh semua data.',
            'ruteFilter' => $ruteFilter,
            'ruteUnduh' => $ruteUnduh,
        ]);
    }

    /**
     * Unduh PDF laporan rekap aplikasi.
     */
    public function unduh(Request $request): Response
    {
        return $this->unduhPdf($request, null, 'laporan-sipak-');
    }

    /**
     * Unduh PDF laporan khusus aplikasiku (instansi sendiri).
     */
    public function unduhMilikku(Request $request): Response
    {
        abort_if(! auth()->user()->perangkat_daerah_id, 403);

        return $this->unduhPdf($request, auth()->user()->perangkat_daerah_id, 'laporan-aplikasiku-');
    }

    private function unduhPdf(Request $request, ?int $skpdId, string $awalan): Response
    {
        $filter = $request->validate([
            'status' => ['nullable', 'in:aktif,nonaktif,pengembangan'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'platform' => ['nullable', 'in:web,android,ios,desktop'],
        ]);

        $aplikasi = $this->query($filter, $skpdId)->orderBy('nama')->get();
        $namaBerkas = $awalan.now()->format('Ymd-His').'.pdf';

        $opsi = new Options;
        $opsi->set('defaultFont', 'DejaVu Sans');
        $opsi->set('isRemoteEnabled', false);

        $pdf = new Dompdf($opsi);
        $pdf->loadHtml(view('laporan.pdf', [
            'aplikasi' => $aplikasi,
            'filter' => array_filter($filter),
            'statusList' => self::STATUS,
            'platformList' => self::PLATFORM,
            'diunduhPada' => now()->format('d M Y H:i'),
        ])->render());
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$namaBerkas.'"',
        ]);
    }

    private function query(array $filter, ?int $skpdId = null)
    {
        return Aplikasi::with('perangkatDaerah')
            ->when($skpdId, fn ($q) => $q->where('perangkat_daerah_id', $skpdId))
            ->when($filter['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filter['tahun'] ?? null, fn ($q, $v) => $q->where('tahun', $v))
            ->when($filter['platform'] ?? null, fn ($q, $v) => $q->where('platform', 'like', '%"'.$v.'"%'));
    }
}
