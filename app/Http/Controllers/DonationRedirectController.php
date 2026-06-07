<?php

namespace App\Http\Controllers;

use App\Models\DonationClick;
use App\Models\Observation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DonationRedirectController extends Controller
{
    public function general(Request $request): RedirectResponse
    {
        return $this->trackAndRedirect($request);
    }

    public function moment(Request $request, Observation $observation): RedirectResponse
    {
        if ($observation->statusValue() !== 'published' && $observation->statusValue() !== 'approved') {
            abort(404);
        }

        return $this->trackAndRedirect($request, $observation);
    }

    private function trackAndRedirect(Request $request, ?Observation $observation = null): RedirectResponse
    {
        DonationClick::create([
            'observation_id' => $observation?->id,
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()->away(config('greidefugels.donation_url'));
    }
}
