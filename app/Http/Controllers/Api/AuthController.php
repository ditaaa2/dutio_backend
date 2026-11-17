<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'kamar' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'kamar' => $validated['kamar'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'penghuni',
            'status' => 'aktif',
        ]);

        $token = $user->createToken('dutio-flutter')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil!',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if ($user->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun kamu sedang nonaktif.',
            ], 403);
        }

        $token = $user->createToken('dutio-flutter')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // PROFILE
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil ditampilkan!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'kamar' => $user->kamar,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ], 200);
    }

    // UPDATE PROFILE
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'kamar' => $user->kamar,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ], 200);
    }

    // UPDATE PASSWORD
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui!',
        ], 200);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil!',
        ]);
    }


    // REGISTER DEVICE UNTUK NOTIFIKASI
    public function registerDevice(Request $request)
    {
        $validated = $request->validate([
            'fid' => 'required|string|max:255',
        ]);

        $user = $request->user();

        $device = UserDevice::updateOrCreate(
            [
                'fid' => $validated['fid'],
            ],
            [
                'user_id' => $user->id,
                'last_registered_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Perangkat berhasil didaftarkan untuk notifikasi DUTIO!',
            'device' => $device,
        ], 200);
    }

}
