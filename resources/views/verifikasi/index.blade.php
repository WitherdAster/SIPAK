@extends('layouts.app')

@section('title', 'Verifikasi Pengajuan - SIPAK')

@section('content')
<div class="space-y-6">
  <div>
    <h2 class="text-xl font-extrabold">Verifikasi Pengajuan</h2>
    <p class="text-xs text-slate-500">Pengajuan aplikasi baru dan pengajuan perubahan data dipisah agar mudah ditinjau.</p>
  </div>

  @if (session('success'))
  <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $errors->first() }}</div>
  @endif

  <!-- Pengajuan aplikasi baru -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <div class="flex flex-wrap items-start gap-2 justify-between mb-1">
      <h3 class="font-bold text-sm">Pengajuan Aplikasi Baru <span class="ml-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $menunggu->total() }}</span></h3>
      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-file-circle-plus mr-1"></i>Aplikasi belum tayang</span>
    </div>
    <p class="text-[11px] text-slate-400 mb-4">Buka detail untuk membaca seluruh data terlebih dahulu. Terima untuk menayangkan, atau minta perbaikan dengan catatan yang jelas agar instansi mudah memperbaikinya.</p>
    <div class="space-y-4">
      @forelse ($menunggu as $app)
      <div class="rounded-xl border border-slate-200 p-4">
        <div class="flex flex-wrap items-center gap-2 justify-between">
          <div>
            <p class="font-bold text-sm">{{ $app->nama }} @if($app->singkatan)<span class="text-slate-400 font-medium text-xs">({{ $app->singkatan }})</span>@endif</p>
            <p class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-building mr-1"></i>{{ $app->perangkatDaerah->nama ?? '-' }} • {{ ucfirst($app->platform) }} • {{ $app->tahun ?? '-' }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5"><i class="fa-solid fa-user mr-1"></i>Diajukan {{ $app->creator->name ?? '-' }} • {{ $app->created_at?->format('d M Y H:i') ?? '-' }}</p>
            @if ($app->deskripsi)<p class="text-xs text-slate-500 mt-1.5 line-clamp-2">{{ $app->deskripsi }}</p>@endif
          </div>
          <button onclick="openAppDetail({{ $app->id }})" class="text-xs font-semibold px-4 py-2 rounded-xl bg-blue-700 text-white hover:bg-blue-800 transition shrink-0" type="button"><i class="fa-solid fa-eye mr-1.5"></i>Lihat Detail & Verifikasi</button>
        </div>
      </div>
      @empty
      <p class="text-sm text-slate-400 text-center py-6">Tidak ada pengajuan aplikasi baru. Semua sudah diverifikasi.</p>
      @endforelse
    </div>
    <div class="mt-4">{{ $menunggu->links() }}</div>
  </div>

  <!-- Pengajuan perubahan data -->
  <div class="bg-white rounded-2xl shadow-sm border border-sky-200 p-6">
    <div class="flex flex-wrap items-start gap-2 justify-between mb-1">
      <h3 class="font-bold text-sm">Pengajuan Perubahan Data <span class="ml-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-700">{{ $perubahan->total() }}</span></h3>
      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200"><i class="fa-solid fa-pen-to-square mr-1"></i>Aplikasi sudah berjalan</span>
    </div>
    <p class="text-[11px] text-slate-400 mb-4">Ini pengajuan perubahan dari instansi untuk aplikasi yang sudah tayang. Data lama tetap berlaku sampai Anda menyetujui. Bandingkan data lama vs baru sebelum memutuskan.</p>
    <div class="space-y-4">
      @forelse ($perubahan as $p)
      <div class="rounded-xl border border-sky-100 bg-sky-50/40 p-4">
        <div class="flex flex-wrap items-center gap-2 justify-between">
          <div>
            <p class="font-bold text-sm">{{ $p->aplikasi->nama ?? '-' }} @if($p->aplikasi?->singkatan)<span class="text-slate-400 font-medium text-xs">({{ $p->aplikasi->singkatan }})</span>@endif</p>
            <p class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-building mr-1"></i>{{ $p->aplikasi->perangkatDaerah->nama ?? '-' }} • Diajukan {{ $p->pengaju->name ?? '-' }} • {{ $p->created_at?->format('d M Y H:i') ?? '-' }}</p>
            <p class="text-[11px] text-sky-700 mt-1">
              <i class="fa-solid fa-file-arrow-up mr-1"></i>{{ count($p->dokumenBaru()) }} dokumen baru
              @if (count($p->dokumenDihapus()))
              <span class="mx-1">•</span><i class="fa-solid fa-file-circle-minus mr-1"></i>{{ count($p->dokumenDihapus()) }} dokumen dihapus
              @endif
            </p>
          </div>
          <a href="{{ route('verifikasi.perubahan.show', $p) }}" class="text-xs font-semibold px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700 transition shrink-0"><i class="fa-solid fa-code-compare mr-1.5"></i>Bandingkan & Putuskan</a>
        </div>
      </div>
      @empty
      <p class="text-sm text-slate-400 text-center py-6">Tidak ada pengajuan perubahan data yang menunggu.</p>
      @endforelse
    </div>
    <div class="mt-4">{{ $perubahan->links() }}</div>
  </div>

  <!-- Riwayat pengajuan aplikasi -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-3">Riwayat Verifikasi Aplikasi</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead><tr class="text-left text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-100">
          <th class="py-2 pr-3">Aplikasi</th><th class="py-2 pr-3">Instansi</th><th class="py-2 pr-3">Hasil</th><th class="py-2 pr-3">Waktu</th><th class="py-2">Informasi</th>
        </tr></thead>
        <tbody>
          @forelse ($riwayat as $app)
          <tr class="border-b border-slate-50">
            <td class="py-2.5 pr-3 font-semibold">{{ $app->nama }}</td>
            <td class="py-2.5 pr-3 text-slate-500">{{ $app->perangkatDaerah->singkatan ?? '-' }}</td>
            <td class="py-2.5 pr-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaVerifikasi() }}">{{ $app->labelVerifikasi() }}</span></td>
            <td class="py-2.5 pr-3 text-slate-500 whitespace-nowrap">{{ $app->diverifikasi_at?->format('d M Y H:i') ?? '—' }}</td>
            <td class="py-2.5 {{ $app->butuhPerbaikan() ? 'text-amber-700 font-medium' : 'text-slate-500' }}">{{ $app->catatan_verifikasi ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Riwayat perubahan data -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-3">Riwayat Keputusan Perubahan Data</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead><tr class="text-left text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-100">
          <th class="py-2 pr-3">Aplikasi</th><th class="py-2 pr-3">Instansi</th><th class="py-2 pr-3">Pengaju</th><th class="py-2 pr-3">Hasil</th><th class="py-2 pr-3">Waktu</th><th class="py-2">Catatan</th>
        </tr></thead>
        <tbody>
          @forelse ($riwayatPerubahan as $p)
          <tr class="border-b border-slate-50">
            <td class="py-2.5 pr-3 font-semibold">{{ $p->aplikasi->nama ?? '-' }}</td>
            <td class="py-2.5 pr-3 text-slate-500">{{ $p->aplikasi->perangkatDaerah->singkatan ?? '-' }}</td>
            <td class="py-2.5 pr-3 text-slate-500">{{ $p->pengaju->name ?? '-' }}</td>
            <td class="py-2.5 pr-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $p->warnaStatus() }}">{{ $p->labelStatus() }}</span></td>
            <td class="py-2.5 pr-3 text-slate-500 whitespace-nowrap">{{ $p->diputuskan_at?->format('d M Y H:i') ?? '—' }}</td>
            <td class="py-2.5 text-slate-500">{{ $p->catatan_admin ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada keputusan perubahan data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
