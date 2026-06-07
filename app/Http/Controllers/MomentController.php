<?php

namespace App\Http\Controllers;

use App\Enums\ObservationStatus;
use App\Models\Observation;
use Illuminate\View\View;

class MomentController extends Controller
{
    public function index(): View
    {
        $moments = Observation::query()
            ->published()
            ->with(['annotation', 'project'])
            ->latest('published_at')
            ->simplePaginate(12);

        return view('moments.index', compact('moments'));
    }

    public function show(Observation $observation): View
    {
        if ($observation->status !== ObservationStatus::Published) {
            abort(404);
        }

        $observation->load(['annotation', 'project']);

        return view('moments.show', compact('observation'));
    }
}
