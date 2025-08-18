<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class IncidentReportsController extends Controller
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
            return view('ADMIN-DASHBOARD.incident-reports', ['fireReports' => [], 'otherEmergencyReports' => []]);
        }

        try {
            $fireReports = $this->firebase->getFireReports($prefix);
            $otherEmergencyReports = $this->firebase->getOtherEmergencyReports($prefix);

            $fireReports = collect($fireReports)
                ->filter(fn($r) => isset($r['date'], $r['reportTime']))
                ->sortByDesc(fn($r) => $this->parseDmyTime($r['date'], $r['reportTime']))
                ->values()->all();

            $otherEmergencyReports = collect($otherEmergencyReports)
                ->filter(fn($r) => isset($r['date'], $r['reportTime']))
                ->sortByDesc(fn($r) => $this->parseDmyTime($r['date'], $r['reportTime']))
                ->values()->all();

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to fetch incident reports. Please try again.');
        }

        return view('ADMIN-DASHBOARD.incident-reports', compact('fireReports', 'otherEmergencyReports'));
    }

    private function parseDmyTime(string $d, string $t): int
    {
        $d = str_replace('-', '/', $d);
        $p = explode('/', $d);
        if (count($p) === 3) {
            [$day, $month, $year] = $p;
            if (strlen($year) === 2) $year = '20'.$year;
            $iso = sprintf('%04d-%02d-%02d %s', (int)$year, (int)$month, (int)$day, $t);
            return strtotime($iso) ?: 0;
        }
        return strtotime("$d $t") ?: 0;
    }
}
