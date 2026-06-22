<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\AuthLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;


use Exception;

class AutentikasiController extends Controller
{

    public function login_auth(AuthLoginRequest $request)
    {
        // Ambil data input yang sudah lolos validasi.validasi dilakukan di file request AuthLoginRequest.php
        $credentials = $request->validated();

        //  RATE LIMITING: (Maksimal 5 kali percobaan per menit)
        $throttleKey = Str::transliterate(Str::lower($credentials['inpEmail']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login. Silakan coba lagi dalam $seconds detik."
            ], 429); // 429: http code untuk request yang terlalu banyak
        }

        try {

            $user = User::query()->where('email', $credentials['inpEmail'])->first();


            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                // Catat kegagalan login untuk sistem Rate Limiter
                RateLimiter::hit($throttleKey, 60); // Terkunci selama 60 detik jika mencapai batas

                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password yang Anda masukkan salah.'
                ], 401); // 401: Unauthorized
            }

            // Bersihkan riwayat kegagalan Rate Limiter jika login sukses
            RateLimiter::clear($throttleKey);

            // MANAGEMENT TOKEN: Hapus token lama pada perangkat ini jika ada (opsional)
            $user->tokens()->where('name', 'login-token')->delete();

            //  GENERATE TOKEN BARU
            $token = $user->createToken('login-token-new')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login sukses! Selamat datang kembali.',
                'token'   => $token,
                'user'    => [
                    'id'           => $user->id,
                    'nama_lengkap' => $user->nama_lengkap,
                    'email'        => $user->email,
                    'hp'           => $user->Hp
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal pada server, silakan coba sesaat lagi.',
                'error_dev' =>   $e->getMessage() //hapus ini ketika di production
            ], 500);
        }
    }


    public function logout_action(Request $request)
    {
        try {

            $user = $request->user();
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();


            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout, sesi Anda telah berakhir.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server saat mencoba logout.'
            ], 500);
        }
    }

    public function daftar_sintakqu(Request $request)
    {
        $validated = $request->validate([
            'inpName' => 'required|string|max:100',
            'inpHp' => 'required|string|max:15|unique:users,hp',
            'inpEmail' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {
            $user = User::create([
                'nama_lengkap' => $validated['inpName'],
                'hp' => $validated['inpHp'],
                'email' => $validated['inpEmail'],
                'password' => bcrypt($validated['password']),
            ]);
            return response()->json([
                'status'    => 'true',
                'message' => 'Berhasil mendaftarkan user, silahkan login !'
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Gagal mendaftar, email atau nomor HP sudah digunakan.',
                'error' => $e->getMessage() // Hapus baris ini saat production mode
            ], 400);
        }
    }
}
