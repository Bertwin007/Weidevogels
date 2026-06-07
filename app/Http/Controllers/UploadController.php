<?php

namespace App\Http\Controllers;

use App\Enums\ObservationStatus;
use App\Models\Project;
use App\Services\ExifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function create(): View
    {
        $project = Project::query()->where('slug', 'ljippelan')->where('active', true)->firstOrFail();

        return view('upload.create', compact('project'));
    }

    public function store(Request $request, ExifService $exif): RedirectResponse
    {
        $project = Project::query()->where('slug', 'ljippelan')->where('active', true)->firstOrFail();

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
            'guest_name' => ['nullable', 'string', 'max:120'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'contributor_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = $validated['photo']->store('observations', 'public');
        $absolute = Storage::disk('public')->path($path);
        $takenAt = $exif->takenAt($absolute);

        $project->observations()->create([
            'guest_name' => $validated['guest_name'] ?? null,
            'guest_email' => $validated['guest_email'] ?? null,
            'photo_path' => $path,
            'contributor_note' => $validated['contributor_note'] ?? null,
            'exif_taken_at' => $takenAt,
            'status' => ObservationStatus::PendingAnnotation,
        ]);

        return redirect()
            ->route('upload.create')
            ->with('success', 'Bedankt! Een vrijwilliger maakt er een verhaal van.');
    }
}
