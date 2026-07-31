<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/i'],
            'value' => ['present'],
        ]);

        UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id, 'key' => $data['key']],
            ['value' => $data['value']]
        );

        return response()->json(['ok' => true]);
    }
}
