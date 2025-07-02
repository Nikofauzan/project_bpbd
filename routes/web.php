<?php

use Illuminate\Support\Facades\Route;

// CONTROLLER UTAMA
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetaTerpaduController;
use App\Http\Controllers\HistoriBencanaController;

// CONTROLLER PETA SPESIALIS (LAMA)
use App\Http\Controllers\BahayaLongsorController;
use App\Http\Controllers\RisikoGunungApiController; // <--- PASTIKAN INI ADA!
use App\Http\Controllers\RisikoGempaController;

// CONTROLLER PETA KOMPREHENSIF (BARU)
use App\Http\Controllers\PetaIkmController;
use App\Http\Controllers\PetaRisikoController;
use App\Http\Controllers\PetaBahayaController;
use App\Http\Controllers\PetaKapasitasController;
use App\Http\Controllers\PetaKerentananController;


// ===== ROUTING MENU UTAMA =====
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/peta-terpadu', [PetaTerpaduController::class, 'index'])->name('peta.terpadu');
Route::get('/lapor', function () { return view('laporbencana'); })->name('lapor');

// ===== ROUTING PETA SPESIALIS (LAMA) =====
Route::get('/longsor', function () { return view('peta_bencana.longsor'); })->name('longsor');
Route::get('/gunungapi', function () { return view('peta_bencana.gunung_api'); })->name('gunungapi');
Route::get('/gempa', function () { return view('peta_bencana.gempa'); })->name('gempa');

// ===== ROUTING UNTUK AMBIL DATA =====
// Data Histori untuk Dashboard Analitis
Route::get('/data/histori-bencana', [HistoriBencanaController::class, 'data'])->name('data.histori');

// Data Peta Spesialis (Lama)
Route::get('/peta/longsor/data', [BahayaLongsorController::class, 'longsorData'])->name('peta.longsor.data');
Route::get('/peta/gunung-api/data', [RisikoGunungApiController::class, 'gunungApiData'])->name('peta.gunungapi.data'); // <--- PASTIKAN BARIS INI BENAR
Route::get('/peta/gempa/data', [RisikoGempaController::class, 'gempaBumiData'])->name('peta.gempabumi.data');

// Data Peta Komprehensif (Baru)
Route::get('/data/risiko', [PetaRisikoController::class, 'data'])->name('data.risiko');
// Route::get('/data/bahaya', [PetaBahayaController::class, 'data'])->name('data.bahaya');
// Route::get('/data/kerentanan', [PetaKerentananController::class, 'data'])->name('data.kerentanan');
// Route::get('/data/kapasitas', [PetaKapasitasController::class, 'data'])->name('data.kapasitas');
// Route::get('/data/ikm', [PetaIkmController::class, 'data'])->name('data.ikm');
