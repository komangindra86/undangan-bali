<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Music;
use Illuminate\Http\JsonResponse;

class MusicController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Music::where('is_active', true)
                ->where(fn ($query) => $query->whereNull('catalog_key')
                    ->orWhere(fn ($licensed) => $licensed->whereNotNull('license_verified_at')
                        ->whereNotNull('attribution')->whereNotNull('preview_file_path')))
                ->orderByRaw("CASE WHEN catalog_key LIKE 'pixabay/%' THEN 0 ELSE 1 END")
                ->orderBy('title')->get(),
        ]);
    }
}
