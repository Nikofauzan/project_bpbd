<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor Bencana</title>
    <style>
        /* Bikin html dan body menuhin layar */
        html, body {
            height: 100%;
            margin: 0;
            font-family: sans-serif;
            /* Ganti background solid dengan gradient oranye yang cakep */
            background: linear-gradient(135deg, #ffc977, #f37321);
            background-size: cover; /* Pastikan gradient menutupi seluruh layar */
        }
        /* Bikin body jadi flex container buat gampang nye-tengahin */
        body {
            display: flex;
            justify-content: center; /* Tengahin secara horizontal */
            align-items: center;    /* Tengahin secara vertikal */
            text-align: center;
        }
        /* ===== Tombol Kembali ===== */
        .back-button {
            position: absolute;
            top: 25px;
            left: 25px;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            backdrop-filter: blur(5px); /* Efek Kaca */
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        .content-wrapper {
            /* Kontainer untuk semua isi */
        }
        .logo-container {
            margin-bottom: 2rem; /* Jarak antara logo dan teks */
        }
        .logo {
            height: 60px; /* Ukuran logo, dijamin tidak segede gaban */
            width: auto;
            margin: 0 10px; /* Jarak antar logo */
            /* filter drop-shadow biar logo lebih 'pop-up' */
            filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.2));
        }
        h2 {
            /* Ganti warna teks jadi putih biar kontras dan mudah dibaca */
            color: #ffffff; 
            font-weight: 600; /* Bikin font-nya lebih tebal biar jelas */
            font-size: 2.2rem; /* Gedein dikit fontnya */
            /* Kasih bayangan biar teksnya 'nongol' dari background */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); 
        }
    </style>
</head>
<body>

    <!-- Tombol Kembali -->
    <a href="javascript:history.back()" class="back-button">&larr; Kembali</a>

    <div class="content-wrapper">
        <div class="logo-container">
            <!-- Asumsi gambar ada di folder 'images' di dalam 'public' -->
            <img src="images/logo_kabupaten_bandung.png" alt="Logo Kabupaten Bandung" class="logo">
            <img src="images/logo_bpbd.png" alt="Logo BPBD" class="logo">
            <img src="images/logo_bedas.png" alt="Logo Bedas" class="logo">
        </div>
        <h2>
            Halaman Ini Sedang Dalam Proses Pengembangan 🚧
        </h2>
    </div>
</body>
</html>
