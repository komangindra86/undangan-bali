<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['invitation_type' => ['sometimes', Rule::in(['wedding', 'birthday'])]]);

        return response()->json([
            'data' => InvitationTemplate::where('is_active', true)
                ->where('invitation_type', $request->input('invitation_type', 'wedding'))
                ->orderBy('id')->get(),
        ]);
    }

    public function show(InvitationTemplate $template): JsonResponse
    {
        abort_unless($template->is_active, 404);

        return response()->json(['data' => $template]);
    }
}
