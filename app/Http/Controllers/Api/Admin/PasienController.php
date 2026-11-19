<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasienController extends Controller
{
    private function cariPasien(string $id): User
    {
        return User::where('role', 'pasien')->findOrFail($id);
    }

    public function index(Request $request)
    {
        $query = User::where('role', 'pasien')
            ->when($request->query('search'), function ($q, $cari) {
                $q->where(function ($q) use ($cari) {
                    $q->where('nama', 'like', "%{$cari}%")
                      ->orWhere('email', 'like', "%{$cari}%")
                      ->orWhere('no_ktp', 'like', "%{$cari}%")
                      ->orWhere('no_rm', 'like', "%{$cari}%");
                });
            })
            ->orderBy('nama');

        return $this->halaman($request, $query, UserResource::class);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'alamat'   => 'required|string',
            'no_ktp'   => 'required|string|max:16|unique:users,no_ktp',
            'no_hp'    => 'required|string|max:15',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role']     = 'pasien';

        $pasien = User::create($data);

        // Pasien yang dibuat admin juga mendapat No. RM
        $pasien->update([
            'no_rm' => date('Ym') . '-' . str_pad($pasien->id, 3, '0', STR_PAD_LEFT),
        ]);

        return (new UserResource($pasien))->response()->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new UserResource($this->cariPasien($id));
    }

    public function update(Request $request, string $id)
    {
        $pasien = $this->cariPasien($id);

        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'alamat'   => 'required|string',
            'no_ktp'   => 'required|string|max:16|unique:users,no_ktp,' . $pasien->id,
            'no_hp'    => 'required|string|max:15',
            'email'    => 'required|email|unique:users,email,' . $pasien->id,
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $pasien->update($data);

        return new UserResource($pasien);
    }

    public function destroy(string $id)
    {
        $this->cariPasien($id)->delete();

        return response()->json(['message' => 'Pasien berhasil dihapus.']);
    }
}