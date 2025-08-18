<!-- Fire Station Details Form (Responsive) -->
<div class="bg-white p-6 sm:p-8 rounded-lg shadow-xl">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-6">Fire Station Details</h1>
    <form method="POST">
        @csrf
        <div class="space-y-6">
            <!-- Fire Station Details -->
            <div class="text-xl font-semibold text-gray-700 mb-4">About Fire Station</div>

            <!-- Our Mission -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center border-b border-gray-300 pb-4">
                <label for="station-mission" class="text-lg font-semibold text-gray-700 mb-2 sm:mb-0 sm:w-1/4">Our Mission</label>
                <textarea id="station-mission" name="station_mission" class="mt-2 sm:mt-0 ml-0 sm:ml-4 w-full sm:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter the mission of the fire station" rows="4" required>Providing emergency services and ensuring public safety 24/7.</textarea>
            </div>

            <!-- About Fire Station -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center border-b border-gray-300 pb-4">
                <label for="station-about" class="text-lg font-semibold text-gray-700 mb-2 sm:mb-0 sm:w-1/4">About the Fire Station</label>
                <textarea id="station-about" name="station_about" class="mt-2 sm:mt-0 ml-0 sm:ml-4 w-full sm:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter details about the fire station" rows="4" required>Our station is dedicated to protecting and serving the community by providing excellent fire safety services.</textarea>
            </div>

            <!-- Fire Station Graph-->
            <div class="flex flex-col sm:flex-row items-start sm:items-center border-b border-gray-300 pb-4">
                <label for="station-graph" class="text-lg font-semibold text-gray-700 mb-2 sm:mb-0 sm:w-1/4">Graph the Fire Station</label>
                <textarea id="station-graph" name="station_graph" class="mt-2 sm:mt-0 ml-0 sm:ml-4 w-full sm:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter details about the fire station" rows="4" required>Our station is dedicated to protecting and serving the community by providing excellent fire safety services.</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition">Save Changes</button>
        </div>
    </form>
</div>

<!-- Firebase SDKs -->
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Firebase config
    const firebaseConfig = {
        apiKey: "AIzaSyC1CtwiZwi120k6VrJwbFGKIY4pEJyxHxU",
        authDomain: "flare-capstone-c029d.firebaseapp.com",
        databaseURL: "https://flare-capstone-c029d-default-rtdb.firebaseio.com",
        projectId: "flare-capstone-c029d",
        storageBucket: "flare-capstone-c029d.firebasestorage.app",
        messagingSenderId: "683706660470",
        appId: "1:683706660470:web:d1302ac460f71bf8929157",
        measurementId: "G-QDWQJKCYPN"
    };

    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    const db = firebase.database();

    const userEmail = "{{ session('firebase_user_email') }}";
    if (!userEmail) return;

    const stations = [
        'CanocotanFireStation',
        'LaFilipinaFireStation',
        'MabiniFireStation'
    ];

    stations.forEach(node => {
        db.ref(node + '/MabiniProfile').once('value').then(snap => {
            const data = snap.val();
            if (!data) return;

            const stationEmail = String(data.email || '').trim().toLowerCase();
            if (stationEmail && stationEmail === String(userEmail).trim().toLowerCase()) {
                applyStationDataToForm(data);
            }
        }).catch(err => {
            console.error('Error fetching data for ' + node + ':', err);
        });
    });

    function applyStationDataToForm(data) {
        // Log data to inspect the structure
        console.log(data);

        // Populate fields with Firebase data
        const mission = firstNonEmpty(data.mission, "Our mission is to provide safety and service.");
        const about = firstNonEmpty(data.about, "We serve the community with dedication.");
        const graph = firstNonEmpty(data.graph, "Graph of the Fire Station.");

        // Assign data to form fields
        setValue('station-mission', mission);
        setValue('station-about', about);
        setValue('station-graph', graph);
    }

    function firstNonEmpty(...vals) {
        for (let i = 0; i < vals.length; i++) {
            const v = vals[i];
            if (v !== undefined && v !== null && String(v).trim() !== '') return String(v).trim();
        }
        return '';  // Returns an empty string if all values are empty
    }

    function setValue(id, val) {
        const el = document.getElementById(id);
        if (el && typeof val === 'string' && val.trim() !== '') {
            el.value = val;
        }
    }
});
</script>
