<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetaTerpaduController extends Controller
{
    /**
     * Menampilkan halaman utama Peta Bencana Terpadu.
     *
     * Fungsi ini tidak melakukan apa-apa selain memanggil file view (Blade)
     * yang berisi semua logika untuk peta super kita. Semua keajaiban
     * terjadi di sisi frontend (JavaScript) pada file Blade tersebut.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // [PERBAIKAN FINAL BANGET] Alamatnya disesuaikan dengan struktur folder
        // Folder /layouts/peta_bencana/peta_terpadu.blade.php
        // ditulisnya menjadi 'layouts.peta_bencana.peta_terpadu'
        return view('peta_bencana.peta_terpadu');
    }
}
