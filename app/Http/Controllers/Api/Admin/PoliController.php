<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoliResource;
use App\Models\Poli;
use Illuminate\Http\Request;

class PoliController extends Controller
{
    public function index(Request $request)
    {
        $query = Poli::query()
            ->when($request->query('search'), function ($q, $cari) {
                $q->where(function ($q) use ($cari) {
                    $q->where('nama_poli', 'like', "%{$cari}%")
                      ->orWhere('keterangan', 'like', "%{$cari}%");
                });
            })
            ->orderBy('nama_poli');

        return $this->halaman($request, $query, PoliResource::class);
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