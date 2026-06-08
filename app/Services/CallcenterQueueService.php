<?php

namespace App\Services;

use App\Models\Observation;

class CallcenterQueueService
{
    /**
     * @return array{partners: int, open_triggers: int, scans_24h: int, areas_kuikens: int}
     */
    public function kpis(): array
    {
        return [
            'partners' => max(0, Observation::published()->whereNotNull('guest_name')->distinct()->count('guest_name')),
            'open_triggers' => Observation::pendingAnnotation()->count(),
            'scans_24h' => Observation::query()->where('created_at', '>=', now()->subDay())->count(),
            'areas_kuikens' => Observation::query()
                ->where(function ($query) {
                    $query->where('ai_behavior', 'like', '%kuiken%')
                        ->orWhere('contributor_note', 'like', '%kuiken%');
                })
                ->count(),
        ];
    }

    /**
     * @return list<array{co: string, geb: string, trig: string, st: string, act: string, observation_id: ?int}>
     */
    public function queue(): array
    {
        return Observation::query()
            ->pendingAnnotation()
            ->with('project')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Observation $observation) => $this->queueRowFromObservation($observation))
            ->all();
    }

    /**
     * @return array{co: string, geb: string, trig: string, st: string, act: string, observation_id: int}
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
            'observation_id' => $observation->id,
        ];
    }
}
