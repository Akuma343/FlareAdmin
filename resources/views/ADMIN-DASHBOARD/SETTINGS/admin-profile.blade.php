<!-- Admin Profile (Blade) -->
<div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Profile</h1>

    <form id="admin-form" method="POST">
        @csrf
        <div class="space-y-6">
            <!-- Admin Name -->
            <div class="flex flex-col md:flex-row items-center border-b border-gray-300 pb-4">
                <label for="admin-name" class="w-full md:w-1/4 text-lg font-semibold text-gray-700">Full Name</label>
                <input type="text" id="admin-name" name="admin_name" class="ml-4 w-full md:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="John Doe" required disabled>
            </div>

            <!-- Admin Email -->
            <div class="flex flex-col md:flex-row items-center border-b border-gray-300 pb-4">
                <label for="admin-email" class="w-full md:w-1/4 text-lg font-semibold text-gray-700">Email</label>
                <input type="email" id="admin-email" name="admin_email" class="ml-4 w-full md:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="johndoe@example.com" required disabled>
            </div>

            <!-- Phone Number -->
            <div class="flex flex-col md:flex-row items-center border-b border-gray-300 pb-4">
                <label for="admin-phone" class="w-full md:w-1/4 text-lg font-semibold text-gray-700">Phone Number</label>
                <input type="text" id="admin-phone" name="admin_phone" class="ml-4 w-full md:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="123-456-7890" required disabled>
            </div>

            <!-- Change Password -->
            <div class="text-xl font-semibold text-gray-800 mb-4">Change Password</div>

            <div class="flex flex-col md:flex-row items-center border-b border-gray-300 pb-4">
                <label for="admin-password" class="w-full md:w-1/4 text-lg font-semibold text-gray-700">New Password</label>
                <input type="password" id="admin-password" name="admin_password" class="ml-4 w-full md:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter new password" disabled>
            </div>

            <div class="flex flex-col md:flex-row items-center border-b border-gray-300 pb-4">
                <label for="admin-password-confirm" class="w-full md:w-1/4 text-lg font-semibold text-gray-700">Confirm Password</label>
                <input type="password" id="admin-password-confirm" name="admin_password_confirm" class="ml-4 w-full md:w-3/4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirm new password" disabled>
            </div>
        </div>

        <!-- Buttons for Edit and Save -->
        <div class="mt-6 flex justify-between">
            <button type="button" id="edit-btn" class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition">Edit</button>
            <button type="submit" id="save-btn" class="bg-green-600 text-white py-2 px-6 rounded-lg hover:bg-green-700 transition hidden">Save Changes</button>
        </div>
    </form>
</div>

<!-- Firebase SDKs (match versions used elsewhere) -->
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
        { name: 'CanocotanFireStation', profile: 'CanocotanProfile' },
        { name: 'LaFilipinaFireStation', profile: 'LaFilipinaProfile' },
        { name: 'MabiniFireStation', profile: 'MabiniProfile' }
    ];

    stations.forEach(station => {
        const profileRef = `${station.name}/${station.profile}`;

        db.ref(profileRef).once('value').then(snap => {
            const data = snap.val();
            if (!data) return;

            const stationEmail = String(data.email || '').trim().toLowerCase();
            if (stationEmail && stationEmail === String(userEmail).trim().toLowerCase()) {
                applyStationDataToForm(data);
            }
        }).catch(err => {
            console.error('Error fetching data for ' + station.name + ':', err);
        });
    });

    function applyStationDataToForm(data) {
        const contactName = firstNonEmpty(data.name);
        const email = firstNonEmpty(data.email);
        const phone = firstNonEmpty(data.contact);

        setValue('admin-name', contactName);
        setValue('admin-email', email);
        setValue('admin-phone', phone);
    }

    function firstNonEmpty(/* ...vals */) {
        for (let i = 0; i < arguments.length; i++) {
            const v = arguments[i];
            if (v !== undefined && v !== null && String(v).trim() !== '') return String(v).trim();
        }
        return '';
    }

    function setValue(id, val) {
        const el = document.getElementById(id);
        if (el && typeof val === 'string' && val.trim() !== '') el.value = val;
    }

    // Handle the Edit/Cancel functionality
    const editBtn = document.getElementById('edit-btn');
    const saveBtn = document.getElementById('save-btn');
    const formInputs = document.querySelectorAll('input');

    editBtn.addEventListener('click', function () {
        // Toggle edit mode
        formInputs.forEach(input => {
            input.disabled = !input.disabled; // Toggle between disabled and enabled
        });

        // Toggle button text
        if (editBtn.textContent === "Edit") {
            editBtn.textContent = "Cancel";
            saveBtn.classList.remove('hidden'); // Show the Save button
        } else {
            editBtn.textContent = "Edit";
            saveBtn.classList.add('hidden'); // Hide the Save button
            resetForm(); // Reset form if user clicks Cancel
        }
    });

    // Handle form submission for saving changes
    const form = document.getElementById('admin-form');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const updatedData = {
            name: document.getElementById('admin-name').value,
            email: document.getElementById('admin-email').value,
            contact: document.getElementById('admin-phone').value,
            password: document.getElementById('admin-password').value ? document.getElementById('admin-password').value : null
        };

        // Update Firebase with new data
        stations.forEach(station => {
            const profileRef = `${station.name}/${station.profile}`;

            db.ref(profileRef).once('value').then(snap => {
                const data = snap.val();
                if (!data) return;

                const stationEmail = String(data.email || '').trim().toLowerCase();
                if (stationEmail && stationEmail === String(userEmail).trim().toLowerCase()) {
                    db.ref(profileRef).update(updatedData)
                        .then(() => {
                            alert("Profile updated successfully!");
                            resetForm(); // Reset form to non-editable state after save
                            editBtn.textContent = "Edit"; // Reset button text
                            saveBtn.classList.add('hidden'); // Hide the Save button
                        })
                        .catch(err => {
                            console.error("Error updating profile:", err);
                        });
                }
            }).catch(err => {
                console.error('Error fetching data for ' + station.name + ':', err);
            });
        });
    });

    // Reset form to original (non-editable) state
    function resetForm() {
        formInputs.forEach(input => {
            input.disabled = true; // Disable the inputs again
        });
    }
});
</script>

