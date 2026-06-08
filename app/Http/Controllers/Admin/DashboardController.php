<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationClick;
use App\Models\Observation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending' => Observation::pendingAnnotation()->count(),
            'published' => Observation::published()->count(),
            'donations' => DonationClick::count(),
        ];

        $observations = Observation::query()
            ->with(['annotation', 'project'])
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.dashboard', compact('stats', 'observations'));
    }

    public function unpublish(Observation $observation): RedirectResponse
    {
        $this->authorize('unpublish', $observation);

        $observation->unpublish();

        return back()->with('success', 'Moment van de site gehaald.');
    }
}
