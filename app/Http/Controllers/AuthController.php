<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

class AuthController extends Controller
{
    protected FirebaseAuth $auth;

    public function __construct()
    {
        try {
            // Build credentials from .env instead of JSON file
            $firebaseCredentials = [
                "type" => "service_account",
                "project_id" => env('FIREBASE_PROJECT_ID'),
                "private_key" => str_replace("\\n", "\n", env('FIREBASE_PRIVATE_KEY')),
                "client_email" => env('FIREBASE_CLIENT_EMAIL'),
                "token_uri" => "https://oauth2.googleapis.com/token",
            ];

            if (
                empty($firebaseCredentials['project_id']) ||
                empty($firebaseCredentials['private_key']) ||
                empty($firebaseCredentials['client_email'])
            ) {
                throw new \RuntimeException("Firebase credentials are missing or invalid. Check your .env file.");
            }

            // Initialize Firebase Auth with .env credentials
            $this->auth = (new Factory)
                ->withServiceAccount($firebaseCredentials)
                ->createAuth();

        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to initialize Firebase Auth: " . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Define allowed emails
        $allowedEmails = [
            'mabini123@gmail.com',
            'lafilipina123@gmail.com',
            'canocotan123@gmail.com'
        ];

        if (!in_array($email, $allowedEmails)) {
            return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
        }

        try {
            $firebaseApiKey = env('FIREBASE_API_KEY');

            // Send login request to Firebase Authentication REST API
            $response = Http::post("https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$firebaseApiKey}", [
                'email' => $email,
                'password' => $password,
                'returnSecureToken' => true
            ]);

            if (!$response->ok()) {
                return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
            }

            $idToken = $response->json()['idToken'];

            // Verify ID Token with Firebase
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            Log::info('Firebase Authentication Successful', ['uid' => $uid, 'email' => $email]);

            // Store Firebase data in session
            Session::put('firebase_user_email', $email);
            Session::put('firebase_user_uid', $uid);

            Log::info('Session data after login', ['session' => session()->all()]);

            return redirect()->route('dashboard');

        } catch (\Throwable $e) {
            Log::error('Login failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['login' => 'Login failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout()
    {
        Session::forget('firebase_user_email');
        Session::forget('firebase_user_uid');
        return redirect()->route('login');
    }
}
