@extends('layouts.app')

@section('title', 'Peta Bahaya Longsor')

@push('styles')
    {{-- Panggil semua CSS yang dibutuhkan --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <style>
        #map-container { position: relative; }
        #map { height: 75vh; border-radius: 10px; background-color: #f0f2f5; }
        
        /* Style untuk Search Bar */
        .leaflet-search-control {
            position: absolute;
            top: 10px;
            left: 50px; /* Ditaro di kiri biar nggak tabrakan sama filter */
            z-index: 1000;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
        }
        .leaflet-search-control input {
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px 0 0 8px;
            width: 250px;
        }
        .leaflet-search-control input:focus {
            outline: none;
        }
        .leaflet-search-control button {
            border: none;
            background-color: #17a2b8; /* Warna disesuaikan dengan tombol info */
            color: white;
            padding: 0.5rem 1rem;
            cursor: pointer;
            border-radius: 0 8px 8px 0;
        }
        .leaflet-search-control button:hover {
            background-color: #138496;
        }

        /* Style untuk Remote Control Filter */
        .leaflet-filter-control { position: absolute; top: 10px; right: 10px; z-index: 1000; background-color: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); max-width: 250px; }
        .leaflet-filter-control-toggle { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.2rem; }
        .leaflet-filter-control-content { padding: 15px; border-top: 1px solid #eee; display: none; }
        .leaflet-filter-control.expanded .leaflet-filter-control-content { display: block; }
        .filter-section h6 { font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 5px; }
        .form-check-label { font-size: 0.9rem; }
    </style>
@endpush

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Peta Interaktif Bahaya Longsor</h1>

    <div id="map-container">
        <div id="map"></div>

        {{-- HTML untuk Search Bar --}}
        <div class="leaflet-search-control" id="search-control">
            <input type="text" id="search-input" placeholder="Cari nama wilayah...">
            <button id="search-button"><i class="fas fa-search"></i></button>
        </div>

        {{-- HTML untuk Remote Control Filter --}}
        <div class="leaflet-filter-control" id="filter-control">
            <div class="leaflet-filter-control-toggle" id="filter-toggle" title="Buka/Tutup Filter"><i class="fas fa-filter"></i></div>
            <div class="leaflet-filter-control-content">
                <div class="filter-section">
                    <h6>Filter Atribut</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Tinggi" id="filter-tinggi" checked>
                        <label class="form-check-label" for="filter-tinggi"><i class="fas fa-circle me-2" style="color: #e60000;"></i> Risiko Tinggi</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Sedang" id="filter-sedang" checked>
                        <label class="form-check-label" for="filter-sedang"><i class="fas fa-circle me-2" style="color: #ff9900;"></i> Risiko Sedang</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Rendah" id="filter-rendah" checked>
                        <label class="form-check-label" for="filter-rendah"><i class="fas fa-circle me-2" style="color: #33cc33;"></i> Risiko Rendah</label>
                    </div>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Aman" id="filter-aman" checked>
                        <label class="form-check-label" for="filter-aman"><i class="fas fa-circle me-2" style="color: #cccccc;"></i> Tidak Ada Data</label>
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
            var map = L.map('map').setView([-7.1, 107.65], 10);
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
            
            const layerIndex = {};

            function getRisikoLevel(str) {
                if (typeof str !== 'string' || str.trim() === '') return 'Aman';
                const lowerStr = str.toLowerCase().trim();
                if (lowerStr.includes('tinggi')) return 'Tinggi';
                if (lowerStr.includes('sedang')) return 'Sedang';
                if (lowerStr.includes('rendah')) return 'Rendah';
                return 'Aman';
            }

            fetch('{{ route('peta.longsor.data') }}')
                .then(response => {
                    if (!response.ok) throw new Error('Gagal memuat data dari server');
                    return response.json();
                })
                .then(data => {
                    L.geoJSON(data, {
                        onEachFeature: function (feature, layer) {
                            let risiko = getRisikoLevel(feature.properties.REMARK);
                            let nama = feature.properties.NAMAOBJ || 'N/A';
                            
                            if (ember[risiko]) {
                                let popupContent = `<b>Wilayah:</b> ${nama}<br><b>Status Risiko:</b> ${risiko}`;
                                layer.bindPopup(popupContent);
                                ember[risiko].addLayer(layer);

                                if (nama !== 'N/A') {
                                    // Daftarin layer ini ke "Buku Telepon"
                                    layerIndex[nama.toLowerCase()] = layer;
                                }
                            }
                        },
                        style: function (feature) {
                            let risiko = getRisikoLevel(feature.properties.REMARK);
                            let warna = "#cccccc";
                            if (risiko === "Tinggi") warna = "#e60000";
                            else if (risiko === "Sedang") warna = "#ff9900";
                            else if (risiko === "Rendah") warna = "#33cc33";
                            return { color: "#000", weight: 1, fillColor: warna, fillOpacity: 0.7 };
                        }
                    });

                    updatePeta();
                })
                .catch(err => console.error('Gagal load GeoJSON:', err));
            
            // Logika untuk Search Bar
            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');

            function performSearch() {
                const query = searchInput.value.toLowerCase().trim();
                if (query && layerIndex[query]) {
                    const foundLayer = layerIndex[query];
                    map.fitBounds(foundLayer.getBounds(), { maxZoom: 14 });
                    foundLayer.openPopup();
                } else {
                    // [PERBAIKAN] Ganti pesan notifikasi
                    alert('Wilayah tidak terdampak atau nama tidak ditemukan.');
                }
            }

            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });


            // Logika filter
            function updatePeta() {
                document.querySelectorAll('.form-check-input').forEach(checkbox => {
                    const risikoValue = checkbox.value;
                    const targetEmber = ember[risikoValue];
                    if (targetEmber) {
                        if (checkbox.checked) {
                            if (!map.hasLayer(targetEmber)) map.addLayer(targetEmber);
                        } else {
                            if (map.hasLayer(targetEmber)) map.removeLayer(targetEmber);
                        }
                    }
                });
            }
            
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.addEventListener('change', updatePeta);
            });

            const filterControl = document.getElementById('filter-control');
            const filterToggle = document.getElementById('filter-toggle');
            filterToggle.addEventListener('click', function () {
                filterControl.classList.toggle('expanded');
            });
        });
    </script>
@endpush
