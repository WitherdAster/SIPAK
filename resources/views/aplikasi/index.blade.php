@extends('layouts.app')

@section('title', 'Semua Aplikasi - SIPAK')

@section('content')
@php
  $adaFilter = request()->filled('q') || request()->filled('skpd') || request()->filled('tahun') || request()->filled('platform');
@endphp

<div class="space-y-5">
  <div class="flex flex-wrap items-center gap-3 justify-between">
    <div>
      <h2 class="text-xl font-extrabold">Semua Aplikasi</h2>
      <p class="text-xs text-slate-500">Seluruh aplikasi dari semua instansi • <span class="font-semibold text-slate-600">{{ $aplikasi->total() }} aplikasi</span></p>
    </div>
    

  @if (session('success'))
  <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700">{{ session('success') }}</div>
  @endif
 

  <!-- Filter -->
  <form action="{{ route('aplikasi.index') }}" method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
      <div class="sm:col-span-2">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5" for="q">Cari Aplikasi</label>
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input id="q" name="q" value="{{ request('q') }}" placeholder="Nama atau singkatan..." class="w-full text-sm rounded-xl border-slate-300 pl-9 pr-3 py-2">
        </div>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5" for="skpd">SKPD</label>
        <select id="skpd" name="skpd" onchange="this.form.submit()" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
          <option value="">Semua SKPD</option>
          @foreach ($skpdList as $skpd)
          <option value="{{ $skpd->id }}" {{ (string) request('skpd') === (string) $skpd->id ? 'selected' : '' }}>{{ $skpd->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5" for="tahun">Tahun</label>
        <select id="tahun" name="tahun" onchange="this.form.submit()" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
          <option value="">Semua Tahun</option>
          @foreach ($tahunList as $tahun)
          <option value="{{ $tahun }}" {{ (string) request('tahun') === (string) $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5" for="platform">Platform</label>
        <select id="platform" name="platform" onchange="this.form.submit()" class="w-full text-sm rounded-xl border-slate-300 px-3 py-2">
          <option value="">Semua Platform</option>
          @foreach ($platformList as $value => $label)
          <option value="{{ $value }}" {{ request('platform') === $value ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-2 justify-between">
      <div class="flex flex-wrap items-center gap-1.5">
        @if (request()->filled('q'))
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-magnifying-glass mr-1"></i>{{ request('q') }}</span>
        @endif
        @if (request()->filled('skpd'))
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-building mr-1"></i>{{ $skpdList->firstWhere('id', (int) request('skpd'))?->nama ?? 'SKPD' }}</span>
        @endif
        @if (request()->filled('tahun'))
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-calendar mr-1"></i>{{ request('tahun') }}</span>
        @endif
        @if (request()->filled('platform'))
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-mobile-screen mr-1"></i>{{ $platformList[request('platform')] ?? request('platform') }}</span>
        @endif
        @unless ($adaFilter)
        <span class="text-[11px] text-slate-400">Pilih filter untuk mempersempit hasil.</span>
        @endunless
      </div>
      <div class="flex gap-2">
        <a href="{{ route('aplikasi.index') }}" class="text-xs font-semibold px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Bersihkan</a>
        <button class="text-xs font-semibold px-4 py-2 rounded-xl bg-blue-700 text-white hover:bg-blue-800 transition" type="submit"><i class="fa-solid fa-filter mr-1.5"></i>Terapkan</button>
      </div>
    </div>
  </form>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($aplikasi as $app)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 cursor-pointer hover:shadow transition" onclick="openAppDetail({{ $app->id }})">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-1">
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaVerifikasi() }}">{{ $app->labelVerifikasi() }}</span>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaStatus() }}">{{ ucfirst($app->status) }}</span>
        </div>
        <span class="text-[10px] font-semibold text-slate-400 uppercase">{{ $app->platform }} • {{ $app->tahun ?? '-' }}</span>
      </div>
      <p class="font-bold">{{ $app->nama }} @if($app->singkatan)<span class="text-slate-400 font-medium text-xs">({{ $app->singkatan }})</span>@endif</p>
      <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-building mr-1"></i>{{ $app->perangkatDaerah->nama ?? '-' }}</p>
      <p class="text-xs text-slate-400 mt-2 line-clamp-2">{{ $app->deskripsi }}</p>
    </div>
    @empty
    <div class="col-span-full text-center py-10">
      <p class="text-sm text-slate-400">Tidak ada aplikasi yang cocok dengan filter.</p>
      @if ($adaFilter)
      <a href="{{ route('aplikasi.index') }}" class="mt-2 inline-block text-xs font-semibold text-blue-700 hover:underline">Reset filter</a>
      @endif
    </div>
    @endforelse
  </div>

  {{ $aplikasi->links() }}
</div>
@endsection
