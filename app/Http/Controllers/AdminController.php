<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AdminController extends Controller
{
    // Method to load the dashboard page
 public function dashboard()
{


    return view('ADMIN-DASHBOARD.dashboard');
}


      public function fireFighters()
    {
        return view('ADMIN-DASHBOARD.fire-fighters');
    }

    // Method to load the settings page
    public function settings()
    {
        return view('ADMIN-DASHBOARD.settings');
    }

    public function login()
    {
        return view('ADMIN-DASHBOARD.login');
    }




    // Add more methods for other pages if needed
}
