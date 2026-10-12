<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login dari aplikasi mobile.
     *
     * Mengembalikan token Sanctum yang dipakai sebagai
     * header: Authorization: Bearer <token>
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username'         => 'required|string',
            'password'         => 'required|string',
            'device_name'      => 'nullable|string|max:100',
            'device_platform'  => 'nullable|string|max:50',
        ]);

        $user = User::where('username', $data['username'])->first();

        // Password di aplikasi ini masih md5 (lihat User::setPasswordAttribute),
        // jadi pencocokannya disamakan agar kredensial web & mobile sama.
        $plain = $data['password'];
        $cocok = $user && (
            $user->password === md5($plain)     // md5 (lama)
            || Hash::check($plain, $user->password)  // bcrypt (kalau nanti dimigrasi)
        );

        if (!$cocok) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Buang token lama di perangkat yang sama supaya tidak menumpuk.
        $deviceName = $data['device_name'] ?? 'mobile';

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        // Simpan info perangkat (berguna untuk logout per perangkat / audit).
        $user->tokens()->where('name', $deviceName)->update([
            'device_platform' => $data['device_platform'] ?? null,
        ]);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Profil user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Cabut token untuk perangkat ini saja.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil keluar.',
        ]);
    }

    /**
     * Cabut semua token user (dipakai kalau perangkat hilang).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Berhasil keluar dari semua perangkat.',
        ]);
    }
}