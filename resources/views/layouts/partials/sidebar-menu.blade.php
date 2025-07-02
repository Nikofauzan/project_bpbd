{{-- File ini HANYA berisi daftar menu agar bisa dipakai ulang --}}
<ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
        <span class="text-muted"
            style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; padding-left: 1rem;">Main
            Menu</span>
    </li>

    <li class="nav-item mb-1">
        <a href="{{ route('dashboard') }}"
            class="nav-link text-dark sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt fa-fw me-2"></i>
            Dashboard
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('lapor') }}"
            class="nav-link text-dark sidebar-link {{ request()->routeIs('lapor') ? 'active' : '' }}">
            <i class="fas fa-bullhorn fa-fw me-2"></i>
            Lapor Bencana
        </a>
    </li>

    <li class="nav-item mt-3 mb-1">
        <span class="text-muted"
            style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; padding-left: 1rem;">Analisis
            Peta</span>
    </li>

    <li class="nav-item">
        <a href="{{ route('peta.terpadu') }}"
            class="nav-link text-dark sidebar-link {{ request()->routeIs('peta.terpadu') ? 'active' : '' }}">
            <i class="fas fa-map"></i>
            Peta Terpadu KRB
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('longsor') }}"
            class="nav-link text-dark sidebar-link {{ request()->routeIs('longsor') ? 'active' : '' }}">
            <i class="fas fa-mountain fa-fw me-2"></i>
            Bahaya Longsor
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('gunungapi') }}"
            class="nav-link text-dark sidebar-link {{ request()->routeIs('gunungapi') ? 'active' : '' }}">
            <i class="fa-solid fa-volcano"></i>
            Bahaya Gunung Api
        </a>
    </li>

    <li class="nav-item">
        <a href="{{ route('gempa') }}"
            class="nav-link text-dark sidebar-link {{ request()->routeIs('gempabumi') ? 'active' : '' }}">
            <i class="fa-solid fa-house-chimney-crack"></i>
            Bahaya Gempa Bumi
        </a>
    </li>
</ul>