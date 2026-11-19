<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registrasi pasien baru
    public function register(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'alamat'   => 'required|string',
            'no_ktp'   => 'required|numeric|digits_between:1,16|unique:users,no_ktp',
            'no_hp'    => 'required|numeric',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create(array_merge($data, [
            'password' => Hash::make($data['password']),
            'role'     => 'pasien',
        ]));

        // No. RM dibuat dari id user yang baru, sehingga pasti unik
        $user->update([
            'no_rm' => date('Ym') . '-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
        ]);

        return response()->json(array_merge([
            'message' => 'Registrasi berhasil.',
            'user'    => new UserResource($user),
        ], $this->buatToken($user)), 201);
    }

    // Login: email + password, mengembalikan token
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        return response()->json(array_merge([
            'message' => 'Login berhasil.',
            'role'    => $user->role,
            'user'    => new UserResource($user->load('poli')),
        ], $this->buatToken($user)));
    }

    // Profil user yang sedang login
    public function me(Request $request)
    {
        return new UserResource($request->user()->load('poli'));
    }

    // Logout: hapus token yang sedang dipakai
        // Logout: hapus token yang sedang dipakai
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        // Pada request dari halaman web (sesi) tidak ada token yang perlu dihapus
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    // Logout dari semua perangkat: hapus seluruh token milik user
    public function logoutSemua(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Semua token telah dicabut.']);
    }

    // Membuat token baru berlaku 24 jam, dan mencabut token lama milik user
    private function buatToken(User $user): array
    {
        $user->tokens()->delete();   // satu token aktif per pengguna

        $kedaluwarsa = now()->addHours(24);
        $token = $user->createToken('api-token', ['*'], $kedaluwarsa);

        return [
            'token'      => $token->plainTextToken,
            'expires_at' => $kedaluwarsa->toDateTimeString(),
        ];
    }
}