<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Station Sidebar</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-3"></div>

    <!-- Preload Bootstrap Icons Font -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/fonts/bootstrap-icons.woff2" as="font" type="font/woff2" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">

    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
        th, td { vertical-align: middle; }
        .toast { background-color: #E00024; color: white; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 5px 15px rgba(0,0,0,0.3); min-width: 250px; max-width: 300px; opacity: 0.95; }
        @keyframes fade-in-down { 0% { opacity: 0; transform: translateY(-10%); } 100% { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }
        .logo { width: 100px; height: 100px; background: #f4f4f4; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .logo img { width: 80%; height: 80%; object-fit: contain; }
        .sidebar { width: 250px; background-color: #E00024; height: 100vh; position: fixed; top: 0; left: 0; padding-top: 20px; color: white; display: flex; flex-direction: column; }
        .sidebar img.logo { width: 50px; height: auto; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto; }
        .sidebar h2 { text-align: center; font-size: 22px; margin-bottom: 50px; }
        .sidebar a { color: white; text-decoration: none; padding: 15px; font-size: 18px; display: flex; align-items: center; border-bottom: 1px solid white; transition: background 0.3s; }
        .sidebar a:hover { background-color: #E87F2E; }
        .active { background-color: #E87F2E; border-radius: 10px; }
        .logout { margin-top: auto; padding: 15px; background-color: #e74c3c; text-align: center; color: white; cursor: pointer; }
        .logout:hover { background-color: #c0392b; }
        .sidebar img { width: 20px; height: 20px; margin-right: 20px; }
        .main-content{ margin-left: 260px; padding: 20px; margin-top: 20px; }
    </style>
</head>
<body>

    @include('ADMIN-DASHBOARD.sidebar')

    <div class="main-content">
        @yield('content')
    </div>

    @yield('scripts')

    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Maps/Leaflet (unchanged) -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNyhUph8_RefB5yw_lr43J_7AMkeYICfU-lTQZYnbo&libraries=geometry&callback=initMap" async defer></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNyhUph8_RefB5yw_lr43J_7AMkeYICfU&libraries=geometry,places&callback=initMap" async defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNyhUph8_RefB5yw_lr43J_7AMkeYICfU&libraries=places"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<script>
let canocotanRef, laFilipinaRef, mabiniRef, responseMessageRef;
let fireSound, emergencySound;
const shownReplyToasts = new Set();
const activeToasts = new Map();

document.addEventListener('DOMContentLoaded', function () {
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

  firebase.initializeApp(firebaseConfig);
  const database = firebase.database();

  // Station -> Profile node names
  const stationProfile = {
    CanocotanFireStation: 'CanocotanProfile',
    LaFilipinaFireStation: 'LaFilipinaProfile',
    MabiniFireStation: 'MabiniProfile'
  };

  canocotanRef       = database.ref('CanocotanFireStation');
  laFilipinaRef      = database.ref('LaFilipinaFireStation');
  mabiniRef          = database.ref('MabiniFireStation');

  const userEmail = "{{ session('firebase_user_email') }}";

  fireSound = new Audio("{{ asset('sound/alert.mp3') }}");
  emergencySound = new Audio("{{ asset('sound/emergency.mp3') }}");
  fireSound.preload = emergencySound.preload = "auto";

  document.addEventListener('click', function unlockAudio() {
    fireSound.play().catch(() => {});
    emergencySound.play().catch(() => {});
    fireSound.pause(); emergencySound.pause();
    fireSound.currentTime = emergencySound.currentTime = 0;
    document.removeEventListener('click', unlockAudio);
  });

  if (userEmail) {
    checkUserInFireStation('CanocotanFireStation', userEmail);
    checkUserInFireStation('LaFilipinaFireStation', userEmail);
    checkUserInFireStation('MabiniFireStation', userEmail);
  }

  function checkUserInFireStation(station, email) {
    const profileKey = stationProfile[station];
    database.ref(`${station}/${profileKey}`).once('value').then(snap => {
      let data = snap.val();
      if (!data) return tryOldRoot();
      const em = (data.email || '').toLowerCase();
      if (em && em === email.toLowerCase()) {
        document.getElementById('station-name').innerText = data.name || station;
      }
    }).catch(tryOldRoot);

    function tryOldRoot() {
      database.ref(station).once('value').then(snap => {
        const data = snap.val() || {};
        const em = (data.email || '').toLowerCase();
        if (em && em === email.toLowerCase()) {
          document.getElementById('station-name').innerText = data.name || station;
        }
      }).catch(() => {});
    }
  }

  document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });

  // Station-scoped paths for reports and messages
  function getNodes(email) {
    email = (email || "").toLowerCase();

    if (email.includes("mabini123@gmail.com")) {
      const base = "MabiniFireStation";
      return {
        base,
        fire: `${base}/MabiniFireReport`,
        emergency: `${base}/MabiniOtherEmergency`,
        reply: `${base}/ReplyMessage`,
        response: `${base}/ResponseMessage`,
      };
    }
    if (email.includes("canocotan123@gmail.com")) {
      const base = "CanocotanFireStation";
      return {
        base,
        fire: `${base}/CanocotanFireReport`,
        emergency: `${base}/CanocotanOtherEmergency`,
        reply: `${base}/ReplyMessage`,
        response: `${base}/ResponseMessage`,
      };
    }
    if (email.includes("lafilipina123@gmail.com")) {
      const base = "LaFilipinaFireStation";
      return {
        base,
        fire: `${base}/LaFilipinaFireReport`,
        emergency: `${base}/LaFilipinaOtherEmergency`,
        reply: `${base}/ReplyMessage`,
        response: `${base}/ResponseMessage`,
      };
    }
    return null;
  }

  const nodes = getNodes(userEmail);
  if (!nodes) return;

  // Optional handle if needed elsewhere
  responseMessageRef = database.ref(nodes.response);

  // Listeners for latest Fire and Other Emergency reports
  firebase.database().ref(nodes.fire).limitToLast(1).on('child_added', snapshot => {
    const newReport = snapshot.val();
    const newId = snapshot.key;
    if (localStorage.getItem('last_fire_id') !== newId) {
      localStorage.setItem('last_fire_id', newId);
      newReport.id = newId;
      insertNewReportRow(newReport, 'fireReports');
      showQueuedToast({ id: newId, type: 'fireReports', reporterName: newReport.name || 'Unknown', message: "Fire Report! A new incident has been added.", sound: fireSound });
    }
  });

  firebase.database().ref(nodes.emergency).limitToLast(1).on('child_added', snapshot => {
    const newReport = snapshot.val();
    const newId = snapshot.key;
    if (localStorage.getItem('last_emergency_id') !== newId) {
      localStorage.setItem('last_emergency_id', newId);
      newReport.id = newId;
      insertNewReportRow(newReport, 'otherEmergency');
      showQueuedToast({ id: newId, type: 'otherEmergency', reporterName: newReport.name || 'Unknown', message: "Emergency Report! A new incident has been added.", sound: emergencySound });
    }
  });

// Listen for new replies inside each Fire Report
firebase.database().ref(nodes.fire).on('child_added', reportSnap => {
  const incidentId = reportSnap.key;
  const reportData = reportSnap.val();
  // Watch the messages node for this incident
  reportSnap.ref.child('messages').limitToLast(1).on('child_added', msgSnap => {
    const msg = msgSnap.val();
    if (!msg || (msg.type || '').toLowerCase() !== 'reply') return;
    if (localStorage.getItem('last_reply_id') === msgSnap.key) return;

    localStorage.setItem('last_reply_id', msgSnap.key);
    const name = reportData?.name || msg.reporterName || 'Unknown';
    showQueuedToast({
      id: incidentId,
      type: 'message',
      reporterName: name,
      message: "New Message Received. Tap to view the chat.",
      sound: null
    });
  });
});

// Listen for new replies inside each Other Emergency Report
firebase.database().ref(nodes.emergency).on('child_added', reportSnap => {
  const incidentId = reportSnap.key;
  const reportData = reportSnap.val();
  reportSnap.ref.child('messages').limitToLast(1).on('child_added', msgSnap => {
    const msg = msgSnap.val();
    if (!msg || (msg.type || '').toLowerCase() !== 'reply') return;
    if (localStorage.getItem('last_reply_id') === msgSnap.key) return;

    localStorage.setItem('last_reply_id', msgSnap.key);
    const name = reportData?.name || msg.reporterName || 'Unknown';
    showQueuedToast({
      id: incidentId,
      type: 'message',
      reporterName: name,
      message: "New Message Received. Tap to view the chat.",
      sound: null
    });
  });
});


  // Response status changes (station-scoped)
  firebase.database().ref(nodes.response).on('child_changed', function(snapshot) {
    const updatedReport = snapshot.val();
    updateTableStatus(updatedReport.id, updatedReport.status);
  });

});

// Toasts
function showQueuedToast({ id, type, reporterName = 'Unknown', message = '', sound = null }) {
  if (activeToasts.has(id)) return;
  const toastContainer = document.getElementById("toastContainer");
  const toast = document.createElement("div");
  toast.className = "toast flex justify-between items-center gap-4 animate-fade-in-down";
  toast.innerHTML = `
    <div class="text-sm font-semibold leading-snug">
      ${message}<br><span class="text-xs font-normal">Reporter: ${reporterName}</span>
    </div>
    <button class="text-white font-bold text-sm hover:opacity-75" onclick="handleViewIncident('${id}', '${type}')">View</button>
  `;
  toastContainer.appendChild(toast);
  sound?.play?.().catch(() => {});
  const timeout = setTimeout(() => removeToast(id), 10000);
  activeToasts.set(id, { element: toast, timeout });
}

function removeToast(id) {
  const toastData = activeToasts.get(id);
  if (!toastData) return;
  toastData.element.classList.add('opacity-0', 'transition-opacity', 'duration-500');
  setTimeout(() => toastData.element.remove(), 500);
  clearTimeout(toastData.timeout);
  activeToasts.delete(id);
}

function handleViewIncident(id, type) {
  if (!id || !type) return;
  const row = document.getElementById(`reportRow${id}`);
  const detectedType = row?.getAttribute('data-type') || type;

  if (type === 'message') {
    let report = null;
    if (row) {
      const reportData = row.getAttribute('data-report');
      if (reportData) {
        try { report = JSON.parse(reportData); } catch {}
      }
    }
    if (!report) {
      report = { id, name: "Unknown", contact: "N/A", fireStationName: "Unknown Fire Station" };
    }
    const exists = (detectedType === 'fireReports' ? fireReports : otherEmergencyReports).some(r => r.id === id);
    if (!exists) {
      if (detectedType === 'fireReports') fireReports.push(report);
      else if (detectedType === 'otherEmergency') otherEmergencyReports.push(report);
    }
    openMessageModal(id, detectedType);
    return;
  }

  if (row) {
    openDetailsModal(id, detectedType);
  } else {
    const url = `/app/incident-reports?incidentId=${encodeURIComponent(id)}&type=${encodeURIComponent(detectedType)}&modal=details`;
    window.location.href = url;
  }
}
</script>

@stack('scripts')

</body>
</html>
