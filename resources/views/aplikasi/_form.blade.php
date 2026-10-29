@php
  $lama = $aplikasi ?? null;
  $dokumen = $lama?->dokumens?->keyBy('jenis') ?? collect();
@endphp

@if ($errors->any())
<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $errors->first() }}</div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
  @csrf
  @if (($method ?? 'POST') !== 'POST')
  @method($method)
  @endif

  <!-- 1. Identitas Aplikasi -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-1"><span class="inline-flex w-6 h-6 rounded-lg bg-blue-700 text-white text-xs items-center justify-center mr-2">1</span>Identitas Aplikasi</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="nama">Nama Aplikasi *</label>
        <input id="nama" name="nama" value="{{ old('nama', $lama->nama ?? '') }}" required class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: E-Rapor Batola">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="singkatan">Singkatan</label>
        <input id="singkatan" name="singkatan" value="{{ old('singkatan', $lama->singkatan ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: E-Rapor">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="tahun">Tahun Pembuatan</label>
        <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ old('tahun', $lama->tahun ?? date('Y')) }}" class="w-full rounded-xl border-slate-300 text-sm">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="platform">Platform *</label>
        <select id="platform" name="platform" class="w-full rounded-xl border-slate-300 text-sm">
          @foreach (['web' => 'Web', 'android' => 'Android', 'ios' => 'iOS', 'desktop' => 'Desktop'] as $val => $label)
          <option value="{{ $val }}" {{ old('platform', $lama->platform ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-xs sm:col-span-2">
        <input type="hidden" name="status" value="{{ $lama->status ?? 'aktif' }}">
        <span class="text-slate-400 font-medium">{{ $lama ? 'Status Saat Ini' : 'Status Awal' }}</span>
        <p class="font-bold text-sm mt-0.5">{{ ucfirst($lama->status ?? 'aktif') }} <span class="font-normal text-slate-400">({{ $lama ? 'admin dapat mengubah lewat Kelola Aplikasi' : 'otomatis — admin dapat mengubah lewat Kelola Aplikasi' }})</span></p>
      </div>
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="url">URL / Domain</label>
        <input id="url" name="url" type="url" value="{{ old('url', $lama->url ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="https://...">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="3" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Fungsi utama aplikasi...">{{ old('deskripsi', $lama->deskripsi ?? '') }}</textarea>
      </div>
    </div>
  </div>

  <!-- 2. Pengelolaan -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-1"><span class="inline-flex w-6 h-6 rounded-lg bg-emerald-600 text-white text-xs items-center justify-center mr-2">2</span>Pengelolaan</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
      <div class="sm:col-span-2 rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-xs">
        <span class="text-slate-400 font-medium">SKPD Pemilik</span>
        <p class="font-bold text-sm mt-0.5">{{ auth()->user()->perangkatDaerah->nama ?? '-' }} (otomatis dari akun)</p>
      </div>
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="vendor">Pengembang / Vendor</label>
        <input id="vendor" name="vendor" value="{{ old('vendor', $lama->vendor ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: PT Maju Bersama / Swakelola">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="pic_nama">Nama PIC</label>
        <input id="pic_nama" name="pic_nama" value="{{ old('pic_nama', $lama->pic_nama ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Nama penanggung jawab">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="pic_kontak">Kontak PIC</label>
        <input id="pic_kontak" name="pic_kontak" value="{{ old('pic_kontak', $lama->pic_kontak ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="No. HP / email">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="jumlah_pengguna">Jumlah Pengguna</label>
        <input id="jumlah_pengguna" name="jumlah_pengguna" type="number" min="0" value="{{ old('jumlah_pengguna', $lama->jumlah_pengguna ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: 150">
      </div>
    </div>
  </div>

  <!-- 3. Infrastruktur -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-1"><span class="inline-flex w-6 h-6 rounded-lg bg-violet-600 text-white text-xs items-center justify-center mr-2">3</span>Infrastruktur</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="server_hosting">Server / Hosting</label>
        <input id="server_hosting" name="server_hosting" value="{{ old('server_hosting', $lama->server_hosting ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: VPS Diskominfo / Pusdatin Kemendagri">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="teknologi">Teknologi</label>
        <input id="teknologi" name="teknologi" value="{{ old('teknologi', $lama->teknologi ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: Laravel + Vue">
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="db_teknologi">Database</label>
        <input id="db_teknologi" name="db_teknologi" value="{{ old('db_teknologi', $lama->db_teknologi ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: MySQL / PostgreSQL">
      </div>
    </div>
  </div>

  <!-- 4. Integrasi -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-1"><span class="inline-flex w-6 h-6 rounded-lg bg-sky-600 text-white text-xs items-center justify-center mr-2">4</span>Integrasi</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
      <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer text-sm font-medium">
        <input type="checkbox" name="punya_api" value="1" {{ old('punya_api', $lama->punya_api ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-700 w-4 h-4">Menyediakan API
      </label>
      <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer text-sm font-medium">
        <input type="checkbox" name="splp" value="1" {{ old('splp', $lama->splp ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-700 w-4 h-4">Terhubung SPLP / SPLPD
      </label>
      <div class="sm:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="sistem_terintegrasi">Sistem Terintegrasi</label>
        <textarea id="sistem_terintegrasi" name="sistem_terintegrasi" rows="2" class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: Terhubung dengan SIPD dan IKD">{{ old('sistem_terintegrasi', $lama->sistem_terintegrasi ?? '') }}</textarea>
      </div>
    </div>
  </div>

  <!-- 5. Keamanan -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-1"><span class="inline-flex w-6 h-6 rounded-lg bg-amber-500 text-white text-xs items-center justify-center mr-2">5</span>Keamanan</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
      <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer text-sm font-medium">
        <input type="checkbox" name="audit_keamanan" value="1" {{ old('audit_keamanan', $lama->audit_keamanan ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-700 w-4 h-4">Sudah Audit
      </label>
      <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer text-sm font-medium">
        <input type="checkbox" name="backup_data" value="1" {{ old('backup_data', $lama->backup_data ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-700 w-4 h-4">Backup Data
      </label>
      <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer text-sm font-medium">
        <input type="checkbox" name="disaster_recovery" value="1" {{ old('disaster_recovery', $lama->disaster_recovery ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-700 w-4 h-4">Disaster Recovery
      </label>
    </div>
  </div>

  <!-- 6. Dokumen -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <h3 class="font-bold text-sm mb-1"><span class="inline-flex w-6 h-6 rounded-lg bg-rose-600 text-white text-xs items-center justify-center mr-2">6</span>Dokumen Pendukung</h3>
    <p class="text-[11px] text-slate-400 mt-1">PDF/DOC/JPG, maks 10 MB per berkas. Boleh dilengkapi menyusul.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
      @foreach (\App\Models\DokumenAplikasi::JENIS as $jenis => $label)
      @php $field = 'dok_'.$jenis; $ada = $dokumen->get($jenis); @endphp
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="{{ $field }}">{{ $label }}</label>
        <input id="{{ $field }}" name="{{ $field }}" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        @if ($ada)
        <div class="mt-1.5 flex items-center gap-2 text-[11px]">
          <i class="fa-solid fa-file-lines text-blue-600"></i>
          <a href="{{ route('dokumen.lihat', $ada) }}" target="_blank" rel="noopener" class="font-medium text-blue-700 hover:underline truncate" title="Buka di tab baru">{{ $ada->nama_asli }}</a>
          <a href="{{ route('dokumen.unduh', $ada) }}" class="text-slate-400 hover:text-blue-700 shrink-0" title="Unduh"><i class="fa-solid fa-download"></i></a>
          <label class="ml-auto inline-flex items-center gap-1 text-slate-500 cursor-pointer shrink-0">
            <input type="checkbox" name="hapus_dok_{{ $jenis }}" value="1" {{ old('hapus_dok_'.$jenis) ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 w-3.5 h-3.5">Hapus
          </label>
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>

  <div class="flex flex-col sm:flex-row gap-2">
    <a href="{{ route('aplikasi.milikku') }}" class="sm:w-40 py-3 rounded-xl text-sm font-semibold text-center text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition">Batal</a>
    <button class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 transition" type="submit">
      <i class="fa-solid {{ $submitIcon ?? 'fa-paper-plane' }} mr-1.5"></i>{{ $submitLabel ?? 'Simpan' }}
    </button>
  </div>
</form>
