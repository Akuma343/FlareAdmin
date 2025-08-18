<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResponseController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function updateReportStatus(Request $request)
    {
        try {
            $data = $request->validate([
                'prefix'     => 'required|string',
                'incidentId' => 'required|string',
                'reportType' => 'required|in:fireReports,otherEmergency',
                'status'     => 'required|string',
            ]);

            $ok = $this->firebaseService->updateScopedReportStatus(
                $data['prefix'],
                $data['reportType'],
                $data['incidentId'],
                $data['status']
            );

            return response()->json(['success' => $ok]);
        } catch (\Throwable $e) {
            Log::error("Error updating report status: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update report status']);
        }
    }

    /**
     * Station reply:
     * 1) Write to incident thread (type='response')
     * 2) Write to station inbox summary
     */
    public function storeResponse(Request $request)
    {
        try {
            $data = $request->validate([
                'prefix'          => 'required|string',                 // e.g., Mabini
                'reportType'      => 'required|in:fireReports,otherEmergency',
                'incidentId'      => 'required|string',
                'responseMessage' => 'required|string',
                'reporterName'    => 'nullable|string',
                'contact'         => 'nullable|string',
                'fireStationName' => 'nullable|string',                 // optional pretty name
            ]);

            $now     = now();
            $nowMs   = (int) round(microtime(true) * 1000);
            $fsNice  = $data['fireStationName'] ?? "{$data['prefix']} Fire Station";
            $contact = $data['contact'] ?? '';
            $name    = $data['reporterName'] ?? '';

            // 1) Thread message (Android chat reads this)
            $threadMsg = [
                'type'         => 'response',
                'text'         => $data['responseMessage'],
                'imageBase64'  => null,
                'audioBase64'  => null,
                'uid'          => null,
                'reporterName' => $name,
                'contact'      => $contact,
                'date'         => $now->format('Y-m-d'),
                'time'         => $now->format('H:i:s'),
                'timestamp'    => $nowMs,
                'isRead'       => false,
            ];
            $ok1 = $this->firebaseService->storeUnifiedMessage(
                $data['prefix'],
                $data['reportType'],
                $data['incidentId'],
                $threadMsg
            );

            // 2) Station inbox summary (Android Inbox reads this)
            $summary = [
                'uid'             => null,
                'fireStationName' => $fsNice,
                'incidentId'      => $data['incidentId'],
                'reporterName'    => $name,
                'contact'         => $contact,
                'responseMessage' => $data['responseMessage'],
                'responseDate'    => $now->format('Y-m-d'),
                'responseTime'    => $now->format('H:i:s'),
                'imageBase64'     => null,
                'timestamp'       => $nowMs,
                'isRead'          => false,
            ];
            $ok2 = $this->firebaseService->storeStationResponseSummary($data['prefix'], $summary);

            return response()->json(['success' => ($ok1 && $ok2)]);
        } catch (\Throwable $e) {
            Log::error("Error storing response: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to store response']);
        }
    }
}
