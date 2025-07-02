@extends('layouts.app')

@section('title', 'Peta Bahaya Longsor')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        #map-container {
            position: relative;
        }

        /* ===== CSS UNTUK REMOTE CONTROL FILTER ===== */
        .leaflet-filter-control {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            /* Pastikan di atas peta */
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            max-width: 250px;
        }

        .leaflet-filter-control-toggle {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .leaflet-filter-control-content {
            padding: 15px;
            border-top: 1px solid #eee;
            display: none;
            /* Awalnya disembunyikan */
        }

        /* Class 'expanded' akan ditambahkan via JS */
        .leaflet-filter-control.expanded .leaflet-filter-control-content {
            display: block;
        }

        .filter-section {
            margin-bottom: 15px;
        }

        .filter-section h6 {
            font-weight: 700;
            margin-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
        }

        .form-check-label {
            font-size: 0.9rem;
        }
    </style>
@endpush

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Peta Interaktif Bahaya Longsor</h1>

    <div id="map-container">
        <div id="map" style="height: 70vh; border-radius: 10px;"></div>

        <!-- ===== HTML UNTUK REMOTE CONTROL FILTER ===== -->
        <div class="leaflet-filter-control" id="filter-control">
            <div class="leaflet-filter-control-toggle" id="filter-toggle" title="Buka/Tutup Filter">
                <i class="fas fa-filter"></i>
            </div>
            <div class="leaflet-filter-control-content">
                <div class="filter-section">
                    <h6>Filter Atribut</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Tinggi" id="filter-tinggi" checked>
                        <label class="form-check-label" for="filter-tinggi">
                            <i class="fas fa-circle me-2" style="color: #e60000;"></i> Risiko Tinggi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Sedang" id="filter-sedang" checked>
                        <label class="form-check-label" for="filter-sedang">
                            <i class="fas fa-circle me-2" style="color: #ff9900;"></i> Risiko Sedang
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Rendah" id="filter-rendah" checked>
                        <label class="form-check-label" for="filter-rendah">
                            <i class="fas fa-circle me-2" style="color: #33cc33;"></i> Risiko Rendah
                        </label>
                    </div>
                    {{-- ===== TAMBAHAN CEKLIS BARU ===== --}}
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Aman" id="filter-aman" checked>
                        <label class="form-check-label" for="filter-aman">
                            <i class="fas fa-circle me-2" style="color: #cccccc;"></i> Tidak Ada Data
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var map = L.map('map').setView([-6.914744, 107.609810], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const ember = {
                Tinggi: L.layerGroup(),
                Sedang: L.layerGroup(),
                Rendah: L.layerGroup(),
                Aman: L.layerGroup()
            };

            // Ambil data dari Controller
            fetch('{{ route('peta.longsor.data') }}')
                .then(response => response.json())
                .then(data => {
                    // 2. Proses data SEKALI SAJA di awal untuk memilah ke dalam ember
                    L.geoJSON(data, {
                        onEachFeature: function (feature, layer) {
                            let risiko = feature.properties.REMARK;
                            let kategoriUntukEmber;

                            // Tentukan layer ini mau masuk ke ember mana
                            if (risiko === 'Tinggi' || risiko === 'Sedang' || risiko === 'Rendah') {
                                kategoriUntukEmber = risiko;
                            } else {
                                kategoriUntukEmber = 'Aman'; // Jika null, undefined, atau teks lain
                            }

                            // Tambahkan popup juga
                            let nama = feature.properties.NAMAOBJ;
                            let statusTeks = risiko || 'Tidak Ada Data';
                            let linkVideo = "https://www.youtube.com/watch?v=610tjOA3Jss"; // Link mitigasi // Tampilkan 'Tidak Ada Data' jika REMARK kosong
                            let popupContent = `<b>Wilayah:</b> ${nama}<br><b>Status:</b> ${statusTeks} 
                                <br><a href="${linkVideo}" target="_blank">Lihat Video Mitigasi Bencana</a>`;
                            layer.bindPopup(popupContent);

                            // Masukkan layer ke ember yang sesuai
                            ember[kategoriUntukEmber].addLayer(layer);
                        },
                        style: function (feature) {
                            let risiko = feature.properties.REMARK || "Tidak Diketahui";
                            let warna = "#cccccc"; // Warna default abu-abu untuk 'Aman' / 'Tidak Diketahui'
                            if (risiko === "Tinggi") warna = "#e60000";
                            else if (risiko === "Sedang") warna = "#ff9900";
                            else if (risiko === "Rendah") warna = "#33cc33";
                            return { color: "#000", weight: 1, fillColor: warna, fillOpacity: 0.7 };
                        }
                    });

                    // 3. Secara default, tampilkan semua ember di peta
                    Object.values(ember).forEach(e => e.addTo(map));
                })
                .catch(err => console.error('Gagal load GeoJSON:', err));

            // ===== LOGIKA UNTUK REMOTE CONTROL FILTER =====

            // Fungsi untuk update peta berdasarkan ceklis (TIDAK PERLU DIUBAH, sudah pintar)
            function updatePeta() {
                document.querySelectorAll('.form-check-input').forEach(checkbox => {
                    const risikoValue = checkbox.value; // nilainya 'Tinggi', 'Sedang', 'Rendah', 'Aman'
                    const targetEmber = ember[risikoValue];

                    if (targetEmber) {
                        if (checkbox.checked) {
                            if (!map.hasLayer(targetEmber)) {
                                map.addLayer(targetEmber);
                            }
                        } else {
                            if (map.hasLayer(targetEmber)) {
                                map.removeLayer(targetEmber);
                            }
                        }
                    }
                });
            }

            // Pasang "pendengar" ke setiap checkbox
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', updatePeta);
            });

            // Logika untuk buka-tutup (lipat) remote control
            const filterControl = document.getElementById('filter-control');
            const filterToggle = document.getElementById('filter-toggle');
            filterToggle.addEventListener('click', function () {
                filterControl.classList.toggle('expanded');
            });
        });
    </script>
@endpush