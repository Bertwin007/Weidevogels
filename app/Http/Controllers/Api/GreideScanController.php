<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GreideScanService;
use App\Services\PartnerScanSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'story_line' => $result['story_line'],
            'caption' => $result['caption'],
            'behavior' => $result['behavior'],
            'season' => $result['season'],
            'live' => $result['live'],
            'notes' => $result['notes'],
        ]);
    }

    public function submit(Request $request, PartnerScanSubmissionService $submitter): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'string', 'max:6000000'],
            'media' => ['nullable', 'string', 'max:64'],
            'company_name' => ['required', 'string', 'max:120'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'live' => ['nullable', 'boolean'],
            'species' => ['required', 'array', 'min:1'],
            'species.*.nl' => ['required', 'string', 'max:120'],
            'species.*.fy' => ['nullable', 'string', 'max:120'],
            'species.*.count' => ['nullable', 'integer', 'min:1'],
            'species.*.confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'story_line' => ['nullable', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'behavior' => ['nullable', 'string', 'max:160'],
            'season' => ['nullable', 'string', 'max:60'],
        ]);

        try {
            $observation = $submitter->submit(
                $validated['image'],
                $validated['media'] ?? 'image/jpeg',
                $validated['company_name'],
                $validated['company_email'] ?? null,
                $validated['species'],
                (bool) ($validated['live'] ?? false),
                $validated['story_line'] ?? null,
                $validated['behavior'] ?? null,
                $validated['season'] ?? null,
                $validated['caption'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Greide-scan inzenden mislukt: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['message' => 'Inzenden mislukt. Probeer het later opnieuw.'], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Inzending ontvangen. Een expert verifieert de scan en neemt contact op.',
            'observation_id' => $observation->id,
        ]);
    }
}
