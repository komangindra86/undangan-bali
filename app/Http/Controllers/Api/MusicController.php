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
            'data' => Music::where('is_active', true)->orderBy('title')->get(),
        ]);
    }
}
