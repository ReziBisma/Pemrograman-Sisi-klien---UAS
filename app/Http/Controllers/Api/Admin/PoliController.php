<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoliResource;
use App\Models\Poli;
use Illuminate\Http\Request;

class PoliController extends Controller
{
    public function index()
    {
        return PoliResource::collection(Poli::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_poli'  => 'required|string|max:25',
            'keterangan' => 'nullable|string',
        ]);

        return (new PoliResource(Poli::create($data)))
            ->response()->setStatusCode(201);
    }

    public function show(string $id)
    {
        return new PoliResource(Poli::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'nama_poli'  => 'required|string|max:25',
            'keterangan' => 'nullable|string',
        ]);

        $poli = Poli::findOrFail($id);
        $poli->update($data);

        return new PoliResource($poli);
    }

    public function destroy(string $id)
    {
        Poli::findOrFail($id)->delete();

        return response()->json(['message' => 'Poli berhasil dihapus.']);
    }
}