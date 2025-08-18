@extends('ADMIN-DASHBOARD.app')

@section('title', 'SMS Reports')

@section('content')
<div class="container mx-auto p-6 sm:p-8">
    <div class="bg-white p-6 shadow rounded-lg mb-8">
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-700 mb-4">SMS Reports</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left text-gray-600">ID</th>
                        <th class="px-4 py-2 text-left text-gray-600">Name</th>
                        <th class="px-4 py-2 text-left text-gray-600">Date</th>
                        <th class="px-4 py-2 text-left text-gray-600">Time</th>
                        <th class="px-4 py-2 text-left text-gray-600">Action</th>
                    </tr>
                </thead>
                <tbody id="smsReportsTableBody">
                    @forelse ($smsReports as $report)
                        <tr class="border-b" data-report='@json($report)'>
                            <td class="px-4 py-2">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2">{{ $report['name'] }}</td>
                            <td class="px-4 py-2">{{ $report['date'] }}</td>
                            <td class="px-4 py-2">{{ $report['time'] }}</td>
                            <td class="px-4 py-2 flex flex-wrap space-x-2">
                                <button onclick="openSmsDetailsModal(this)" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded">View Details</button>
                                @if(isset($report['latitude']) && isset($report['longitude']))
                                    <button onclick="openSmsLocationModal({{ $report['latitude'] }}, {{ $report['longitude'] }})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">View Location</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-2 text-center text-gray-500">No SMS reports found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div id="smsDetailsModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full sm:max-w-2xl relative">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Report Details</h2>
        <div class="space-y-2">
            <p><strong>ID:</strong> <span id="smsDetailId"></span></p>
            <p><strong>Name:</strong> <span id="smsDetailName"></span></p>
            <p><strong>Location:</strong> <span id="smsDetailLocation"></span></p>
            <p><strong>Fire Report:</strong> <span id="smsDetailFireReport"></span></p>
            <p><strong>Date:</strong> <span id="smsDetailDate"></span></p>
            <p><strong>Time:</strong> <span id="smsDetailTime"></span></p>
            <p><strong>Status:</strong> <span id="smsDetailStatus"></span></p>
        </div>
        <button onclick="document.getElementById('smsDetailsModal').classList.add('hidden')" class="absolute top-2 right-4 text-2xl">&times;</button>
    </div>
</div>

<!-- Location Modal -->
<div id="smsLocationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-6 w-full sm:w-3/4 lg:w-1/2 xl:w-1/3 shadow-lg relative">
        <h3 class="text-lg sm:text-xl font-semibold mb-4 text-gray-800">Report Location</h3>

        <!-- Map Container (Flexbox layout) -->
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
            <!-- Google Maps for routing -->
            <div class="flex-1">
                <iframe id="mapIframe" width="100%" height="470" style="border:0;" allowfullscreen loading="lazy"></iframe>
            </div>

            <!-- Leaflet.js map -->
            <div id="mapContainer" class="flex-1" style="height: 470px;"></div>
        </div>

        <button onclick="document.getElementById('smsLocationModal').classList.add('hidden')" class="absolute top-2 right-4 text-2xl">&times;</button>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    let leafletMap = null;
    let leafletLayersGroup = null;

    // Modal for viewing SMS details
    function openSmsDetailsModal(button) {
        const report = JSON.parse(button.closest('tr').dataset.report);
        document.getElementById('smsDetailId').innerText = report.id ?? 'N/A';
        document.getElementById('smsDetailName').innerText = report.name ?? 'N/A';
        document.getElementById('smsDetailLocation').innerText = report.location ?? 'N/A';
        document.getElementById('smsDetailFireReport').innerText = report.fireReport ?? 'N/A';
        document.getElementById('smsDetailDate').innerText = report.date ?? 'N/A';
        document.getElementById('smsDetailTime').innerText = report.time ?? 'N/A';
        document.getElementById('smsDetailStatus').innerText = report.status ?? 'Pending';

        document.getElementById('smsDetailsModal').classList.remove('hidden');
    }

    // Modal for viewing SMS location
    function openSmsLocationModal(reportLat, reportLng) {
        if (!reportLat || !reportLng) return;

        const userEmail = "{{ session('firebase_user_email') }}";

        // Fetch station data and display the location on the map
        canocotanRef.once('value').then(snapshot => {
            const data = snapshot.val();
            if (data && data.email?.toLowerCase() === userEmail.toLowerCase()) {
                return displaySmsMaps(reportLat, reportLng, data.latitude, data.longitude);
            }
            return laFilipinaRef.once('value');
        }).then(snapshot => {
            if (!snapshot?.val()) return;
            const data = snapshot.val();
            if (data.email?.toLowerCase() === userEmail.toLowerCase()) {
                return displaySmsMaps(reportLat, reportLng, data.latitude, data.longitude);
            }
            return mabiniRef.once('value');
        }).then(snapshot => {
            if (!snapshot?.val()) return;
            const data = snapshot.val();
            if (data.email?.toLowerCase() === userEmail.toLowerCase()) {
                return displaySmsMaps(reportLat, reportLng, data.latitude, data.longitude);
            }
        }).catch(err => console.error('Error finding station:', err));

        document.getElementById('smsLocationModal').classList.remove('hidden');
    }

    // Function to display maps (Google and Leaflet)
    function displaySmsMaps(reportLat, reportLng, stationLat, stationLng) {
        // Google Maps for routing
        const mapSrc = `https://www.google.com/maps/embed/v1/directions?origin=${reportLat},${reportLng}&destination=${stationLat},${stationLng}&key=AIzaSyCNyhUph8_RefB5yw_lr43J_7AMkeYICfU`;
        document.getElementById('mapIframe').src = mapSrc;

        // Leaflet.js map for geofencing
        if (!leafletMap) {
            leafletMap = L.map('mapContainer').setView([reportLat, reportLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(leafletMap);
            leafletLayersGroup = L.layerGroup().addTo(leafletMap);
        } else {
            leafletMap.setView([reportLat, reportLng], 15);
            leafletLayersGroup.clearLayers();
        }

        // Markers for report and station
        const reportIcon = L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const fireStationIcon = L.icon({
            iconUrl: '{{ asset('images/far_location.png') }}',
            iconSize: [41, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const reportMarker = L.marker([reportLat, reportLng], { icon: reportIcon }).bindPopup("📍 Report Location");
        const stationMarker = L.marker([stationLat, stationLng], { icon: fireStationIcon }).bindPopup("🚒 Fire Station Location");

        const circle = L.circle([reportLat, reportLng], {
            radius: 30,
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.4
        });

        leafletLayersGroup.addLayer(reportMarker);
        leafletLayersGroup.addLayer(stationMarker);
        leafletLayersGroup.addLayer(circle);
    }
</script>
@endpush
