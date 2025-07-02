@extends('layouts.app')

@section('title', 'Dashboard Analitis Bencana')

@push('styles')
    {{-- Panggil CSS Leaflet & Font Awesome --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <style>
        /* Style-style ini bisa kamu pindahkan ke file CSS utamamu nanti */
        .dashboard-card {
            background-color: #fff;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            height: 100%;
        }
        .card-header-custom {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #main-map {
            width: 100%;
            height: 500px;
            background-color: #e9ecef;
            border-radius: 0.5rem;
        }
        .table-responsive {
            max-height: 400px;
        }
        /* [BARU] Biar dropdown filter di kartu nggak terlalu lebar */
        .card-header-custom .form-select {
            max-width: 200px;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- BARIS 1: JUDUL DAN FILTER UTAMA --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0 text-gray-800">Dashboard Analitis Bencana</h4>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-light border btn-sm">Tahun: <strong>2025</strong> <i class="fas fa-caret-down ms-2"></i></button>
        </div>
    </div>

    {{-- BARIS 2: GRAFIK-GRAFIK UTAMA --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="dashboard-card">
                {{-- [UPGRADE] Tambah dropdown filter di sini --}}
                <div class="card-header-custom">
                    <span>Frekuensi Kejadian Bencana</span>
                    <select id="filterJenisBencana" class="form-select form-select-sm">
                        {{-- Opsi akan diisi oleh JavaScript --}}
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="chartFrekuensiBencana"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dashboard-card">
                <div class="card-header-custom">Tren Kejadian per Bulan</div>
                <div class="card-body">
                    <canvas id="chartTrenBulanan"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 3: PETA UTAMA DAN TABEL HISTORI --}}
    <div class="row">
        <div class="col-lg-7">
            <div class="dashboard-card">
                 <div class="card-header-custom">Peta Sebaran Bencana (Outline Wilayah)</div>
                 <div class="card-body">
                     <div id="main-map" style="height: 450px;"></div>
                 </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="dashboard-card">
                <div class="card-header-custom">Tabel Histori Kejadian Bencana 2025</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Lokasi</th>
                                    {{-- [PERBAIKAN] Ganti header tabel --}}
                                    <th class="text-end">Kerugian (Rp)</th>
                                </tr>
                            </thead>
                            <tbody id="tabelHistoriBody">
                                <tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

@push('scripts')
{{-- Panggil Leaflet & Chart.js --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Inisialisasi Peta Kosongan
    const map = L.map('main-map').setView([-7.1, 107.65], 9);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO'
    }).addTo(map);

    // Jurus Peta Kosongan (Outline Wilayah)
    fetch("{{ route('peta.longsor.data') }}")
        .then(response => response.ok ? response.json() : Promise.reject('Gagal mengambil data outline'))
        .then(data => {
            const outlineLayer = L.geoJSON(data, {
                style: { color: "#4e73df", weight: 2, opacity: 0.8, fillColor: "#4e73df", fillOpacity: 0.1 }
            }).addTo(map);
            map.fitBounds(outlineLayer.getBounds());
        })
        .catch(err => console.error('Error saat memuat outline peta:', err));

    // [UPGRADE TOTAL] Logika baru untuk chart interaktif
    const filterSelect = document.getElementById('filterJenisBencana');
    const ctxFrekuensi = document.getElementById('chartFrekuensiBencana').getContext('2d');
    let chartFrekuensi; // Variabel untuk menyimpan instance chart
    let dataCache; // Variabel untuk menyimpan data dari server

    function updateChart() {
        if (chartFrekuensi) {
            chartFrekuensi.destroy(); // Hancurkan chart lama sebelum bikin yang baru
        }

        const selectedJenis = filterSelect.value;
        let labels, data, title;

        if (selectedJenis === 'semua') {
            labels = Object.keys(dataCache.frekuensiBencana);
            data = Object.values(dataCache.frekuensiBencana);
            title = 'Frekuensi per Jenis Bencana';
        } else {
            const dataLokasi = dataCache.frekuensiPerLokasi[selectedJenis] || {};
            labels = Object.keys(dataLokasi);
            data = Object.values(dataLokasi);
            title = `Frekuensi ${selectedJenis} per Kecamatan`;
        }

        chartFrekuensi = new Chart(ctxFrekuensi, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Kejadian',
                    data: data,
                    backgroundColor: '#4e73df',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    y: { beginAtZero: true, ticks: { precision: 0 } } // Pastikan sumbu Y tidak ada koma
                },
                plugins: { 
                    legend: { display: false },
                    title: { display: true, text: title }
                }
            }
        });
    }

    fetch("{{ route('data.histori') }}")
        .then(response => response.ok ? response.json() : Promise.reject('Gagal mengambil data histori'))
        .then(data => {
            dataCache = data; // Simpan data ke cache

            // 1. Mengisi Tabel Histori dengan data yang benar
            const tabelBody = document.getElementById('tabelHistoriBody');
            tabelBody.innerHTML = '';
            data.tabelData.forEach(row => {
                const tr = document.createElement('tr');
                // [PERBAIKAN] Ganti 'meninggal' jadi 'kerugian'
                tr.innerHTML = `<td>${row.tanggal}</td><td>${row.jenis}</td><td>${row.lokasi}</td><td class="text-end">${row.kerugian}</td>`;
                tabelBody.appendChild(tr);
            });

            // 2. Mengisi dropdown filter
            filterSelect.innerHTML = '<option value="semua">Semua Bencana</option>';
            for (const jenis in data.frekuensiBencana) {
                const option = document.createElement('option');
                option.value = jenis;
                option.textContent = jenis;
                filterSelect.appendChild(option);
            }

            // 3. Pasang event listener ke dropdown
            filterSelect.addEventListener('change', updateChart);

            // 4. Buat chart awal (Semua Bencana)
            updateChart();

            // 5. Membuat Diagram Tren Bulanan (dengan data yang sudah benar)
            const ctxTren = document.getElementById('chartTrenBulanan').getContext('2d');
            new Chart(ctxTren, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Jumlah Kejadian',
                        data: data.trenBulanan,
                        fill: true,
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderColor: '#1cc88a',
                        tension: 0.3
                    }]
                },
                 options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: false } }
                }
            });
        })
        .catch(err => {
            console.error('Error saat memuat data histori & chart:', err);
            document.getElementById('tabelHistoriBody').innerHTML = `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>`;
        });
});
</script>
@endpush
