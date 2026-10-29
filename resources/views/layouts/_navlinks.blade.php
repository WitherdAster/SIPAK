{{-- Link navigasi dipakai ulang untuk desktop & panel burger mobile. --}}
<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-gauge-high mr-1.5"></i>Dashboard
</a>
@if (auth()->user()->role === 'admin')
<a href="{{ route('verifikasi.index') }}" class="nav-link {{ request()->routeIs('verifikasi.*') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-list-check mr-1.5"></i>Verifikasi
  @if (($pengajuanMenunggu ?? 0) > 0)
  <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-400 text-blue-950">{{ $pengajuanMenunggu }}</span>
  @endif
</a>
<a href="{{ route('akun.index') }}" class="nav-link {{ request()->routeIs('akun.*') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-users mr-1.5"></i>Akun SKPD
</a>
<a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-file-arrow-down mr-1.5"></i>Laporan
</a>
@else
<a href="{{ route('aplikasi.create') }}" class="nav-link {{ request()->routeIs('aplikasi.create', 'aplikasi.store') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-file-circle-plus mr-1.5"></i>Form Pengajuan Aplikasi
</a>
<a href="{{ route('aplikasi.milikku') }}" class="nav-link {{ request()->routeIs('aplikasi.milikku') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-folder-open mr-1.5"></i>Aplikasiku
</a>
<a href="{{ route('laporan.milikku') }}" class="nav-link {{ request()->routeIs('laporan.milikku', 'laporan.unduhMilikku') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-file-arrow-down mr-1.5"></i>Laporan
</a>
@endif
<a href="{{ route('aplikasi.index') }}" class="nav-link {{ request()->routeIs('aplikasi.index') ? 'nav-active' : 'nav-idle' }} {{ ($mobile ?? false) ? 'block w-full text-left' : '' }}">
  <i class="fa-solid fa-layer-group mr-1.5"></i>Semua Aplikasi
</a>
