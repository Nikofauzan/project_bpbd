<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">

        <!-- Tombol untuk Buka Sidebar (HANYA MUNCUL DI HP) -->
        <button class="btn btn-warning d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile" aria-controls="sidebarMobile">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Bagian Kiri Navbar: Logo dan Nama Aplikasi -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            {{-- Menggunakan asset() untuk memanggil gambar dari folder public --}}
            {{-- MENAMBAHKAN INLINE STYLE UNTUK MENGATUR UKURAN LOGO --}}
            <img src="{{ asset('images/logo_kabupaten_bandung.png') }}" alt="Logo Kabupaten Bandung" style="height: 40px; width: auto; margin-right: 8px;">
            <img src="{{ asset('images/logo_bpbd.png') }}" alt="Logo BPBD" style="height: 40px; width: auto; margin-right: 8px;">
            <img src="{{ asset('images/logo_bedas.png') }}" alt="Logo Bedas" style="height: 40px; width: auto;">
            <span class="ms-2 fw-bold d-none d-sm-inline">SIG Bencana Kabupaten Bandung</span>
        </a>

        <!-- Bagian Kanan Navbar (Bisa diisi nanti) -->
        <div class="ms-auto">
            {{-- Kosong untuk saat ini, bisa diisi notifikasi atau search bar nanti --}}
        </div>

    </div>
</nav>
