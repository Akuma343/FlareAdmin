@extends('ADMIN-DASHBOARD.app')

@section('title', 'Settings')

<style>
    #sidebar {
        margin-top: 60px;
        background: #D9D9D9;
        padding: 20px;
    }

    a {
        color: black;
    }

    li {
        margin-top: -30px;
    }

    #generalOptions {
        margin-bottom: 30px;
        margin-top: 0px;
    }

    #frontendPosting {
        margin-bottom: 30px;
    }

    .bg-white {
        margin-top: 35px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .active-link a {
        font-weight: bold;
        color: #1D4ED8;
    }

    /* Custom Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
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

@section('content')
    <div class="flex flex-col md:flex-row">
        <h1 class="text-xl font-bold mb-6 md:mr-6">Settings</h1>

        <!-- Sidebar -->
        <div id="sidebar" class="w-full md:w-1/4">
            <ul class="space-y-4">
                <!-- General Options -->
                <li id="generalOptions" onclick="toggleTab('general-options-tab', 'generalOptions')" class="hover:text-gray-400 flex items-center active-link">
                    <img src="{{ asset('images/settings_black.png') }}" alt="General Options" class="w-6 h-6 mr-3">
                    <a href="javascript:void(0)">General Options</a>
                </li>

                <!-- Frontend Posting -->
                <li id="frontendPosting" onclick="toggleTab('frontend-posting-tab', 'frontendPosting')" class="hover:text-gray-400 flex items-center">
                    <img src="{{ asset('images/profile_black.png') }}" alt="Frontend Posting" class="w-6 h-6 mr-3">
                    <a href="javascript:void(0)">My Profile</a>
                </li>

                <!-- My Account -->
                <li id="myAccount" onclick="openLogoutConfirmation()" class="hover:text-gray-400 flex items-center">
                    <img src="{{ asset('images/logout_black.png') }}" alt="My Account" class="w-6 h-6 mr-3">
                    <a href="javascript:void(0)">Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-3/4 p-6">
            <div id="general-options-tab" class="tab-content active">
                @include('ADMIN-DASHBOARD.SETTINGS.general-options')
            </div>

            <div id="frontend-posting-tab" class="tab-content">
                @include('ADMIN-DASHBOARD.SETTINGS.admin-profile')
            </div>

            <div id="myAccount-tab" class="tab-content">
                <!-- Optional: You can add content for logout confirmation or account settings -->
                <p>Logging out...</p>
            </div>
        </div>
    </div>

    <!-- Modal for Logout Confirmation -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <h2>Are you sure you want to logout?</h2>
            <button class="cancel" onclick="closeLogoutModal()">Cancel</button>
            <a id="confirmLogoutLink" href="javascript:void(0)">
                <button class="confirm">Logout</button>
            </a>
        </div>
    </div>
@endsection

<script>
    function toggleTab(tabId, linkId) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        document.querySelectorAll('#sidebar li').forEach(li => li.classList.remove('active-link'));
        document.getElementById(linkId).classList.add('active-link');
    }

    function openLogoutConfirmation() {
        document.getElementById('logoutModal').style.display = 'flex';

        // Set logout link action when confirmation is confirmed
        document.getElementById('confirmLogoutLink').onclick = function() {
            window.location.href = '{{ route('logout') }}';
        }
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleTab('general-options-tab', 'generalOptions');
    });
</script>
