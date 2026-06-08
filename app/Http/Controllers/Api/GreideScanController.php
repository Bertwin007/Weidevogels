<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GreideScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GreideScanController extends Controller
{
    public function store(Request $request, GreideScanService $scanner): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'string', 'max:6000000'],
            'media' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $scanner->scanBase64(
            $validated['image'],
            $validated['media'] ?? 'image/jpeg',
        );

        return response()->json([
            'species' => $result['species'],
            'live' => $result['live'],
            'notes' => $result['notes'],
        ]);
    }
}
