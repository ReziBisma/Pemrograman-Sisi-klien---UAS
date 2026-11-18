<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DokterController extends Controller
{
    private function cariDokter(string $id): User
    {
        return User::with('poli')->where('role', 'dokter')->findOrFail($id);
    }

    public function index()
    {
        return UserResource::collection(
            User::with('poli')->where('role', 'dokter')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'alamat'   => 'required|string',
            'no_ktp'   => 'required|string|max:16|unique:users,no_ktp',
            'no_hp'    => 'required|string|max:15',
            'id_poli'  => 'required|exists:poli,id',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role']     = 'dokter';

        $dokter = User::create($data);

        return (new UserResource($dokter->load('poli')))
            ->response()->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new UserResource($this->cariDokter($id));
    }

    public function update(Request $request, string $id)
    {
        $dokter = $this->cariDokter($id);

        $data = $request->validate([
            'nama'     => 'required|string|max:255',
            'alamat'   => 'required|string',
            'no_ktp'   => 'required|string|max:16|unique:users,no_ktp,' . $dokter->id,
            'no_hp'    => 'required|string|max:15',
            'id_poli'  => 'required|exists:poli,id',
            'email'    => 'required|email|unique:users,email,' . $dokter->id,
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // kosong = password tidak diubah
        }

        $dokter->update($data);

        return new UserResource($dokter->load('poli'));
    }

    public function destroy(string $id)
    {
        $this->cariDokter($id)->delete();

        return response()->json(['message' => 'Dokter berhasil dihapus.']);
    }
}