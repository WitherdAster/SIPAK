<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'SIPAK Kabupaten Barito Kuala')</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style type="text/tailwindcss">
  body { font-family: 'Inter', sans-serif; }
  .nav-link { @apply text-xs font-semibold px-4 py-2 rounded-full transition; }
  .nav-active { @apply bg-white text-blue-800 shadow; }
  .nav-idle { @apply text-blue-100 hover:bg-white/15 hover:text-white; }
</style>
<style>
  @keyframes marquee-x { from { transform: translateX(0); } to { transform: translateX(-50%); } }
  .marquee-track { display: flex; gap: 1rem; width: max-content; animation: marquee-x 40s linear infinite; }
  .marquee-paused:hover .marquee-track { animation-play-state: paused; }
</style>
@stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
<div class="min-h-screen flex flex-col">
  <!-- Navbar atas -->
  <header class="bg-gradient-to-r from-blue-900 to-blue-600 text-white px-4 sm:px-6 py-3 shadow sticky top-0 z-30">
    <div class="max-w-6xl mx-auto flex items-center gap-3 justify-between">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center">
          <i class="fa-solid fa-shield-halved text-amber-400"></i>
        </div>
        <div>
          <h1 class="font-bold text-sm tracking-wide leading-tight">SIPAK</h1>
          <p class="text-blue-200 text-[11px]">{{ auth()->user()->perangkatDaerah->singkatan ?? 'Barito Kuala' }}</p>
        </div>
      </a>
      <nav class="hidden lg:flex flex-wrap items-center gap-1.5">
        @include('layouts._navlinks')
      </nav>
      <div class="flex items-center gap-2">
        <a href="{{ route('profil.password.edit') }}" class="text-xs font-semibold px-3 sm:px-4 py-2 rounded-full border border-white/30 bg-white/10 hover:bg-white/20 transition" title="Ubah Password">
          <i class="fa-solid fa-key sm:mr-1.5"></i><span class="hidden sm:inline">Password</span>
        </a>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button class="text-xs font-semibold px-4 py-2 rounded-full border border-white/30 bg-white/10 hover:bg-white/20 transition" type="submit">
            <i class="fa-solid fa-arrow-right-from-bracket sm:mr-1.5"></i><span class="hidden sm:inline">Keluar</span>
          </button>
        </form>
        <button id="nav-toggle" class="lg:hidden w-9 h-9 rounded-full border border-white/30 bg-white/10 hover:bg-white/20 transition flex items-center justify-center" type="button" aria-label="Buka menu">
          <i id="nav-toggle-icon" class="fa-solid fa-bars text-sm"></i>
        </button>
      </div>
    </div>
    <div id="nav-mobile" class="hidden lg:hidden max-w-6xl mx-auto mt-2 pb-1">
      <nav class="flex flex-col gap-1 rounded-2xl bg-blue-950/50 border border-white/15 p-2">
        @include('layouts._navlinks', ['mobile' => true])
      </nav>
    </div>
  </header>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('nav-toggle');
  const panel = document.getElementById('nav-mobile');
  const icon = document.getElementById('nav-toggle-icon');
  if (toggle && panel) {
    toggle.addEventListener('click', () => {
      const terbuka = panel.classList.toggle('hidden');
      if (icon) {
        icon.classList.toggle('fa-bars', terbuka);
        icon.classList.toggle('fa-xmark', !terbuka);
      }
    });
  }
});
</script>

  <main class="flex-1 px-4 sm:px-6 py-8">
    <div class="max-w-6xl mx-auto">
      @yield('content')
    </div>
  </main>

  <footer class="px-6 py-4 text-center text-xs text-slate-400 border-t border-slate-200">
    &copy; 2025 Pemerintah Kabupaten Barito Kuala • Diskominfo
  </footer>
</div>

<!-- Modal detail aplikasi -->
<div id="app-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
  <div id="app-modal-backdrop" class="absolute inset-0 bg-blue-950/60 backdrop-blur-sm"></div>
  <div class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl p-6 max-h-[85vh] overflow-y-auto">
    <button id="app-modal-close" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 transition flex items-center justify-center" type="button" aria-label="Tutup">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
    <div id="app-modal-loading" class="py-10 text-center text-sm text-slate-400">
      <i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Memuat detail...
    </div>
    <div id="app-modal-body" class="hidden">
      <div class="flex items-center gap-1.5 pr-8">
        <span id="app-modal-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
        <span id="app-modal-status-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full"></span>
      </div>
      <h3 id="app-modal-nama" class="text-lg font-extrabold mt-2 pr-8"></h3>
      <p id="app-modal-instansi" class="text-xs text-slate-500 mt-1"></p>
      <p id="app-modal-deskripsi" class="text-sm text-slate-600 mt-3 leading-relaxed"></p>
      <div class="grid grid-cols-2 gap-3 mt-4 text-xs">
        <div class="rounded-xl bg-slate-50 border border-slate-100 p-3"><p class="text-slate-400 font-medium">Platform</p><p id="app-modal-platform" class="font-bold mt-0.5"></p></div>
        <div class="rounded-xl bg-slate-50 border border-slate-100 p-3"><p class="text-slate-400 font-medium">Tahun</p><p id="app-modal-tahun" class="font-bold mt-0.5"></p></div>
        <div class="rounded-xl bg-slate-50 border border-slate-100 p-3"><p class="text-slate-400 font-medium">Status</p><p id="app-modal-status" class="font-bold mt-0.5"></p></div>
        <div class="rounded-xl bg-slate-50 border border-slate-100 p-3"><p class="text-slate-400 font-medium">URL</p><p id="app-modal-url" class="font-bold mt-0.5 truncate"></p></div>
      </div>
      <div class="mt-4 text-xs space-y-3">
        <div>
          <p class="font-bold text-slate-700 mb-1.5">Pengelolaan</p>
          <div class="grid grid-cols-2 gap-2">
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5"><p class="text-slate-400">Vendor</p><p id="app-modal-vendor" class="font-bold mt-0.5"></p></div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5"><p class="text-slate-400">PIC</p><p id="app-modal-pic" class="font-bold mt-0.5"></p></div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5"><p class="text-slate-400">Kontak</p><p id="app-modal-kontak" class="font-bold mt-0.5"></p></div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5"><p class="text-slate-400">Pengguna</p><p id="app-modal-pengguna" class="font-bold mt-0.5"></p></div>
          </div>
        </div>
        <div>
          <p class="font-bold text-slate-700 mb-1.5">Infrastruktur</p>
          <div class="grid grid-cols-2 gap-2">
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5 col-span-2"><p class="text-slate-400">Server / Hosting</p><p id="app-modal-server" class="font-bold mt-0.5"></p></div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5"><p class="text-slate-400">Teknologi</p><p id="app-modal-teknologi" class="font-bold mt-0.5"></p></div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 p-2.5"><p class="text-slate-400">Database</p><p id="app-modal-database" class="font-bold mt-0.5"></p></div>
          </div>
        </div>
        <div>
          <p class="font-bold text-slate-700 mb-1.5">Integrasi & Keamanan</p>
          <div id="app-modal-flags" class="flex flex-wrap gap-1.5"></div>
          <p id="app-modal-terintegrasi" class="text-slate-500 mt-1.5"></p>
        </div>
        <div>
          <p class="font-bold text-slate-700 mb-1.5">Dokumen</p>
          <div id="app-modal-dokumens" class="space-y-1.5"></div>
        </div>
      </div>
      <div id="app-modal-catatan-wrap" class="hidden mt-3 rounded-xl bg-red-50 border border-red-100 px-3 py-2.5 text-[11px] text-red-700"></div>
      <div id="app-modal-admin" class="hidden mt-4 pt-4 border-t border-slate-100"></div>
    </div>
  </div>
</div>
<script>
function openAppDetail(id) {
  const modal = document.getElementById('app-modal');
  const loading = document.getElementById('app-modal-loading');
  const body = document.getElementById('app-modal-body');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.style.overflow = 'hidden';
  loading.classList.remove('hidden');
  body.classList.add('hidden');

  fetch(`/aplikasi/${id}?t=${Date.now()}`, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
    .then(res => { if (!res.ok) throw new Error('gagal'); return res.json(); })
    .then(d => {
      const badge = document.getElementById('app-modal-badge');
      badge.textContent = d.label;
      badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ' + d.warna;
      const statusBadge = document.getElementById('app-modal-status-badge');
      const statusWarna = { aktif: 'bg-blue-100 text-blue-700', nonaktif: 'bg-slate-200 text-slate-600', pengembangan: 'bg-purple-100 text-purple-700' }[d.status_value] || 'bg-slate-100 text-slate-500';
      statusBadge.textContent = d.status || '-';
      statusBadge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ' + statusWarna;
      document.getElementById('app-modal-nama').textContent = d.singkatan ? `${d.nama} (${d.singkatan})` : d.nama;
      document.getElementById('app-modal-instansi').innerHTML = '<i class="fa-solid fa-building mr-1"></i>' + d.instansi;
      document.getElementById('app-modal-deskripsi').textContent = d.deskripsi || 'Belum ada deskripsi.';
      document.getElementById('app-modal-platform').textContent = d.platform || '-';
      document.getElementById('app-modal-tahun').textContent = d.tahun || '-';
      document.getElementById('app-modal-status').textContent = d.status || '-';
      const urlEl = document.getElementById('app-modal-url');
      urlEl.innerHTML = d.url ? `<a href="${d.url}" target="_blank" rel="noopener" class="text-blue-700 hover:underline">${d.url}</a>` : '-';
      const set = (id, v) => { document.getElementById(id).textContent = v || '-'; };
      set('app-modal-vendor', d.vendor);
      set('app-modal-pic', d.pic);
      set('app-modal-kontak', d.kontak);
      set('app-modal-pengguna', d.pengguna);
      set('app-modal-server', d.server);
      set('app-modal-teknologi', d.teknologi);
      set('app-modal-database', d.database);
      const flags = document.getElementById('app-modal-flags');
      const flag = (label, ya) => `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${ya === 'Ya' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400'}">${label}: ${ya}</span>`;
      flags.innerHTML = flag('API', d.api) + flag('SPLP', d.splp) + flag('Audit', d.audit) + flag('Backup', d.backup) + flag('DR', d.dr);
      document.getElementById('app-modal-terintegrasi').textContent = d.terintegrasi ? 'Terintegrasi: ' + d.terintegrasi : '';
      const dokWrap = document.getElementById('app-modal-dokumens');
      const esc = (s) => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      dokWrap.innerHTML = d.dokumens.length
        ? d.dokumens.map(x => `<div class="flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-2 hover:border-blue-200 transition"><a href="${x.url}" target="_blank" rel="noopener" class="flex items-center gap-2 flex-1 min-w-0 hover:text-blue-700"><i class="fa-solid fa-file-lines text-blue-600"></i><span class="font-semibold shrink-0">${esc(x.label)}</span><span class="text-slate-400 truncate">${esc(x.nama)}</span><span class="ml-auto text-[10px] font-bold text-blue-600 shrink-0">Lihat</span></a><a href="${x.unduh}" class="text-slate-400 hover:text-blue-700 shrink-0 px-1" title="Unduh"><i class="fa-solid fa-download text-[10px]"></i></a></div>`).join('')
        : '<p class="text-slate-400">Belum ada dokumen.</p>';
      const catatanWrap = document.getElementById('app-modal-catatan-wrap');
      if (d.catatan) {
        const perbaikan = d.verifikasi === 'dikembalikan';
        catatanWrap.textContent = (perbaikan ? 'Catatan Perbaikan dari Diskominfo: ' : 'Informasi Diskominfo: ') + d.catatan;
        catatanWrap.className = 'mt-3 rounded-xl px-3 py-2.5 text-[11px] ' + (perbaikan ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-red-50 border border-red-100 text-red-700');
        catatanWrap.classList.remove('hidden');
      } else {
        catatanWrap.classList.add('hidden');
      }
      const adminWrap = document.getElementById('app-modal-admin');
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      if (d.is_admin && d.verifikasi === 'diajukan') {
        adminWrap.innerHTML = `
          <p class="text-xs font-bold text-slate-700 mb-2">Keputusan Verifikasi</p>
          <form action="/verifikasi/${d.id}/terima" method="POST" class="mb-3">
            <input type="hidden" name="_token" value="${csrf}">
            <button class="w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition" type="submit"><i class="fa-solid fa-check mr-1.5"></i>Terima & Tayangkan</button>
          </form>
          <form action="/verifikasi/${d.id}/perbaikan" method="POST" class="rounded-xl border border-amber-200 bg-amber-50/70 p-3">
            <input type="hidden" name="_token" value="${csrf}">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-amber-700 mb-1.5"><i class="fa-solid fa-screwdriver-wrench mr-1"></i>Minta Perbaikan</label>
            <textarea name="catatan_verifikasi" required maxlength="2000" rows="3" placeholder="Tulis dengan jelas data apa yang harus diperbaiki instansi..." class="w-full text-xs rounded-xl border-slate-300 px-3 py-2 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"></textarea>
            <button class="mt-2 w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 transition" type="submit"><i class="fa-solid fa-paper-plane mr-1.5"></i>Kirim Permintaan Perbaikan</button>
          </form>
          <a href="/aplikasi/${d.id}/edit" class="mt-2 flex items-center justify-center w-full py-2 rounded-xl text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition"><i class="fa-solid fa-pen-to-square mr-1.5"></i>Edit Data Aplikasi</a>`;
        adminWrap.classList.remove('hidden');
      } else if (d.is_admin || d.is_owner) {
        if (d.verifikasi === 'dikembalikan') {
          const labelTombol = d.is_owner ? 'Perbaiki Data Sekarang' : 'Edit Data Aplikasi';
          const tombolPerbaiki = `<a href="/aplikasi/${d.id}/edit" class="mt-2 flex items-center justify-center w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 transition"><i class="fa-solid fa-pen-to-square mr-1.5"></i>${labelTombol}</a>`;
          adminWrap.innerHTML = `<p class="text-[11px] rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2.5"><i class="fa-solid fa-screwdriver-wrench mr-1.5"></i>${d.is_owner ? 'Pengajuan ini perlu perbaikan. Perbarui data sesuai catatan Diskominfo, lalu kirim ulang.' : 'Menunggu perbaikan dari instansi. Pengajuan tidak tayang sampai dikirim ulang dan diterima.'}</p>${tombolPerbaiki}`;
          adminWrap.classList.remove('hidden');
        } else {
        const daftar = ['aktif', 'nonaktif', 'pengembangan'];
        const opts = daftar.map(s => `<option value="${s}" ${s === d.status_value ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`).join('');
        const hapusHtml = d.is_admin ? `
          <form action="/aplikasi/${d.id}" method="POST" onsubmit="return confirm('Hapus aplikasi ini beserta dokumennya?')">
            <input type="hidden" name="_token" value="${csrf}">
            <input type="hidden" name="_method" value="DELETE">
            <button class="w-full py-2.5 rounded-xl text-xs font-semibold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition" type="submit"><i class="fa-solid fa-trash mr-1.5"></i>Hapus Aplikasi</button>
          </form>` : '';
        const editHtml = `<a href="/aplikasi/${d.id}/edit" class="mt-2 flex items-center justify-center w-full py-2.5 rounded-xl text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition"><i class="fa-solid fa-pen-to-square mr-1.5"></i>Edit Data Aplikasi</a>`;
        adminWrap.innerHTML = `
          <p class="text-xs font-bold text-slate-700 mb-2">${d.is_admin ? 'Kelola Aplikasi (Admin)' : 'Kelola Aplikasi Saya'}</p>
          <form action="/aplikasi/${d.id}/status" method="POST" class="flex gap-2 mb-2">
            <input type="hidden" name="_token" value="${csrf}">
            <input type="hidden" name="_method" value="PUT">
            <select name="status" onchange="syncStatusBadge(this)" class="flex-1 text-xs rounded-xl border-slate-300 px-3 py-2">${opts}</select>
            <button class="py-2 px-4 rounded-xl text-xs font-semibold text-white bg-blue-700 hover:bg-blue-800 transition shrink-0" type="submit"><i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan Status</button>
          </form>
          <p class="text-[10px] text-slate-400 mb-2"><i class="fa-solid fa-bolt mr-1"></i>Perubahan status langsung berlaku, tanpa verifikasi admin. Pilih <span class="font-semibold">Pengembangan</span> saat aplikasi sedang di-update.</p>
          ${editHtml}
          <div class="mt-2">${hapusHtml}</div>`;
        adminWrap.classList.remove('hidden');
        }
      } else {
        adminWrap.classList.add('hidden');
      }
      loading.classList.add('hidden');
      body.classList.remove('hidden');
    })
    .catch(() => {
      loading.innerHTML = 'Gagal memuat detail. Silakan coba lagi.';
    });
}
function syncStatusBadge(sel) {
  const label = sel.options[sel.selectedIndex].text;
  document.getElementById('app-modal-status').textContent = label;
  const warna = { aktif: 'bg-blue-100 text-blue-700', nonaktif: 'bg-slate-200 text-slate-600', pengembangan: 'bg-purple-100 text-purple-700' }[sel.value] || 'bg-slate-100 text-slate-500';
  const badge = document.getElementById('app-modal-status-badge');
  badge.textContent = label;
  badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ' + warna;
}
function closeAppDetail() {
  const modal = document.getElementById('app-modal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.style.overflow = '';
}
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('app-modal-close').addEventListener('click', closeAppDetail);
  document.getElementById('app-modal-backdrop').addEventListener('click', closeAppDetail);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAppDetail(); });
});
</script>
@stack('scripts')
</body>
</html>
