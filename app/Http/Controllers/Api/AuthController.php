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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

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

            if ($request->hasSession()) {
                Auth::login($user);
            }
            
            $responseData = [
                'user'        => $user,
                'license_key' => $license->license_key,
            ];
            
            // Hanya kirim token untuk Client Stateless (Mobile/Postman), Sembunyikan untuk SPA demi keamanan (XSS)
            if (!$request->hasSession()) {
                $responseData['token'] = $user->createToken('auth_token')->plainTextToken;
            }

            return response()->json([
                'success' => true,
                'message' => 'Register berhasil',
                'data'    => $responseData
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

            if ($request->hasSession()) {
                Auth::login($user);
                // Re-generate session untuk keamanan mencegah session fixation
                $request->session()->regenerate();
            }

            $responseData = ['user' => $user];
            
            // Hanya kirim token untuk Client Stateless (Mobile/Postman), Sembunyikan untuk SPA demi keamanan (XSS)
            if (!$request->hasSession()) {
                $responseData['token'] = $user->createToken('auth_token')->plainTextToken;
            }

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data'    => $responseData
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
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json(['message' => 'Logout success']);
    }

    /**
     * FORGOT PASSWORD
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.exists'   => 'Email tidak ditemukan dalam sistem.'
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            
            // Generate token
            $token = Str::random(60);

            // Save to password_reset_tokens
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email'      => $user->email,
                    'token'      => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Create URL (Frontend route usually /reset-password)
            $frontendUrl = env('APP_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

            // Send Email
            Mail::to($user->email)->send(new ResetPasswordMail($user, $frontendUrl));

            return response()->json([
                'success' => true,
                'message' => 'Tautan reset password telah dikirim ke email Anda.'
            ]);

        } catch (\Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email reset password.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * RESET PASSWORD
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.'
        ]);

        try {
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token reset password tidak valid atau sudah kedaluwarsa.'
                ], 400);
            }

            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Clear token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah. Silakan login kembali.'
            ]);

        } catch (\Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset password.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}