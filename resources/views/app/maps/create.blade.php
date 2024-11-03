@extends('app.layouts.app')

@section('title', 'Criar Polígono')

@section('content')
@include('app.maps.partials.breadcrumb', ['currentPage' => 'Criar Polígono'])

<div class="flex flex-col sm:flex-row items-center justify-between">
  <h1 class="text-2xl font-bold my-4 dark:text-zinc-200">Criar Novo Polígono</h1>
</div>

<x-alert />

<form action="{{ route('polygons.store') }}" method="POST" id="polygonForm" class="space-y-4">
    @csrf
    <div class="mb-4">
        <label for="name" class="block text-sm font-medium dark:text-zinc-200">Nome do Polígono</label>
        <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
    </div>

    <div class="mb-4">
        <label for="orcrim_id" class="block text-sm font-medium dark:text-zinc-200">Grupo Crime</label>
        <select name="orcrim_id" id="orcrim_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @foreach($orcrims as $orcrim)
                <option value="{{ $orcrim->id }}">{{ $orcrim->orcrim_name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Hidden input to store polygon coordinates -->
    <input type="hidden" name="coordinates" id="coordinates">

    <!-- Map container -->
    <div id="map" style="height: 600px; width: 100%;" class="rounded-lg border border-gray-300"></div>

    <div class="flex justify-end space-x-2">
        <button type="button" id="clearButton" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
            Limpar Desenho
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
            Salvar Polígono
        </button>
    </div>
</form>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw/dist/leaflet.draw.css" />

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw/dist/leaflet.draw.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    var map = L.map('map').setView([-9.424957, -40.505760], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Initialize draw control
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    var drawControl = new L.Control.Draw({
        draw: {
            marker: false,
            circle: false,
            circlemarker: false,
            rectangle: false,
            polyline: false,
            polygon: {
                allowIntersection: false,
                drawError: {
                    color: '#e1e100',
                    message: '<strong>Erro:</strong> Polígonos não podem se intersectar!'
                },
                shapeOptions: {
                    color: '#3388ff'
                }
            }
        },
        edit: {
            featureGroup: drawnItems,
            remove: true
        }
    });
    map.addControl(drawControl);

    // Handle drawn layers
    map.on(L.Draw.Event.CREATED, function(event) {
        drawnItems.clearLayers();
        var layer = event.layer;
        drawnItems.addLayer(layer);
        
        // Convert coordinates to GeoJSON
        var coordinates = layer.getLatLngs()[0].map(function(latLng) {
            return [latLng.lng, latLng.lat];
        });
        // Close the polygon by repeating the first point
        coordinates.push(coordinates[0]);
        
        document.getElementById('coordinates').value = JSON.stringify({
            type: 'Polygon',
            coordinates: [coordinates]
        });
    });

    // Clear button functionality
    document.getElementById('clearButton').addEventListener('click', function() {
        drawnItems.clearLayers();
        document.getElementById('coordinates').value = '';
    });

    // Form submission
    document.getElementById('polygonForm').addEventListener('submit', function(e) {
        if (!document.getElementById('coordinates').value) {
            e.preventDefault();
            alert('Por favor, desenhe um polígono no mapa antes de salvar.');
        }
    });
});
</script>
@endsection