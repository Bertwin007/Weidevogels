<?php

namespace App\Http\Controllers;

use App\Models\DonationClick;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $moments = collect();
        $stats = ['moments' => 0, 'donations' => 0];
        $queue = $this->demoQueue();

        try {
            $moments = Observation::query()
                ->published()
                ->with(['annotation', 'project'])
                ->latest('published_at')
                ->limit(12)
                ->get()
                ->filter(fn (Observation $observation) => $observation->photoExistsOnDisk())
                ->take(3)
                ->values();

            $stats = [
                'moments' => Observation::published()->count(),
                'donations' => DonationClick::count(),
            ];

            $pending = Observation::query()
                ->pendingAnnotation()
                ->with('project')
                ->latest()
                ->limit(8)
                ->get();

            if ($pending->isNotEmpty()) {
                $queue = $pending
                    ->map(fn (Observation $observation) => $this->queueRowFromObservation($observation))
                    ->all();
            }
        } catch (\Throwable $e) {
            Log::error('Homepage laden mislukt: '.$e->getMessage(), ['exception' => $e]);
        }

        $kpis = [
            'partners' => max(1, Observation::published()->whereNotNull('guest_name')->distinct()->count('guest_name')),
            'open_triggers' => Observation::pendingAnnotation()->count(),
            'scans_24h' => Observation::query()->where('created_at', '>=', now()->subDay())->count(),
            'areas_kuikens' => Observation::query()
                ->where(function ($query) {
                    $query->where('ai_behavior', 'like', '%kuiken%')
                        ->orWhere('contributor_note', 'like', '%kuiken%');
                })
                ->count(),
        ];

        return view('home', [
            'moments' => $moments,
            'stats' => $stats,
            'queue' => $queue,
            'kpis' => $kpis,
            'scanUrl' => route('api.scan'),
            'submitUrl' => route('api.scan.submit'),
        ]);
    }

    /**
     * @return array{co: string, geb: string, trig: string, st: string, act: string}
     */
    private function queueRowFromObservation(Observation $observation): array
    {
        $company = $observation->guest_name
            ?? $observation->contributor_name
            ?? 'Nieuwe inzending';

        $trigger = filled($observation->ai_species)
            ? $observation->ai_species.' gespot'.($observation->ai_confidence ? " ({$observation->ai_confidence}%)" : '')
            : 'Nieuwe Greide-scan wacht op verificatie';

        $status = match (true) {
            filled($observation->ai_species) && ($observation->ai_confidence ?? 0) >= 80 => 'hot',
            filled($observation->ai_species) => 'warm',
            default => 'new',
        };

        $action = match ($status) {
            'hot' => 'Bel: teamdag plannen',
            'warm' => 'Bel: scan toelichten',
            default => 'Bel: welkom + pakket',
        };

        return [
            'co' => $company,
            'geb' => $observation->project?->name ?? 'Ljippelân',
            'trig' => $trigger,
            'st' => $status,
            'act' => $action,
        ];
    }

    /**
     * @return list<array{co: string, geb: string, trig: string, st: string, act: string}>
     */
    private function demoQueue(): array
    {
        return [
            ['co' => 'Bouwbedrijf De Vries', 'geb' => 'Gruttoland Tjerkwerd', 'trig' => "3 grutto's + kuikens gespot", 'st' => 'hot', 'act' => 'Bel: teamdag plannen'],
            ['co' => 'Jansma Verzekeringen', 'geb' => 'Ljippelân Workum', 'trig' => 'kwartaal-bewijs klaar', 'st' => 'warm', 'act' => 'Bel: rapport toelichten'],
            ['co' => 'De Friese Bakker', 'geb' => 'Noordereiland', 'trig' => 'eerste kievitnest van het seizoen', 'st' => 'hot', 'act' => 'Bel: uitnodigen langs te komen'],
            ['co' => 'Hiemstra Transport', 'geb' => 'Gruttoland Wommels', 'trig' => 'nieuwe Greide-scan ingezonden', 'st' => 'new', 'act' => 'Bel: welkom + pakket'],
            ['co' => 'Accountants Noord', 'geb' => 'Ljippelân Workum', 'trig' => 'tureluur erbij t.o.v. vorig jaar', 'st' => 'warm', 'act' => 'Bel: upgrade Beschermheer'],
        ];
    }
}
