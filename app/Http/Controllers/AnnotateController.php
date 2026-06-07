<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Support\LegacyRecordMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AnnotateController extends Controller
{
    public function index(): View
    {
        $queue = Observation::query()
            ->pendingAnnotation()
            ->with('project')
            ->oldest()
            ->simplePaginate(20);

        return view('annotate.index', compact('queue'));
    }

    public function edit(Observation $observation): View|RedirectResponse
    {
        if (! $observation->isPendingAnnotation()) {
            return redirect()
                ->route('annotate.index')
                ->with('info', 'Dit moment is al verwerkt.');
        }

        $observation->load('project');

        return view('annotate.edit', compact('observation'));
    }

    public function store(Request $request, Observation $observation): RedirectResponse
    {
        if (! $observation->isPendingAnnotation()) {
            return redirect()->route('annotate.index');
        }

        $validated = $request->validate([
            'species' => ['required', 'string', 'max:120'],
            'count_label' => ['required', 'string', 'max:60'],
            'behavior' => ['required', 'string', 'max:160'],
            'season' => ['required', 'string', 'max:60'],
            'story_line' => ['required', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'is_publishable' => ['sometimes', 'boolean'],
        ]);

        $isPublishable = $request->boolean('is_publishable');

        try {
            $attributes = LegacyRecordMapper::annotationAttributes([
                'annotator_id' => $request->user()->id,
                'species' => $validated['species'],
                'count_label' => $validated['count_label'],
                'behavior' => $validated['behavior'],
                'season' => $validated['season'],
                'story_line' => $validated['story_line'],
                'caption' => $validated['caption'] ?? null,
                'is_publishable' => $isPublishable,
            ]);

            $annotation = $observation->annotation()->updateOrCreate(
                ['observation_id' => $observation->id],
                $attributes
            );

            if ($isPublishable) {
                $observation->publishFromAnnotation($annotation);

                return redirect()
                    ->route('annotate.index')
                    ->with('success', 'Moment gepubliceerd op de site.');
            }

            $observation->markNotPublishable();

            return redirect()
                ->route('annotate.index')
                ->with('success', 'Opgeslagen als niet publiceerbaar.');
        } catch (\Throwable $e) {
            Log::error('Annotatie mislukt: '.$e->getMessage(), ['exception' => $e, 'observation_id' => $observation->id]);

            return back()
                ->withErrors(['story_line' => 'Opslaan mislukt: '.$e->getMessage()])
                ->withInput();
        }
    }
}
