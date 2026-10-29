<!DOCTYPE html><html lang="id" style=""><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Login - SIPAK Kabupaten Barito Kuala</title>
<!-- Tailwind CSS CDN with forms and container queries plugins -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Font Awesome Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">
<!-- Tailwind Configuration -->
<script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          colors: {
            sipak: {
              50: '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#0f295e',
              deep: '#0a1d42',
            }
          }
        }
      }
    }
  </script>
<!-- Custom CSS Styles -->
<style data-purpose="custom-layout">
    body {
      font-family: 'Inter', sans-serif;
    }
    .hero-pattern {
      background-color: #3b82f6;
      background-image:
        radial-gradient(at 0% 0%, rgba(30, 64, 175, 0.35) 0px, transparent 50%),
        radial-gradient(at 100% 0%, rgba(191, 219, 254, 0.55) 0px, transparent 55%),
        linear-gradient(135deg, #0b224d 0%, #1e40af 45%, #3b82f6 100%);
    }
    .glass-effect {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.16);
    }
    /* Background foto Diskominfo sisi kiri: buram + fade kiri -> kanan */
    .diskominfo-fade {
      background-image: url("{{ asset('image/diskominfo.png') }}");
      background-size: cover;
      background-position: 20% center;
      background-repeat: no-repeat;
      /* blur lembut agar kartu login lebih menonjol */
      filter: blur(6px) saturate(1.05);
      transform: scale(1.1); /* cegah tepi blur terlihat */
      opacity: 0.6; /* kiri lebih jelas */
      /* fade full-width: kiri solid, kanan hilang total tanpa garis potong */
      -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,1) 0%, rgba(0,0,0,0.9) 28%, rgba(0,0,0,0.45) 52%, rgba(0,0,0,0.12) 72%, rgba(0,0,0,0) 92%);
      mask-image: linear-gradient(to right, rgba(0,0,0,1) 0%, rgba(0,0,0,0.9) 28%, rgba(0,0,0,0.45) 52%, rgba(0,0,0,0.12) 72%, rgba(0,0,0,0) 92%);
    }
    /* Lapisan blend agar foto menyatu mulus ke warna hero-pattern */
    .diskominfo-blend {
      background: linear-gradient(to right, rgba(7,25,59,0.05) 0%, rgba(30,64,175,0.25) 48%, rgba(59,130,246,0.85) 78%, #3b82f6 100%);
    }
    /* Texture pola sisi kanan: jelas di kanan, fade ke kiri */
    .pola-fade {
      background-image: url("{{ asset('image/pola.png') }}");
      background-size: 150px auto;
      background-repeat: repeat;
      background-position: right center;
      opacity: 0.55; /* kanan jelas */
      filter: blur(3px);
      transform: scale(1.08); /* cegah tepi blur terlihat */
      /* fade kebalikan foto kiri: kanan solid, kiri hilang total */
      -webkit-mask-image: linear-gradient(to left, rgba(0,0,0,1) 0%, rgba(0,0,0,0.85) 30%, rgba(0,0,0,0.35) 60%, rgba(0,0,0,0) 85%);
      mask-image: linear-gradient(to left, rgba(0,0,0,1) 0%, rgba(0,0,0,0.85) 30%, rgba(0,0,0,0.35) 60%, rgba(0,0,0,0) 85%);
    }
    /* Rotator info kiri: tumpuk slide dalam 1 grid, fade bergantian */
    .rotator-slide {
      grid-area: 1 / 1;
      opacity: 0;
      transform: translateY(10px);
      transition: opacity 0.6s ease, transform 0.6s ease;
      pointer-events: none;
    }
    .rotator-slide.active {
      opacity: 1;
      transform: none;
      pointer-events: auto;
    }
    /* Gradasi panel kiri kartu: warna bergerak halus (idle) */
    .animated-gradient {
      background: linear-gradient(125deg, #0a1d42 0%, #1e40af 30%, #3b82f6 55%, #1e40af 80%, #0a1d42 100%);
      background-size: 300% 300%;
      animation: gradient-shift 16s ease-in-out infinite;
    }
    @keyframes gradient-shift {
      0%   { background-position: 0% 50%; }
      50%  { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    @media (prefers-reduced-motion: reduce) {
      .animated-gradient { animation: none; }
    }
  </style>
</head>
<body class="min-h-screen bg-[#3b82f6] text-slate-800 antialiased selection:bg-blue-600 selection:text-white flex flex-col justify-between">
<!-- BEGIN: MainContainer -->
<div class="relative min-h-screen w-full flex flex-col justify-between overflow-x-hidden hero-pattern">
<!-- Background Foto Diskominfo (kiri, fade ke kanan) + Pola (kanan, fade ke kiri) -->
<div class="pointer-events-none absolute inset-0 overflow-hidden">
<div class="absolute inset-0 w-full diskominfo-fade"></div>
<div class="absolute inset-0 w-full diskominfo-blend"></div>
<div class="absolute inset-0 w-full pola-fade"></div>
</div>
<!-- Decorative Vector Shapes and Islamic Geometric Motif -->
<div class="pointer-events-none absolute inset-0 overflow-hidden opacity-20">
<svg class="absolute -top-24 -left-24 w-96 h-96 text-white" fill="none" viewBox="0 0 400 400">
<circle cx="200" cy="200" r="180" stroke="currentColor" stroke-dasharray="10 15" stroke-width="2"></circle>
<circle cx="200" cy="200" r="140" stroke="currentColor" stroke-width="1.5"></circle>
<path d="M200 20 L200 380 M20 200 L380 200" stroke="currentColor" stroke-width="1"></path>
</svg>
<svg class="absolute -bottom-32 left-1/3 w-[600px] h-[600px] text-blue-300" fill="none" viewBox="0 0 600 600">
<circle cx="300" cy="300" r="260" stroke="currentColor" stroke-dasharray="8 8" stroke-width="1.5"></circle>
<polygon points="300,50 550,300 300,550 50,300" stroke="currentColor" stroke-width="1.5"></polygon>
</svg>
</div>
<!-- BEGIN: TopBar -->
<header class="w-full relative z-20 px-6 py-5 md:px-12 flex items-center justify-between">


</header>
<!-- END: TopBar -->
<!-- BEGIN: MainContent -->
<main class="w-full relative z-10 flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-12 py-8">
<div class="w-full max-w-5xl">
<!-- Kartu utama: kiri gradasi beranimasi, kanan form login -->
<div class="grid grid-cols-1 lg:grid-cols-12 rounded-[28px] overflow-hidden shadow-2xl border border-white/50 bg-white">
<!-- KIRI: gradasi warna bergerak (idle) + konten brand & rotator -->
<div class="lg:col-span-7 order-2 lg:order-1 relative border border-white/50 animated-gradient p-7 sm:p-9 rounded-[28px] text-white flex flex-col justify-between gap-7">
<!-- Badge -->
<span class="inline-flex items-center gap-2 self-start rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-[11px] font-semibold tracking-wide backdrop-blur">
<i class="fa-solid fa-shield-halved text-amber-300"></i>
<span>SPBE • Kabupaten Barito Kuala</span>
</span>
<!-- Hero Headline -->
<div class="space-y-3" style="text-shadow: 0 2px 14px rgba(0,0,0,0.4);">
<p class="text-2xl sm:text-[2rem] font-bold text-white max-w-md leading-snug">
              Sistem Informasi Pendataan Aplikasi <span class="font-semibold">Kabupaten Barito Kuala</span>
</p>
<p class="text-sm text-blue-100/90 max-w-sm leading-relaxed">
              Selamat datang kembali. Masuk untuk mengelola data aplikasi, dokumen, dan evaluasi SPBE.
            </p>
</div>
<!-- Rotator: 1 kartu kaca kecil, isi berganti otomatis -->
<div class="relative max-w-md rounded-2xl glass-effect p-5 shadow-lg" id="sipak-rotator">
<div class="grid">
<!-- Slide 1: Deskripsi -->
<div class="rotator-slide active" data-slide="0">
<p class="text-xs font-semibold uppercase tracking-wider text-sky-300 mb-1.5"><i class="fa-solid fa-layer-group mr-1.5"></i>Inventarisasi Terpadu</p>
<p class="text-sm leading-relaxed text-slate-100">Platform terpadu inventarisasi, evaluasi kematangan, dan pengawasan siklus hidup seluruh aplikasi pemerintah daerah berbasis SPBE.</p>
</div>
<!-- Slide 2: Statistik -->
<div class="rotator-slide" data-slide="1">
<p class="text-xs font-semibold uppercase tracking-wider text-sky-300 mb-2.5"><i class="fa-solid fa-chart-simple mr-1.5"></i>Cakupan Pendataan</p>
<div class="grid grid-cols-3 gap-2.5">
<div class="rounded-xl bg-white/10 border border-white/15 p-2.5 backdrop-blur-sm">
<div class="w-7 h-7 rounded-lg bg-sky-400/20 text-sky-300 grid place-items-center mb-2"><i class="fa-solid fa-layer-group text-[11px]"></i></div>
<span class="block text-xl font-extrabold text-white leading-none">{{ number_format($totalAplikasi) }}</span>
<span class="mt-1 block text-[10px] text-blue-100/80 font-medium leading-tight">Aplikasi Terdata</span>
</div>
<div class="rounded-xl bg-white/10 border border-white/15 p-2.5 backdrop-blur-sm">
<div class="w-7 h-7 rounded-lg bg-emerald-400/20 text-emerald-300 grid place-items-center mb-2"><i class="fa-solid fa-building-columns text-[11px]"></i></div>
<span class="block text-xl font-extrabold text-white leading-none">{{ number_format($totalSkpd) }}</span>
<span class="mt-1 block text-[10px] text-blue-100/80 font-medium leading-tight">Perangkat Daerah</span>
</div>
<div class="rounded-xl bg-white/10 border border-white/15 p-2.5 backdrop-blur-sm">
<div class="w-7 h-7 rounded-lg bg-amber-400/20 text-amber-300 grid place-items-center mb-2"><i class="fa-solid fa-circle-check text-[11px]"></i></div>
<span class="block text-xl font-extrabold text-white leading-none">{{ number_format($terverifikasi) }}</span>
<span class="mt-1 block text-[10px] text-blue-100/80 font-medium leading-tight">Aplikasi Berjalan</span>
</div>
</div>
</div>
<!-- Slide 3: SPBE -->
<div class="rotator-slide" data-slide="2">
<p class="text-xs font-semibold uppercase tracking-wider text-sky-300 mb-1.5"><i class="fa-solid fa-shield-halved mr-1.5"></i>Pengawasan SPBE</p>
<p class="text-sm leading-relaxed text-slate-100">Setiap aplikasi tercatat, dinilai kematangannya, dan dipantau siklus hidupnya oleh Diskominfo Barito Kuala.</p>
</div>
</div>
<!-- Dots indikator -->
<div class="flex items-center gap-1.5 mt-3.5">
<button aria-label="Slide 1" class="rotator-dot h-1.5 w-6 rounded-full bg-white transition-all duration-300" data-goto="0" type="button"></button>
<button aria-label="Slide 2" class="rotator-dot h-1.5 w-1.5 rounded-full bg-white/40 transition-all duration-300" data-goto="1" type="button"></button>
<button aria-label="Slide 3" class="rotator-dot h-1.5 w-1.5 rounded-full bg-white/40 transition-all duration-300" data-goto="2" type="button"></button>
<span class="ml-auto text-[10px] text-blue-200/70 font-medium">otomatis berganti • arahkan kursor untuk jeda</span>
</div>
</div>
</div>
<!-- KANAN: form login (putih) -->
<div class="lg:col-span-5 order-1 lg:order-2 bg-white p-6 sm:p-8 flex flex-col justify-center">
<!-- Brand Badge & Icon on Card Top -->
<div class="flex flex-col items-center text-center pb-6 border-b border-slate-100">
<!-- Barito Kuala / SIPAK Emblem Badge -->
<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-900 to-blue-700 flex items-center justify-center shadow-lg shadow-blue-900/25 mb-3.5 transform transition-transform hover:scale-105 duration-300">
<i class="fa-solid fa-shield-halved text-2xl text-amber-400"></i>
</div>
<!-- Main Title and Subtitle -->
<h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">SIPAK</h3>

</div>
<!-- Login Form -->
<form action="{{ route('login.process') }}" class="mt-6 space-y-4" id="sipak-login-form" method="POST">
@csrf
<!-- Role / Access Switch Badge (Admin vs SKPD) -->

@if ($errors->any())
<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">
{{ $errors->first() }}
</div>
@endif

<!-- Input: Username / NIP -->
<div>
<label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5" for="username">
                  Username / NIP
                </label>
<div class="relative rounded-xl shadow-sm">
<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
<i class="fa-regular fa-user text-sm"></i>
</div>
<input autocomplete="username" class="block w-full rounded-xl border-slate-300 pl-10 pr-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition duration-150" id="username" name="username" placeholder="Masukkan username atau email" required="" type="text" value="{{ old('username') }}">
</div>
</div>
<!-- Input: Password with toggle button -->
<div>
<label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5" for="password">
                  Password
                </label>
<div class="relative rounded-xl shadow-sm">
<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
<i class="fa-solid fa-lock text-sm"></i>
</div>
<input autocomplete="current-password" class="block w-full rounded-xl border-slate-300 pl-10 pr-11 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition duration-150" id="password" name="password" placeholder="Masukkan kata sandi" required="" type="password">
<!-- Toggle Visibility Button -->
<button aria-label="Tampilkan atau sembunyikan password" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition focus:outline-none" id="togglePassword" type="button">
<i class="fa-regular fa-eye text-sm" id="passwordEyeIcon"></i>
</button>
</div>
</div>
<!-- Options: Remember Me & Forgot Password -->
<div class="flex items-center justify-between text-xs pt-1">
<label class="inline-flex items-center cursor-pointer select-none">
<input class="rounded border-slate-300 text-blue-700 shadow-sm focus:border-blue-700 focus:ring-blue-700/20 w-4 h-4 cursor-pointer" id="remember_me" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
<span class="ml-2 text-slate-600 font-medium">Ingat Saya</span>
</label>
<a class="font-semibold text-blue-700 hover:text-blue-800 hover:underline transition" href="#forgot-password-modal" id="forgotPasswordLink">
                  Lupa Password?
                </a>
</div>
<!-- Submit Button (Primary Action) -->
<div class="pt-2">
<button class="w-full inline-flex items-center justify-center py-3 px-4 rounded-xl text-sm font-semibold tracking-wide text-white bg-blue-700 hover:bg-blue-800 active:bg-blue-900 shadow-md shadow-blue-700/30 transition-all duration-200 transform active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" id="submitBtn" type="submit">
<span class="">Masuk</span>
<i class="fa-solid fa-arrow-right-to-bracket ml-2 text-xs"></i>
</button>
</div>
<!-- Helpdesk / Contact Support Notice -->
<div class="mt-4 pt-4 border-t border-slate-100 text-center">

</div>
</form>
<!-- Card Bottom Official Tagline -->
<div class="mt-6 pt-3 text-center border-t border-slate-100/70">
<p class="text-[11px] font-medium text-slate-400">
                Pemerintah Kabupaten Barito Kuala
              </p>
<p class="text-[11px] text-slate-400">
                Dinas Komunikasi dan Informatika
              </p>
</div>
</div>
</div>
</div>
</main>
<!-- END: MainContent -->
<!-- BEGIN: Footer -->
<footer class="w-full relative z-20 px-6 py-4 text-center border-t border-white/20 bg-blue-900/30 backdrop-blur-md">
<div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between text-xs text-blue-50/90 gap-2">
<div class="flex items-center space-x-2">
<span class="">© 2025 Pemerintah Kabupaten Barito Kuala • Diskominfo</span>
</div>
<div class="flex items-center space-x-4 text-xs text-blue-300/80">
<span class="inline-flex items-center gap-1">
<i class="fa-solid fa-shield-cat text-emerald-400"></i> SPBE Terverifikasi
          </span>


</div>
</div>
</footer>
<!-- END: Footer -->
</div>
<!-- END: MainContainer -->
<!-- JavaScript for Interactions -->
<script data-purpose="login-interactions">
    document.addEventListener('DOMContentLoaded', () => {
      // Toggle Password Visibility
      const togglePasswordBtn = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const passwordEyeIcon = document.getElementById('passwordEyeIcon');

      if (togglePasswordBtn && passwordInput && passwordEyeIcon) {
        togglePasswordBtn.addEventListener('click', () => {
          const isPassword = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
          
          if (isPassword) {
            passwordEyeIcon.classList.remove('fa-eye');
            passwordEyeIcon.classList.add('fa-eye-slash');
          } else {
            passwordEyeIcon.classList.remove('fa-eye-slash');
            passwordEyeIcon.classList.add('fa-eye');
          }
        });
      }

      // Forgot Password Modal / Alert prompt handler
      const forgotPasswordLink = document.getElementById('forgotPasswordLink');
      if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', (e) => {
          e.preventDefault();
          alert('Untuk permohonan reset kata sandi SIPAK, silakan hubungi Administrator SPBE Bidang Penyelenggaraan E-Government Diskominfo Barito Kuala melalui tiket Helpdesk atau WhatsApp resmi dinas.');
        });
      }

      // Cegah double-submit: tampilkan status loading, tetap submit form
      const loginForm = document.getElementById('sipak-login-form');
      if (loginForm) {
        loginForm.addEventListener('submit', () => {
          const submitBtn = document.getElementById('submitBtn');
          if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memvalidasi...';
            submitBtn.setAttribute('disabled', 'true');
          }
        });
      }

      // Rotator info kiri: ganti slide otomatis tiap 4,5 detik
      const rotator = document.getElementById('sipak-rotator');
      if (rotator) {
        const slides = rotator.querySelectorAll('.rotator-slide');
        const dots = rotator.querySelectorAll('.rotator-dot');
        let current = 0;
        let timer = null;

        const showSlide = (index) => {
          current = (index + slides.length) % slides.length;
          slides.forEach((s, i) => s.classList.toggle('active', i === current));
          dots.forEach((d, i) => {
            const isActive = i === current;
            d.className = isActive
              ? 'rotator-dot h-1.5 w-6 rounded-full bg-white transition-all duration-300'
              : 'rotator-dot h-1.5 w-1.5 rounded-full bg-white/40 transition-all duration-300';
          });
        };

        const stopAuto = () => {
          if (timer) clearInterval(timer);
          timer = null;
        };
        const startAuto = () => {
          stopAuto();
          timer = setInterval(() => showSlide(current + 1), 4500);
        };

        dots.forEach((d) => {
          d.addEventListener('click', () => {
            showSlide(parseInt(d.dataset.goto, 10));
            startAuto();
          });
        });

        rotator.addEventListener('mouseenter', stopAuto);
        rotator.addEventListener('mouseleave', startAuto);

        showSlide(0);
        startAuto();
      }
    });
  </script>


</body></html>