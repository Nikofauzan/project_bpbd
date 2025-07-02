<!-- Navbar Mobile (hanya muncul di layar kecil) -->
<!-- Warna tombol disesuaikan menjadi oranye -->

<div class="offcanvas offcanvas-start bg-white text-dark" tabindex="-1" id="sidebarMobile"
    aria-labelledby="sidebarMobileLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMobileLabel">
            <i class="fas fa-compass me-2 text-orange"></i> Menu Navigasi
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        @include('layouts.partials.sidebar-menu')
    </div>
</div>

<div class="d-none d-lg-flex flex-column bg-white text-dark p-3 vh-100 position-fixed shadow-sm" style="width: 280px;">
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 text-dark text-decoration-none">
        <i class="fas fa-map-marked-alt fa-2x me-2 text-orange"></i>
        <span class="fs-4" style="font-weight: 700;">SIG Kab Bandung</span>
    </a>
    <hr>

    @include('layouts.partials.sidebar-menu')

    <hr class="mt-auto">
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle fa-fw me-2"></i>
            <strong>Admin</strong>
        </a>
        <ul class="dropdown-menu shadow">
            <li><a class="dropdown-item" href="#">Profil</a></li>
            <li><a class="dropdown-item" href="#">Pengaturan</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#">Keluar</a></li>
        </ul>
    </div>
</div>

{{-- Helper untuk memberi margin pada konten utama agar tidak tertutup sidebar desktop --}}
<div class="d-none d-lg-block" style="min-width: 280px;"></div>