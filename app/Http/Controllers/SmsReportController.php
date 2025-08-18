<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class SmsReportController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        // Check if the user is logged in and has an email session
        if (!session()->has('firebase_user_email')) {
            return redirect()->route('login')->with('error', 'You must be logged in to view this page.');
        }

        // Get the user email and determine the corresponding fire station prefix
        $email = strtolower(session('firebase_user_email'));
        $prefix = match ($email) {
            'canocotan123@gmail.com'  => 'Canocotan',
            'lafilipina123@gmail.com' => 'LaFilipina',
            'mabini123@gmail.com'     => 'Mabini',
            default                   => null
        };

        if (!$prefix) {
            return view('ADMIN-DASHBOARD.sms-reports', ['smsReports' => []]);
        }

        try {
            // Fetch SMS reports for the user’s fire station
            $smsReports = $this->firebase->getSmsReports($prefix);

            // Filter and sort the reports based on date and time
            $smsReports = collect($smsReports)
                ->filter(fn($r) => isset($r['date'], $r['time'])) // Ensure both date and time exist
                ->sortByDesc(fn($r) => $this->parseDmyTime($r['date'], $r['time'])) // Sort by date and time descending
                ->values()->all();

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to fetch SMS reports. Please try again.');
        }

        // Return the view with the SMS reports
        return view('ADMIN-DASHBOARD.sms-reports', compact('smsReports'));
    }

    // Helper function to parse the date and time into a timestamp for sorting
    private function parseDmyTime(string $d, string $t): int
    {
        // Replace "-" with "/" in the date
        $d = str_replace('-', '/', $d);
        $p = explode('/', $d);

        if (count($p) === 3) {
            [$day, $month, $year] = $p;
            // Handle 2-digit year (e.g., '22' to '2022')
            if (strlen($year) === 2) $year = '20'.$year;
            // Format the date into a proper ISO string for comparison
            $iso = sprintf('%04d-%02d-%02d %s', (int)$year, (int)$month, (int)$day, $t);
            return strtotime($iso) ?: 0; // Return the timestamp or 0 if invalid
        }
        return strtotime("$d $t") ?: 0; // Fallback for other formats
    }

    // Method to update SMS report status
    public function updateSmsReportStatus(Request $request, $id)
    {
        $status = $request->input('status');

        // Get the user’s fire station prefix
        $email = strtolower(session('firebase_user_email'));
        $prefix = match ($email) {
            'canocotan123@gmail.com'  => 'Canocotan',
            'lafilipina123@gmail.com' => 'LaFilipina',
            'mabini123@gmail.com'     => 'Mabini',
            default                   => null
        };

        if (!$prefix) {
            return redirect()->route('sms-reports')->with('error', 'No matching fire station found.');
        }

        try {
            // Update the status in Firebase
            $this->firebase->updateReportStatus($prefix, 'SmsReport', $id, $status);
            return redirect()->route('sms-reports')->with('success', 'SMS report status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sms-reports')->with('error', 'Failed to update SMS report status.');
        }
    }
}
