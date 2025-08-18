@extends('ADMIN-DASHBOARD.app')

@section('title', 'Canocotan Fire Station')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">Fire Fighters</h1>

    <div class="bg-white p-6 shadow rounded-lg">
        <div class="flex items-center justify-between mb-4 flex-col sm:flex-row">
            <button id="addFirefighterBtn" class="px-4 py-2 bg-green-500 text-white rounded-lg mb-4 sm:mb-0">Add New Fire Fighter</button>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">Active Station:</span>
                <span id="activeStationLabel">—</span>
            </div>
        </div>

        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left text-gray-600">Fire Fighter ID</th>
                    <th class="px-4 py-2 text-left text-gray-600">Name</th>
                    <th class="px-4 py-2 text-left text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody id="firefighterTableBody"></tbody>
        </table>
    </div>

    <!-- View modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-11/12 sm:w-1/2">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Fire Fighter Details</h3>
            <div class="space-y-2">
                <p><strong>ID:</strong> <span id="viewFirefighterId"></span></p>
                <p><strong>Name:</strong> <span id="viewFirefighterName"></span></p>
                <p><strong>Email:</strong> <span id="viewFirefighterEmail"></span></p>
                <p><strong>Contact:</strong> <span id="viewFirefighterContact"></span></p>
                <p><strong>Birthday:</strong> <span id="viewFirefighterBirthday"></span></p>
            </div>
            <button id="closeViewModalBtn" class="mt-4 px-4 py-2 bg-gray-500 text-white rounded-lg">Close</button>
        </div>
    </div>

    <!-- Add/Edit modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-11/12 sm:w-1/2">
            <h3 class="text-xl font-semibold text-gray-700 mb-4" id="modalTitle">Add New Fire Fighter</h3>
            <form method="POST" id="addFirefighterForm">
                @csrf
                <input type="hidden" id="firefighterRecordKey" />
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Name:</label>
                    <input type="text" id="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Email:</label>
                    <input type="email" id="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Contact:</label>
                    <input type="text" id="contact" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold">Birthday:</label>
                    <input type="date" id="birthday" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                </div>
                <div class="mt-4">
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Save</button>
                </div>
            </form>
            <button id="closeAddModalBtn" class="mt-4 px-4 py-2 bg-gray-500 text-white rounded-lg">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>

<script>
// --- Firebase init ---
const firebaseConfig = {
  apiKey: "AIzaSyC1CtwiZwi120k6VrJwbFGKIY4pEJyxHxU",
  authDomain: "flare-capstone-c029d.firebaseapp.com",
  databaseURL: "https://flare-capstone-c029d-default-rtdb.firebaseio.com",
  projectId: "flare-capstone-c029d",
  storageBucket: "flare-capstone-c029d.appspot.com",
  messagingSenderId: "683706660470",
  appId: "1:683706660470:web:d1302ac460f71bf8929157"
};
firebase.initializeApp(firebaseConfig);

// --- Session/User context ---
const userEmail = "{{ session('firebase_user_email') }}";
const sessionStationNode = "{{ session('fire_station_node') }}"; // optional override

// Station config (no __seq anywhere)
const stationConfig = {
  "MabiniFireStation":     { label: "Mabini Fire Station",      fightersSub: "MabiniFireFighters",     prefix: "MABINI" },
  "CanocotanFireStation":  { label: "Canocotan Fire Station",    fightersSub: "CanocotanFireFighters",  prefix: "CANOCOTAN" },
  "LaFilipinaFireStation": { label: "La Filipina Fire Station",  fightersSub: "LaFilipinaFireFighters", prefix: "LAFILIPINA" }
};

function stationFromEmail(email) {
  if (!email) return null;
  if (email.includes("mabini123@gmail.com"))     return "MabiniFireStation";
  if (email.includes("canocotan123@gmail.com"))  return "CanocotanFireStation";
  if (email.includes("lafilipina123@gmail.com")) return "LaFilipinaFireStation";
  return null;
}

const activeStationNode = sessionStationNode || stationFromEmail(userEmail);
const cfg = activeStationNode ? stationConfig[activeStationNode] : null;
const fightersPath = cfg ? `${activeStationNode}/${cfg.fightersSub}` : null;

document.getElementById('activeStationLabel').innerText = cfg ? cfg.label : 'Unknown';

// ---------- Helpers ----------
function makeReadableId(prefix) {
  const year = new Date().getFullYear();
  const newRef = firebase.database().ref().child('tmp').push();
  const key = newRef.key || Math.random().toString(36).slice(2);
  newRef.remove();
  const suffix = key.slice(-5).toUpperCase();
  return `${prefix}-${year}-${suffix}`;
}

// ---------- Load & render ----------
function loadFirefighters() {
  if (!fightersPath) return;

  firebase.database().ref(fightersPath).on('value', snapshot => {
    const tbody = document.getElementById('firefighterTableBody');
    tbody.innerHTML = '';

    const data = snapshot.val();
    if (!data) return;

    const rows = Object.entries(data)
      .map(([key, ff]) => ({ key, ...ff }))
      .sort((a, b) => (a.name || '').localeCompare(b.name || ''));

    rows.forEach(({ key, id, name }) => {
      const tr = document.createElement('tr');
      tr.className = 'border-b';
      tr.innerHTML = `
        <td class="px-4 py-2">${id || ''}</td>
        <td class="px-4 py-2 font-semibold">${name || ''}</td>
        <td class="px-4 py-2 space-x-2">
          <button class="viewBtn text-blue-500 hover:underline" data-key="${key}">View</button>
          <button class="editBtn text-yellow-500 hover:underline" data-key="${key}">Edit</button>
          <button class="deleteBtn text-red-500 hover:underline" data-key="${key}">Delete</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    bindActionButtons();
  });
}

function bindActionButtons() {
  if (!fightersPath) return;

  // View
  document.querySelectorAll('.viewBtn').forEach(btn => {
    btn.onclick = () => {
      const key = btn.dataset.key;
      firebase.database().ref(`${fightersPath}/${key}`).once('value').then(snap => {
        const d = snap.val() || {};
        document.getElementById('viewFirefighterId').innerText = d.id || '';
        document.getElementById('viewFirefighterName').innerText = d.name || '';
        document.getElementById('viewFirefighterEmail').innerText = d.email || '';
        document.getElementById('viewFirefighterContact').innerText = d.contact || '';
        document.getElementById('viewFirefighterBirthday').innerText = d.birthday || '';
        document.getElementById('viewModal').classList.remove('hidden');
      });
    };
  });

  // Edit
  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.onclick = () => {
      const key = btn.dataset.key;
      firebase.database().ref(`${fightersPath}/${key}`).once('value').then(snap => {
        const d = snap.val() || {};
        document.getElementById('modalTitle').innerText = 'Edit Fire Fighter';
        document.getElementById('submitBtn').innerText = 'Update Fire Fighter';
        document.getElementById('name').value = d.name || '';
        document.getElementById('email').value = d.email || '';
        document.getElementById('contact').value = d.contact || '';
        document.getElementById('birthday').value = d.birthday || '';
        document.getElementById('firefighterRecordKey').value = key;
        document.getElementById('addModal').classList.remove('hidden');
      });
    };
  });

  // Delete
  document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.onclick = () => {
      const key = btn.dataset.key;
      if (confirm('Are you sure you want to delete this firefighter?')) {
        firebase.database().ref(`${fightersPath}/${key}`).remove();
      }
    };
  });
}

// ---------- Modal actions ----------
document.getElementById('addFirefighterBtn').onclick = () => {
  document.getElementById('modalTitle').innerText = 'Add New Fire Fighter';
  document.getElementById('submitBtn').innerText = 'Add Fire Fighter';
  document.getElementById('addFirefighterForm').reset();
  document.getElementById('firefighterRecordKey').value = '';
  document.getElementById('addModal').classList.remove('hidden');
};

document.getElementById('closeAddModalBtn').onclick = () => {
  document.getElementById('addModal').classList.add('hidden');
};

document.getElementById('closeViewModalBtn').onclick = () => {
  document.getElementById('viewModal').classList.add('hidden');
};

// ---------- Check for duplicates ----------

function checkForDuplicates(name, email) {
  const allFightersRef = firebase.database().ref();
  const promises = Object.keys(stationConfig).map(station => {
    const path = `${station}/${stationConfig[station].fightersSub}`;
    return allFightersRef.child(path).once('value').then(snapshot => {
      const data = snapshot.val();
      return Object.values(data || {}).some(fighter => fighter.name === name && fighter.email === email);
    });
  });

  return Promise.all(promises).then(results => results.some(result => result));
}

// ---------- Create/Update (Check for duplicates) ----------
document.getElementById('addFirefighterForm').onsubmit = e => {
  e.preventDefault();
  if (!fightersPath || !cfg) return;

  const key = document.getElementById('firefighterRecordKey').value.trim();
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const contact = document.getElementById('contact').value.trim();
  const birthday = document.getElementById('birthday').value;

  checkForDuplicates(name, email).then(isDuplicate => {
    if (isDuplicate) {
      alert('A firefighter with the same name and email already exists.');
      return;
    }

    if (key) {
      firebase.database().ref(`${fightersPath}/${key}`).once('value').then(snap => {
        const existing = snap.val() || {};
        const data = { id: existing.id, name, email, contact, birthday };
        return firebase.database().ref(`${fightersPath}/${key}`).set(data);
      }).then(() => {
        document.getElementById('addModal').classList.add('hidden');
      });
      return;
    }

    const newKey = firebase.database().ref(fightersPath).push().key;
    const generatedId = makeReadableId(cfg.prefix);
    const data = { id: generatedId, name, email, contact, birthday };

    firebase.database().ref(`${fightersPath}/${newKey}`).set(data).then(() => {
      document.getElementById('addModal').classList.add('hidden');
    });
  });
};

window.addEventListener('DOMContentLoaded', loadFirefighters);
</script>

@endsection
