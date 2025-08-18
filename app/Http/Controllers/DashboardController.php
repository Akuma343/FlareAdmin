<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index(Request $request)
    {
        if (!session()->has('firebase_user_email')) {
            return redirect()->route('login')->with('error', 'You must be logged in to view this page.');
        }

        $email = strtolower(session('firebase_user_email'));
        $prefix = match ($email) {
            'canocotan123@gmail.com'  => 'Canocotan',
            'lafilipina123@gmail.com' => 'LaFilipina',
            'mabini123@gmail.com'     => 'Mabini',
            default                   => null
        };

        if (!$prefix) {
            return view('ADMIN-DASHBOARD.dashboard', [
                'fireReports' => [],
                'otherEmergencyReports' => []
            ]);
        }

        try {
            // Fetch both fire and other emergency reports
            $fireReports = $this->firebase->getFireReports($prefix);
            $otherEmergencyReports = $this->firebase->getOtherEmergencyReports($prefix);

            // Filter only today's reports
            $today = now()->format('d/m/Y');
            $fireReports = collect($fireReports)
                ->filter(fn($r) => isset($r['date'], $r['reportTime']) && $r['date'] === $today)
                ->sortByDesc(fn($r) => $this->parseDmyTime($r['date'], $r['reportTime']))
                ->values()->all();

            $otherEmergencyReports = collect($otherEmergencyReports)
                ->filter(fn($r) => isset($r['date'], $r['reportTime']) && $r['date'] === $today)
                ->sortByDesc(fn($r) => $this->parseDmyTime($r['date'], $r['reportTime']))
                ->values()->all();

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to fetch incident reports. Please try again.');
        }

        // Return the reports to the view
        return view('ADMIN-DASHBOARD.dashboard', compact('fireReports', 'otherEmergencyReports'));
    }

    private function parseDmyTime(string $d, string $t): int
    {
        $d = str_replace('-', '/', $d);
        $p = explode('/', $d);
        if (count($p) === 3) {
            [$day, $month, $year] = $p;
            if (strlen($year) === 2) $year = '20' . $year;
            $iso = sprintf('%04d-%02d-%02d %s', (int) $year, (int) $month, (int) $day, $t);
            return strtotime($iso) ?: 0;
        }
        return strtotime("$d $t") ?: 0;
    }
}
