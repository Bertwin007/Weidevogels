<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\LegacyRecordMapper;
use App\Services\ExifService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function create(): View
    {
        $project = Project::findLjippelan();

        abort_unless($project, 404, 'Project Ljippelân is nog niet ingesteld.');

        return view('upload.create', compact('project'));
    }

    public function store(Request $request, ExifService $exif): RedirectResponse
    {
        $project = Project::findLjippelan();

        if (! $project) {
            return back()->withErrors(['photo' => 'Project Ljippelân ontbreekt. Neem contact op met de beheerder.']);
        }

        $validated = $request->validate([
            'photo' => ['required', 'file', 'image', 'max:10240'],
            'guest_name' => ['nullable', 'string', 'max:120'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'contributor_note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $path = $validated['photo']->store('observations', 'public');

            if (! $path) {
                return back()->withErrors(['photo' => 'Opslaan van de foto is mislukt. Controleer de rechten op storage/.'])->withInput();
            }

            $absolute = Storage::disk('public')->path($path);
            $takenAt = $exif->takenAt($absolute);

            $attributes = LegacyRecordMapper::observationAttributes([
                'guest_name' => $validated['guest_name'] ?? null,
                'guest_email' => $validated['guest_email'] ?? null,
                'photo_path' => $path,
                'contributor_note' => $validated['contributor_note'] ?? null,
                'exif_taken_at' => $takenAt,
                'status' => 'pending',
            ], $validated['photo']);

            $project->observations()->create($attributes);
        } catch (\Throwable $e) {
            Log::error('Upload mislukt: '.$e->getMessage(), ['exception' => $e]);

            return back()
                ->withErrors(['photo' => 'Upload mislukt: '.$e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('upload.create')
            ->with('success', 'Bedankt! Een vrijwilliger maakt er een verhaal van.');
    }
}
