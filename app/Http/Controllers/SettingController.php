<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Setting::all()]);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate(['value' => 'required']);

        Setting::set($key, $request->value);

        return response()->json(['message' => "Setting '{$key}' updated.", 'data' => Setting::where('key', $key)->first()]);
    }
}
