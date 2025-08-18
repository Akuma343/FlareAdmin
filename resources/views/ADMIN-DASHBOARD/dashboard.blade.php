@extends('ADMIN-DASHBOARD.app')

@section('title', 'Fire Station Dashboard')

@section('content')
@php
    // Initialize reports
    $fireReports = isset($fireReports) && is_array($fireReports) ? $fireReports : [];
    $otherEmergencyReports = isset($otherEmergencyReports) && is_array($otherEmergencyReports) ? $otherEmergencyReports : [];

    // Count active incidents
    $activeCount = collect($fireReports)->where('status', 'Ongoing')->count()
                 + collect($otherEmergencyReports)->where('status', 'Ongoing')->count();

    // Merge and sort recent reports by date and reportTime, then limit to the first 5
    $recent = collect($fireReports)->merge($otherEmergencyReports)->sortByDesc(function ($r) {
        $d = trim((string)($r['date'] ?? ''));
        $t = trim((string)($r['reportTime'] ?? ''));
        $parts = explode('/', $d);
        if (count($parts) === 3) {
            $d = sprintf('%s-%s-%s', $parts[2], str_pad($parts[1], 2, '0', STR_PAD_LEFT), str_pad($parts[0], 2, '0', STR_PAD_LEFT));
        }
        return strtotime("$d $t") ?: 0;
    })->take(5)->values()->all();
@endphp

<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Welcome to the Fire Station Admin Dashboard</h1>

    <!-- Overview Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-4 shadow rounded-lg">
            <h2 class="text-xl font-semibold text-gray-700">Active Incidents</h2>
            <p id="activeIncidentsCount" class="text-gray-500">{{ $activeCount }} Active Incidents</p>
            <a href="{{ url('incident-reports') }}" class="text-blue-500 hover:underline mt-4 inline-block">View Details</a>
        </div>
    </div>

    <!-- Recent Incidents Section -->
    <div class="bg-white p-6 shadow rounded-lg mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Today's Incidents</h2>
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left text-gray-600">Incident ID</th>
                    <th class="px-4 py-2 text-left text-gray-600">Location</th>
                    <th class="px-4 py-2 text-left text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-gray-600">Action</th>
                </tr>
            </thead>
            <tbody id="recentIncidentsBody">
                @forelse($recent as $report)
                    @php
                        $type = array_key_exists('alertLevel', $report ?? []) ? 'fireReports' : 'otherEmergency';
                        $status = strtolower($report['status'] ?? 'unknown');
                        $color = $status === 'ongoing' ? 'red'
                                : ($status === 'completed' ? 'green'
                                : ($status === 'pending' ? 'orange'
                                : ($status === 'received' ? 'blue' : 'yellow')));
                    @endphp
                    <tr class="border-b">
                        <td class="px-4 py-2">#{{ $report['id'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $report['exactLocation'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-{{ $color }}-500">{{ ucfirst($report['status'] ?? 'Unknown') }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ url('incident-reports') }}?incidentId={{ $report['id'] ?? '' }}&type={{ $type }}" class="text-blue-500 hover:underline">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No incidents available today</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    let fireReports = @json($fireReports);
    let otherEmergencyReports = @json($otherEmergencyReports);
    const todayStr = new Date().toLocaleDateString('en-GB'); // "DD/MM/YYYY"

    function parseDateTime(dateStr, timeStr) {
        if (!dateStr) return 0;
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            const year = parts[2].length === 2 ? '20' + parts[2] : parts[2];
            return new Date(`${year}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}T${timeStr || '00:00'}`).getTime();
        }
        return Date.parse(`${dateStr} ${timeStr || ''}`) || 0;
    }

    function updateActiveIncidentsCount() {
        const active = fireReports.filter(r => r.status === 'Ongoing').length
                    + otherEmergencyReports.filter(r => r.status === 'Ongoing').length;
        document.getElementById('activeIncidentsCount').textContent = `${active} Active Incidents`;
    }

    function renderRecentIncidents() {
        const tbody = document.getElementById('recentIncidentsBody');
        const merged = [...fireReports.map(r => ({...r, __type: 'fireReports'})),
                        ...otherEmergencyReports.map(r => ({...r, __type: 'otherEmergency'}))];
        merged.sort((a,b) => parseDateTime(b.date, b.reportTime) - parseDateTime(a.date, a.reportTime));
        const frag = document.createDocumentFragment();

        if (merged.length === 0) {
            frag.innerHTML = `<tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No incidents available today</td></tr>`;
        } else {
            merged.slice(0, 5).forEach(report => {
                const status = (report.status || 'Unknown').toLowerCase();
                const color = status === 'ongoing' ? 'red'
                            : (status === 'completed' ? 'green'
                            : (status === 'pending' ? 'orange'
                            : (status === 'received' ? 'blue' : 'yellow')));

                const tr = document.createElement('tr');
                tr.className = 'border-b';
                tr.innerHTML = `
                    <td class="px-4 py-2">#${report.id ?? 'N/A'}</td>
                    <td class="px-4 py-2">${report.exactLocation ?? 'N/A'}</td>
                    <td class="px-4 py-2 text-${color}-500">${(report.status ?? 'Unknown')}</td>
                    <td class="px-4 py-2">
                        <a class="text-blue-500 hover:underline" href="{{ url('incident-reports') }}?incidentId=${encodeURIComponent(report.id ?? '')}&type=${encodeURIComponent(report.__type)}">View Details</a>
                    </td>`;
                frag.appendChild(tr);
            });
        }
        tbody.innerHTML = '';
        tbody.appendChild(frag);
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateActiveIncidentsCount();
        renderRecentIncidents();
    });
</script>
@endsection
