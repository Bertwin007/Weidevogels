<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $moments = Observation::query()
            ->published()
            ->with(['annotation', 'project'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $stats = [
            'moments' => Observation::published()->count(),
            'donations' => \App\Models\DonationClick::count(),
        ];

        return view('home', compact('moments', 'stats'));
    }
}
