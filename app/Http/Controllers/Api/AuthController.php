<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login user with session-based authentication
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            

            // Find user by email
            $user = User::where('email', $validated['email'])->first();

            // Check if user exists
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Email tidak ditemukan dalam sistem.'],
                ]);
            }

            // Check password
            if (!Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Password yang Anda masukkan salah.'],
                ]);
            }

            // Create session (HttpOnly cookie will be set automatically)
            // Session middleware will handle session start
            Auth::login($user);
            
            // Save session to ensure cookie is set
            $request->session()->save();

            $response = response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'teacher',
                ],
                'message' => 'Login berhasil',
            ], 200);

            // Explicitly set cookie headers for cross-origin support
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat login',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        try {
            
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $response = response()->json([
                'user' => [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'role' => Auth::user()->role ?? 'teacher',
                ],
            ], 200);

            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Auth me error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Logout user (destroy session)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }
}

