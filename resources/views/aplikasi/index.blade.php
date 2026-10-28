@extends('layouts.app')

@section('title', 'Semua Aplikasi - SIPAK')

@section('content')
@php
  $skpdDipilih = collect((array) request('skpd', []))->filter(fn ($v) => filled($v))->map(fn ($v) => (string) $v);
  $tahunDipilih = collect((array) request('tahun', []))->filter(fn ($v) => filled($v))->map(fn ($v) => (string) $v);
  $platformDipilih = collect((array) request('platform', []))->filter(fn ($v) => filled($v))->map(fn ($v) => (string) $v);
  $adaFilter = request()->filled('q') || $skpdDipilih->isNotEmpty() || $tahunDipilih->isNotEmpty() || $platformDipilih->isNotEmpty();
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
        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">SKPD</span>
        <details class="filter-dropdown relative">
          <summary class="[&::-webkit-details-marker]:hidden list-none flex items-center justify-between gap-2 w-full text-sm rounded-xl border border-slate-300 bg-white px-3 py-2 cursor-pointer select-none">
            <span class="truncate" data-summary data-default="Semua SKPD">{{ $skpdDipilih->isNotEmpty() ? $skpdDipilih->count().' SKPD dipilih' : 'Semua SKPD' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
          </summary>
          <div class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg p-2">
            @foreach ($skpdList as $skpd)
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
              <input type="checkbox" name="skpd[]" value="{{ $skpd->id }}" @checked($skpdDipilih->contains((string) $skpd->id)) class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
              <span class="truncate">{{ $skpd->nama }}</span>
            </label>
            @endforeach
          </div>
        </details>
      </div>
      <div>
        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Tahun</span>
        <details class="filter-dropdown relative">
          <summary class="[&::-webkit-details-marker]:hidden list-none flex items-center justify-between gap-2 w-full text-sm rounded-xl border border-slate-300 bg-white px-3 py-2 cursor-pointer select-none">
            <span class="truncate" data-summary data-default="Semua Tahun">{{ $tahunDipilih->isNotEmpty() ? $tahunDipilih->count().' tahun dipilih' : 'Semua Tahun' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
          </summary>
          <div class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg p-2">
            @forelse ($tahunList as $tahun)
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
              <input type="checkbox" name="tahun[]" value="{{ $tahun }}" @checked($tahunDipilih->contains((string) $tahun)) class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
              <span>{{ $tahun }}</span>
            </label>
            @empty
            <p class="px-2 py-1.5 text-xs text-slate-400">Belum ada data tahun.</p>
            @endforelse
          </div>
        </details>
      </div>
      <div>
        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Platform</span>
        <details class="filter-dropdown relative">
          <summary class="[&::-webkit-details-marker]:hidden list-none flex items-center justify-between gap-2 w-full text-sm rounded-xl border border-slate-300 bg-white px-3 py-2 cursor-pointer select-none">
            <span class="truncate" data-summary data-default="Semua Platform">{{ $platformDipilih->isNotEmpty() ? $platformDipilih->count().' platform dipilih' : 'Semua Platform' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
          </summary>
          <div class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg p-2">
            @foreach ($platformList as $value => $label)
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
              <input type="checkbox" name="platform[]" value="{{ $value }}" @checked($platformDipilih->contains((string) $value)) class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
              <span>{{ $label }}</span>
            </label>
            @endforeach
          </div>
        </details>
      </div>
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-2 justify-between">
      <div class="flex flex-wrap items-center gap-1.5">
        @if (request()->filled('q'))
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-magnifying-glass mr-1"></i>{{ request('q') }}</span>
        @endif
        @foreach ($skpdList->whereIn('id', $skpdDipilih->map(fn ($v) => (int) $v)) as $skpd)
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-building mr-1"></i>{{ $skpd->nama }}</span>
        @endforeach
        @foreach ($tahunDipilih as $tahun)
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-calendar mr-1"></i>{{ $tahun }}</span>
        @endforeach
        @foreach ($platformDipilih as $platform)
        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-solid fa-mobile-screen mr-1"></i>{{ $platformList[$platform] ?? $platform }}</span>
        @endforeach
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
        <span class="text-[10px] font-semibold text-slate-400 uppercase">{{ $app->platformLabel() }} • {{ $app->tahun ?? '-' }}</span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const dropdowns = document.querySelectorAll('.filter-dropdown');

  const refreshLabel = (dropdown) => {
    const jumlah = dropdown.querySelectorAll('input[type="checkbox"]:checked').length;
    const label = dropdown.querySelector('[data-summary]');
    if (label) {
      label.textContent = jumlah ? `${jumlah} dipilih` : label.dataset.default;
    }
  };

  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener('toggle', () => {
      if (dropdown.open) {
        dropdowns.forEach((lain) => { if (lain !== dropdown) lain.open = false; });
      }
    });

    dropdown.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
      checkbox.addEventListener('change', () => refreshLabel(dropdown));
    });
  });

  document.addEventListener('click', (event) => {
    dropdowns.forEach((dropdown) => {
      if (dropdown.open && !dropdown.contains(event.target)) dropdown.open = false;
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') dropdowns.forEach((dropdown) => { dropdown.open = false; });
  });
});
</script>
@endpush
