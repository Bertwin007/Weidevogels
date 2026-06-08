<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Services\AiPreScanService;
use App\Services\LegacyRecordMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnnotateController extends Controller
{
    public function index(): View
    {
        $queueTotal = Observation::query()->pendingAnnotation()->count();

        $queue = Observation::query()
            ->pendingAnnotation()
            ->with('project')
            ->oldest()
            ->simplePaginate(20);

        return view('annotate.index', compact('queue', 'queueTotal'));
    }

    public function edit(Observation $observation): View|RedirectResponse
    {
        if (! $observation->isPendingAnnotation()) {
            return redirect()
                ->route('annotate.index')
                ->with('info', 'Dit moment is al verwerkt.');
        }

        $observation->load('project');

        $photoUrl = $observation->photoExistsOnDisk()
            ? route('annotate.photo', $observation)
            : null;

        $aiScanner = app(AiPreScanService::class);

        return view('annotate.edit', [
            'observation' => $observation,
            'photoUrl' => $photoUrl,
            'aiEnabled' => (bool) config('greidefugels.ai.enabled'),
            'aiConfigured' => $aiScanner->usesRealVision(),
            'aiProvider' => $aiScanner->activeProvider(),
        ]);
    }

    public function rescan(Observation $observation, AiPreScanService $scanner): RedirectResponse
    {
        if (! $observation->isPendingAnnotation()) {
            return redirect()->route('annotate.index');
        }

        $suggestion = $scanner->analyze($observation->fresh());

        if ($suggestion->isEmpty() && $suggestion->notes) {
            return back()->withErrors(['ai' => $suggestion->notes]);
        }

        $message = $suggestion->isHeuristicSuggestion()
            ? 'Basisvoorstel gemaakt. Voor hogere nauwkeurigheid: stel GOOGLE_AI_API_KEY in.'
            : 'AI-voorstel vernieuwd ('.($suggestion->confidence ?? '?').'% zekerheid). Controleer en pas zo nodig aan.';

        return back()->with('success', $message);
    }

    public function photo(Observation $observation): BinaryFileResponse
    {
        $absolute = $observation->absolutePhotoPath();

        abort_unless($absolute, 404);

        return response()->file($absolute, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
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
