<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Peta Bencana') - Sistem Informasi Geografis</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome (untuk ikon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" /

    <!-- Google Fonts (Biar lebih cakep) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc; /* Warna background abu-abu muda */
        }
        .main-content {
            padding-left: 250px; /* Lebar sidebar */
            transition: padding-left 0.3s;
        }
        /* Style untuk layar kecil */
        @media (max-width: 992px) {
            .main-content {
                padding-left: 0;
            }
        }
    </style>

    {{-- Tempat untuk naruh CSS dari halaman lain (misal: halaman peta) --}}
    @stack('styles')
</head>
<body>
    <div class="d-flex" id="wrapper">
        
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Konten Utama -->
        <div id="page-content-wrapper" class="w-100">

            <!-- Navbar Atas -->
            @include('layouts.navbar')

            <!-- Isi Halaman -->
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Tempat untuk naruh JS dari halaman lain (misal: halaman peta) --}}
    @stack('scripts')
</body>
</html>
