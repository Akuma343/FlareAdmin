<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Kreait\Firebase\Auth as FirebaseAuth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\AuthException;
use Illuminate\Support\Facades\Auth;


class User extends Authenticatable
{
    use Notifiable;

    // Mass assignable fields
    protected $fillable = [
        'email', 'password', 'name', // Ensure only the necessary fields
    ];

    // Hidden fields for serialization
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Cast attributes
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Method to authenticate using Firebase
    public static function loginWithFirebase($email, $password)
    {
        // Initialize Firebase Auth
        $firebaseAuth = (new Factory())->createAuth();

        try {
            // Authenticate user using Firebase with email and password
            $user = $firebaseAuth->signInWithEmailAndPassword($email, $password);

            // Return the authenticated Firebase user
            return $user;
        } catch (AuthException $e) {
            // Handle Firebase authentication failure
            throw new \RuntimeException("Firebase Authentication failed: " . $e->getMessage());
        }
    }
}
