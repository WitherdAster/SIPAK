<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div class="sm:col-span-2">
    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="perangkat_daerah">Instansi (SKPD) *</label>
    <input id="perangkat_daerah" name="perangkat_daerah" list="daftar-skpd" value="{{ old('perangkat_daerah', $skpdTerpilih->nama ?? $akun->perangkatDaerah->nama ?? '') }}" required class="w-full rounded-xl border-slate-300 text-sm" placeholder="Ketik nama instansi..." autocomplete="off">
    <datalist id="daftar-skpd">
      @if (isset($akun) && $akun->perangkatDaerah)
      <option value="{{ $akun->perangkatDaerah->nama }}"></option>
      @endif
      @foreach ($skpdTersedia as $skpd)
      <option value="{{ $skpd->nama }}"></option>
      @endforeach
    </datalist>
    <p class="text-[11px] text-slate-400 mt-1">Ketik manual atau pilih saran. Instansi baru otomatis dibuat. Nama akun otomatis mengikuti instansi. 1 instansi = 1 akun.</p>
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="username">Username *</label>
    <input id="username" name="username" value="{{ old('username', $akun->username ?? '') }}" required class="w-full rounded-xl border-slate-300 text-sm font-mono" placeholder="cth: dinkes">
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="email">Email *</label>
    <input id="email" name="email" type="email" value="{{ old('email', $akun->email ?? '') }}" required class="w-full rounded-xl border-slate-300 text-sm" placeholder="cth: dinkes@sipak.batolakab.go.id">
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password">Password {{ isset($akun) ? '(isi hanya jika diganti)' : '*' }}</label>
    <input id="password" name="password" type="password" {{ isset($akun) ? '' : 'required' }} minlength="8" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Minimal 8 karakter" autocomplete="new-password">
  </div>
  <div>
    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password_confirmation">Konfirmasi Password {{ isset($akun) ? '' : '*' }}</label>
    <input id="password_confirmation" name="password_confirmation" type="password" {{ isset($akun) ? '' : 'required' }} minlength="8" class="w-full rounded-xl border-slate-300 text-sm" autocomplete="new-password">
  </div>
</div>
