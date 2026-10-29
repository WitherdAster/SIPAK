@extends('layouts.app')

@section('title', 'Aplikasiku - SIPAK')

@section('content')
<div class="space-y-5">
  <div class="flex flex-wrap items-center gap-3 justify-between">
    <div>
      <h2 class="text-xl font-extrabold">Aplikasiku</h2>
      <p class="text-xs text-slate-500">Aplikasi milik {{ auth()->user()->perangkatDaerah->nama ?? 'instansimu' }}</p>
    </div>
    <a href="{{ route('aplikasi.create') }}" class="text-xs font-semibold px-4 py-2 rounded-xl bg-blue-700 text-white hover:bg-blue-800 transition"><i class="fa-solid fa-plus mr-1.5"></i>Ajukan Baru</a>
  </div>

  @if (session('success'))
  <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700">{{ session('success') }}</div>
  @endif

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($aplikasi as $app)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 cursor-pointer hover:shadow transition" onclick="openAppDetail({{ $app->id }})">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-1">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaVerifikasi() }}">{{ $app->labelVerifikasi() }}</span>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaStatus() }}">{{ ucfirst($app->status) }}</span>
        </div>
        <span class="text-[10px] font-semibold text-slate-400 uppercase">{{ $app->platformLabel() }} • {{ $app->tahun ?? '-' }}</span>
      </div>
      <p class="font-bold">{{ $app->nama }} @if($app->singkatan)<span class="text-slate-400 font-medium text-xs">({{ $app->singkatan }})</span>@endif</p>
      <p class="text-xs text-slate-400 mt-2 line-clamp-2">{{ $app->deskripsi }}</p>
      @if ($app->butuhPerbaikan())
      <div class="mt-3 rounded-xl bg-amber-50 border border-amber-200 px-3 py-2.5">
        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700"><i class="fa-solid fa-screwdriver-wrench mr-1"></i>Perlu Perbaikan</p>
        <p class="text-[11px] text-amber-900 mt-1 whitespace-pre-line">{{ $app->catatan_verifikasi ?: 'Tanpa catatan.' }}</p>
        <a href="{{ route('aplikasi.edit', $app) }}" onclick="event.stopPropagation()" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-amber-700 transition"><i class="fa-solid fa-pen-to-square"></i>Perbaiki Data</a>
      </div>
      @elseif ($app->perubahanMenunggu)
      <div class="mt-3 rounded-xl bg-sky-50 border border-sky-200 px-3 py-2.5">
        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-700"><i class="fa-solid fa-hourglass-half mr-1"></i>Perubahan Menunggu ACC</p>
        <p class="text-[11px] text-sky-900 mt-1">Diajukan {{ $app->perubahanMenunggu->created_at?->format('d M Y H:i') ?? '-' }}. Data lama tetap tayang.</p>
        <a href="{{ route('aplikasi.edit', $app) }}" onclick="event.stopPropagation()" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-sky-700 transition"><i class="fa-solid fa-magnifying-glass"></i>Lihat Pengajuan</a>
      </div>
      @elseif ($app->perubahanTerakhir && $app->perubahanTerakhir->status === 'ditolak')
      <div class="mt-3 rounded-xl bg-red-50 border border-red-200 px-3 py-2.5">
        <p class="text-[10px] font-bold uppercase tracking-wider text-red-700"><i class="fa-solid fa-circle-xmark mr-1"></i>Perubahan Ditolak</p>
        <p class="text-[11px] text-red-900 mt-1 whitespace-pre-line">{{ $app->perubahanTerakhir->catatan_admin ?: 'Tanpa catatan.' }}</p>
      </div>
      @elseif ($app->catatan_verifikasi)
      <p class="text-[11px] mt-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-600 px-3 py-2">Catatan Diskominfo: {{ $app->catatan_verifikasi }}</p>
      @endif
      @unless ($app->butuhPerbaikan() || $app->perubahanMenunggu)
      <div class="mt-3 flex justify-end">
        <a href="{{ route('aplikasi.edit', $app) }}" onclick="event.stopPropagation()" class="text-[11px] font-semibold text-blue-700 hover:underline"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit Data</a>
      </div>
      @endunless
    </div>
    @empty
    <p class="text-sm text-slate-400 col-span-full text-center py-10">Belum ada aplikasi. <a href="{{ route('aplikasi.create') }}" class="text-blue-700 font-semibold hover:underline">Ajukan sekarang</a>.</p>
    @endforelse
  </div>

  {{ $aplikasi->links() }}
</div>
@endsection
