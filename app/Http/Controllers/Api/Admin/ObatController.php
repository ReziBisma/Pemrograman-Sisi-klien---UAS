<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ObatResource;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    private function rules(): array
    {
        return [
            'nama_obat' => 'required|string|max:255',
            'kemasan'   => 'nullable|string|max:35',
            'harga'     => 'required|integer|min:0',
        ];
    }

    public function index()
    {
        return ObatResource::collection(Obat::all());
    }

    public function store(Request $request)
    {
        $obat = Obat::create($request->validate($this->rules()));

        return (new ObatResource($obat))->response()->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new ObatResource(Obat::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $obat = Obat::findOrFail($id);
        $obat->update($request->validate($this->rules()));

        return new ObatResource($obat);
    }

    public function destroy(string $id)
    {
        Obat::findOrFail($id)->delete();

        return response()->json(['message' => 'Obat berhasil dihapus.']);
    }
}