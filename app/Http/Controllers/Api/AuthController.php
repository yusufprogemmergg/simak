<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\UserLicense;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * REGISTER — validasi license key dari tabel user_licenses
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Cari license key yang tersedia (belum dipakai)
            $license = UserLicense::where('license_key', $request->license_key)
                ->where('status', 'available')
                ->whereNull('user_id')
                ->first();

            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'License key tidak valid atau sudah digunakan.'
                ], 400);
            }

            // Buat akun owner
            $user = User::create([
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'owner',
            ]);

            // Aktifkan license — tautkan ke user yang baru daftar
            $license->update([
                'user_id'    => $user->id,
                'status'     => 'active',
                'start_date' => now(),
            ]);

            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Register berhasil',
                'data'    => [
                    'user'        => $user,
                    'license_key' => $license->license_key,
                    'token'       => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Register gagal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * LOGIN
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            // Super admin tidak perlu cek license
            if ($user->role !== 'super_admin' && !$user->hasActiveLicense()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lisensi tidak aktif atau sudah expired'
                ], 403);
            }

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            Auth::login($user);
            
            // Re-generate session untuk keamanan mencegah session fixation
            $request->session()->regenerate();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data'    => [
                    'user'  => $user,
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login gagal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * LOGOUT (jika belum ada, ini berguna untuk SPA)
     */
    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json(['message' => 'Logout success']);
    }
}