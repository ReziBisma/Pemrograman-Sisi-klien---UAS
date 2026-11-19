<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Controller
{
    // Mengubah query menjadi respons daftar:
    // - ?semua=1       -> seluruh data tanpa pagination (untuk dropdown)
    // - ?per_page=N    -> N data per halaman (1 sampai 100, default 10)
    protected function halaman(Request $request, Builder $query, string $resource)
    {
        if ($request->boolean('semua')) {
            return $resource::collection($query->get());
        }

        $perHalaman = min(max((int) $request->query('per_page', 10), 1), 100);

        return $resource::collection(
            $query->paginate($perHalaman)->withQueryString()
        );
    }
}