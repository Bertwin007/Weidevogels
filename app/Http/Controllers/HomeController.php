<?php

namespace App\Http\Controllers;

use App\Models\DonationClick;
use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $moments = collect();
        $stats = ['moments' => 0, 'donations' => 0];

        try {
            $moments = Observation::query()
                ->published()
                ->with(['annotation', 'project'])
                ->latest('published_at')
                ->limit(3)
                ->get();

            $stats = [
                'moments' => Observation::published()->count(),
                'donations' => DonationClick::count(),
            ];
        } catch (\Throwable $e) {
            Log::error('Homepage laden mislukt: '.$e->getMessage(), ['exception' => $e]);
        }

        return view('home', compact('moments', 'stats'));
    }
}
