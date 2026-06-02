<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => InvitationTemplate::where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function show(InvitationTemplate $template): JsonResponse
    {
        abort_unless($template->is_active, 404);

        return response()->json(['data' => $template]);
    }
}
