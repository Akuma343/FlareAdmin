<div class="sidebar">
    <!-- Logo Image -->
    <img style="width: 100px;" src="{{ asset('images/logo.png')}}" alt="Logo" class="logo">

    <h2 id="station-name"></h2>

    <!-- Dashboard Link (Active when on the dashboard route) -->
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <img src="{{ asset('images/dashboard.png')}}" alt="Dashboard">Dashboard
    </a>

    <a href="{{ route('incident-reports') }}" class="{{ request()->routeIs('incident-reports') ? 'active' : '' }}">
        <img src="{{ asset('images/report.png')}}" alt="Incident Reports">Incident Reports
    </a>

     <a href="{{ route('sms-reports') }}" class="{{ request()->routeIs('sms-reports') ? 'active' : '' }}">
        <img src="{{ asset('images/sms.png')}}" alt="Incident Reports">Sms Reports
    </a>

    <a href="{{ route('fire-fighters') }}" class="{{ request()->routeIs('fire-fighters') ? 'active' : '' }}">
        <img src="{{ asset('images/fireman.png')}}" alt="Fire Fighters">Fire Fighters
    </a>

    <!-- Settings Link -->
    <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">
        <img src="{{ asset('images/settings.png')}}" alt="Settings">Settings
    </a>


    <a onclick="openLogoutConfirmation(event)" class="menu-item">
        <img src="{{ asset('images/logout.png')}}" alt="Logout">Logout
    </a>


</div>


<!-- Modal for Logout Confirmation -->
<div id="logoutModal" class="modal" style="display: none;">
    <div class="modal-content">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
        <h2>Are you sure you want to logout?</h2>
        <button class="cancel" onclick="closeLogoutModal()">Cancel</button>
        <a id="confirmLogoutLink" href="javascript:void(0)">
            <button class="confirm">Logout</button>
        </a>
    </div>
</div>


<script>
    // Open the Logout Confirmation Modal
    function openLogoutConfirmation(event) {
        event.preventDefault(); // Prevent the default link action (navigate)

        // Ensure the logout link doesn't become active
        document.querySelector('.menu-item').classList.remove('active');

        // Show the modal
        document.getElementById('logoutModal').style.display = 'flex';
    }

    // Close the Logout Confirmation Modal
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none'; // Hide the modal
    }

    // Confirm Logout action (redirect to logout route)
    document.getElementById('confirmLogoutLink').onclick = function() {
        window.location.href = '{{ route('logout') }}'; // Redirect to logout route
    }
</script>

<style>
    /* Modal Centering and Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        display: none; /* Hidden by default */
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        width: 400px;
        text-align: center;
    }

    .modal-content img {
        width: 80px;
        height: 80px;
        margin-bottom: 20px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .modal-content h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .modal-content button {
        padding: 10px 20px;
        font-size: 16px;
        margin: 5px;
        cursor: pointer;
        border: none;
        border-radius: 5px;
    }

    .modal-content .cancel {
        background-color: #E87F2E;
        color: white;
    }

    .modal-content .confirm {
        background-color: #E00024;
        color: white;
    }
</style>
