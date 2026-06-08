<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\Project;
use App\Services\LegacyRecordMapper;
use App\Support\ObservationLabels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('status')->toString() ?: 'all';
        $search = trim($request->string('q')->toString());

        $query = Observation::query()
            ->with(['annotation', 'project'])
            ->latest('created_at');

        match ($filter) {
            'pending' => $query->pendingAnnotation(),
            'published' => $query->published(),
            'rejected' => $query->whereIn('status', ['rejected', 'not_publishable']),
            'unpublished' => $query->where('status', 'unpublished'),
            default => null,
        };

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhere('contributor_note', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $submissions = $query->paginate(25)->withQueryString();

        $counts = [
            'all' => Observation::count(),
            'pending' => Observation::pendingAnnotation()->count(),
            'published' => Observation::published()->count(),
            'rejected' => Observation::whereIn('status', ['rejected', 'not_publishable'])->count(),
            'unpublished' => Observation::where('status', 'unpublished')->count(),
        ];

        return view('admin.submissions.index', compact('submissions', 'filter', 'search', 'counts'));
    }

    public function edit(Observation $observation): View
    {
        $observation->load(['annotation', 'project', 'annotation.annotator']);

        $projects = Project::query()->orderBy('name')->get();
        $statuses = ObservationLabels::editableStatuses();
        $currentStatus = ObservationLabels::normalizeStatus($observation->statusValue());

        $photoUrl = $observation->photoExistsOnDisk()
            ? route('annotate.photo', $observation)
            : null;

        return view('admin.submissions.edit', compact(
            'observation',
            'projects',
            'statuses',
            'currentStatus',
            'photoUrl',
        ));
    }

    public function update(Request $request, Observation $observation): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'guest_name' => ['nullable', 'string', 'max:120'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'contributor_note' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:pending,published,rejected,unpublished'],
            'species' => ['nullable', 'string', 'max:120'],
            'count_label' => ['nullable', 'string', 'max:60'],
            'behavior' => ['nullable', 'string', 'max:160'],
            'season' => ['nullable', 'string', 'max:60'],
            'story_line' => ['nullable', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ], [
            'project_id.required' => 'Kies een project.',
            'project_id.exists' => 'Het gekozen project bestaat niet.',
            'guest_email.email' => 'Vul een geldig e-mailadres in.',
            'status.required' => 'Kies een status.',
            'status.in' => 'De gekozen status is ongeldig.',
        ]);

        try {
            $observation->update([
                'project_id' => $validated['project_id'],
                'guest_name' => $validated['guest_name'] ?? null,
                'guest_email' => $validated['guest_email'] ?? null,
                'contributor_note' => $validated['contributor_note'] ?? null,
            ]);

            $hasAnnotation = collect([
                $validated['species'] ?? null,
                $validated['count_label'] ?? null,
                $validated['behavior'] ?? null,
                $validated['season'] ?? null,
                $validated['story_line'] ?? null,
                $validated['caption'] ?? null,
            ])->filter(fn (?string $value) => filled($value))->isNotEmpty();

            $annotation = null;

            if ($hasAnnotation) {
                $attributes = LegacyRecordMapper::annotationAttributes([
                    'annotator_id' => $request->user()->id,
                    'species' => $validated['species'] ?? $observation->annotation?->species ?? '',
                    'count_label' => $validated['count_label'] ?? $observation->annotation?->count_label ?? '1',
                    'behavior' => $validated['behavior'] ?? $observation->annotation?->behavior ?? '',
                    'season' => $validated['season'] ?? $observation->annotation?->season ?? '',
                    'story_line' => $validated['story_line'] ?? $observation->annotation?->story_line ?? '',
                    'caption' => $validated['caption'] ?? null,
                    'is_publishable' => $validated['status'] === 'published',
                ]);

                $annotation = $observation->annotation()->updateOrCreate(
                    ['observation_id' => $observation->id],
                    $attributes
                );
            }

            $this->applyStatus($observation, $validated['status'], $annotation, $validated);
        } catch (\Throwable $e) {
            Log::error('Inzending bijwerken mislukt: '.$e->getMessage(), [
                'exception' => $e,
                'observation_id' => $observation->id,
            ]);

            return back()
                ->withErrors(['form' => 'Opslaan mislukt: '.$e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('admin.submissions.edit', $observation)
            ->with('success', 'Inzending opgeslagen.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function applyStatus(Observation $observation, string $status, $annotation, array $validated): void
    {
        match ($status) {
            'pending' => $observation->update([
                'status' => 'pending',
                'slug' => null,
                'published_at' => null,
            ]),
            'published' => $this->publishObservation($observation, $annotation, $validated),
            'rejected' => $observation->markNotPublishable(),
            'unpublished' => $observation->unpublish(),
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function publishObservation(Observation $observation, $annotation, array $validated): void
    {
        if ($annotation) {
            $observation->publishFromAnnotation($annotation);

            return;
        }

        if ($observation->isPublished()) {
            return;
        }

        $existing = $observation->annotation;

        if ($existing && filled($existing->story_line)) {
            $observation->publishFromAnnotation($existing);

            return;
        }

        if (filled($validated['story_line'] ?? null)) {
            throw new \InvalidArgumentException('Sla eerst de annotatie op voordat je publiceert.');
        }

        throw new \InvalidArgumentException('Vul minimaal een verhaalregel in om te publiceren.');
    }
}
