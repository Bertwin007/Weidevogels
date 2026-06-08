<?php

namespace App\Http\Controllers;

use App\Models\DonationClick;
use App\Models\Observation;
use App\Services\LegacyRecordMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DonationRedirectController extends Controller
{
    public function general(Request $request): RedirectResponse
    {
        return $this->trackAndRedirect($request);
    }

    public function moment(Request $request, Observation $observation): RedirectResponse
    {
        if (! $observation->isPublished()) {
            abort(404);
        }

        return $this->trackAndRedirect($request, $observation);
    }

    private function trackAndRedirect(Request $request, ?Observation $observation = null): RedirectResponse
    {
        try {
            $attributes = LegacyRecordMapper::donationClickAttributes([
                'ip_hash' => hash('sha256', $request->ip().config('app.key')),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ], $observation);

            if ($attributes !== []) {
                DonationClick::create($attributes);
            }
        } catch (\Throwable $e) {
            Log::warning('Donatieklick niet gelogd: '.$e->getMessage(), [
                'exception' => $e,
                'observation_id' => $observation?->id,
            ]);
        }

        return redirect()->away($this->donationUrl($observation));
    }

    private function donationUrl(?Observation $observation = null): string
    {
        $url = config('greidefugels.donation_url');

        if (! is_string($url) || $url === '') {
            $url = 'https://www.agrarischnatuurfondsfryslan.nl/steun';
        }

        if ($observation === null) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query([
            'utm_content' => 'moment-'.$observation->id,
        ]);
    }
}
