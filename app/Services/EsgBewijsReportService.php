<?php

namespace App\Services;

use App\Models\Observation;
use App\Support\PartnerSlug;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EsgBewijsReportService
{
    /**
     * @return list<array{slug: string, company: string, published: int, project: ?string, latest: ?string}>
     */
    public function listPartners(?int $season = null): array
    {
        $season ??= (int) now()->year;

        return Observation::query()
            ->published()
            ->whereNotNull('guest_name')
            ->where('guest_name', '!=', '')
            ->with('project')
            ->get()
            ->filter(fn (Observation $observation) => $this->observationInSeason($observation, $season))
            ->groupBy('guest_name')
            ->map(function (Collection $group, string $company) {
                $latest = $group->sortByDesc('published_at')->first();

                return [
                    'slug' => PartnerSlug::fromCompany($company),
                    'company' => $company,
                    'published' => $group->count(),
                    'project' => $latest?->project?->name,
                    'latest' => $latest?->published_at?->format('d-m-Y'),
                ];
            })
            ->sortBy('company')
            ->values()
            ->all();
    }

  public function resolveCompany(string $partnerSlug): ?string
    {
        return Observation::query()
            ->whereNotNull('guest_name')
            ->where('guest_name', '!=', '')
            ->select('guest_name')
            ->distinct()
            ->pluck('guest_name')
            ->first(fn (string $name) => PartnerSlug::matchesCompany($partnerSlug, $name));
    }

    /**
     * @return array<string, mixed>
     */
    public function build(string $company, ?int $season = null): array
    {
        $season ??= (int) now()->year;
        $slug = PartnerSlug::fromCompany($company);
        $partnerConfig = config("esg.partners.{$slug}", []);
        $defaults = config('esg.defaults', []);

        $current = $this->observationsForCompanySeason($company, $season);
        $previous = $this->observationsForCompanySeason($company, $season - 1);

        if ($current->isEmpty()) {
            throw new \InvalidArgumentException("Geen geverifieerde momenten voor {$company} in seizoen {$season}.");
        }

        $project = $current->first()?->project;
        $speciesRows = $this->aggregateSpecies($current, $previous);
        $totals = $this->aggregateTotals($speciesRows, $current, $previous);

        $period = $this->seasonPeriod($current, $season);
        $geotagged = $current->contains(fn (Observation $o) => $o->exif_taken_at !== null);

        $habitat = array_merge(
            config('esg.habitat_defaults', []),
            $partnerConfig['habitat'] ?? []
        );

        $package = $partnerConfig['package'] ?? $defaults['package'] ?? 'Wachter';
        $adoptedM2 = (int) ($partnerConfig['adopted_m2'] ?? $defaults['adopted_m2'] ?? 2000);
        $partnerSince = $partnerConfig['partner_since'] ?? $defaults['partner_since'] ?? ($current->min('created_at')?->year);
        $areaHa = $partnerConfig['area_ha'] ?? $defaults['area_ha'] ?? 12;
        $areaSubtitle = $partnerConfig['area_subtitle'] ?? $defaults['area_subtitle'] ?? $project?->location;

        $reportNr = sprintf('GP-%d-%04d', $season, abs(crc32($company.$season)) % 10000);

        return [
            'report' => [
                'nr' => $reportNr,
                'season' => $season,
                'generatedAt' => now()->locale('nl')->isoFormat('D MMMM Y'),
                'generatedAtIso' => now()->toDateString(),
            ],
            'partner' => [
                'company' => $company,
                'package' => $package,
                'adopted_m2' => $adoptedM2,
                'adopted_m2_formatted' => number_format($adoptedM2, 0, ',', '.'),
                'partnerSince' => $partnerSince,
            ],
            'area' => [
                'name' => $project?->name ?? 'Ljippelân',
                'subtitle' => $areaSubtitle,
                'ha' => $areaHa,
                'description' => $project?->description,
            ],
            'totals' => $totals,
            'richnessScore' => $this->richnessScore(count($speciesRows)),
            'species' => $speciesRows,
            'habitat' => [
                'waterpeil_cm' => $habitat['waterpeil_cm'] ?? null,
                'kruidenrijk_pct' => $habitat['kruidenrijk_pct'] ?? null,
                'laat_gemaaid_pct' => $habitat['laat_gemaaid_pct'] ?? null,
                'plasdras_ha' => $habitat['plasdras_ha'] ?? null,
            ],
            'method' => [
                'photos' => $current->count(),
                'teamdays' => $this->distinctTeamDays($current),
                'period' => $period,
                'geotagged' => $geotagged,
            ],
            'share' => $this->buildShareText($company, $adoptedM2, $totals, $speciesRows),
            'observation_ids' => $current->pluck('id')->all(),
        ];
    }

    /**
     * @return Collection<int, Observation>
     */
    private function observationsForCompanySeason(string $company, int $season): Collection
    {
        return Observation::query()
            ->published()
            ->where('guest_name', $company)
            ->with(['annotation', 'project'])
            ->get()
            ->filter(fn (Observation $observation) => $this->observationInSeason($observation, $season))
            ->values();
    }

    private function observationInSeason(Observation $observation, int $season): bool
    {
        $date = $observation->exif_taken_at
            ?? $observation->published_at
            ?? $observation->created_at;

        return $date !== null && (int) $date->year === $season;
    }

    /**
     * @param  Collection<int, Observation>  $observations
     * @param  Collection<int, Observation>  $previous
     * @return list<array{nl: string, fy: ?string, count: int, nests: ?int, chicks: ?int, trend: ?string, delta: ?int, trend_label: string, trend_class: string}>
     */
    private function aggregateSpecies(Collection $observations, Collection $previous): array
    {
        $currentCounts = $this->speciesCountMap($observations);
        $previousCounts = $this->speciesCountMap($previous);
        $fyMap = config('esg.species_fy', []);

        $names = $currentCounts->keys()->merge($previousCounts->keys())->unique()->sort()->values();

        return $names->map(function (string $nl) use ($currentCounts, $previousCounts, $observations, $fyMap) {
            $count = (int) ($currentCounts[$nl] ?? 0);
            $prev = (int) ($previousCounts[$nl] ?? 0);
            $delta = $count - $prev;

            $trend = match (true) {
                $count > 0 && $prev === 0 => 'new',
                $delta > 0 => 'up',
                $delta < 0 => 'down',
                $count > 0 && $delta === 0 => 'same',
                default => null,
            };

            $meta = $this->speciesMetaFromObservations($observations, $nl);

            return [
                'nl' => $nl,
                'fy' => $meta['fy'] ?? ($fyMap[$nl] ?? null),
                'count' => $count,
                'nests' => $meta['nests'],
                'chicks' => $meta['chicks'],
                'trend' => $trend,
                'delta' => $trend === 'new' ? null : $delta,
                'trend_label' => $this->trendLabel($trend, $delta),
                'trend_class' => $this->trendClass($trend),
            ];
        })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Observation>  $observations
     * @return Collection<string, int>
     */
    private function speciesCountMap(Collection $observations): Collection
    {
        $map = [];

        foreach ($observations as $observation) {
            foreach ($this->speciesFromObservation($observation) as $row) {
                $nl = $row['nl'];
                $map[$nl] = ($map[$nl] ?? 0) + max(1, (int) ($row['count'] ?? 1));
            }
        }

        return collect($map);
    }

    /**
     * @param  Collection<int, Observation>  $observations
     * @return array{fy: ?string, nests: ?int, chicks: ?int}
     */
    private function speciesMetaFromObservations(Collection $observations, string $species): array
    {
        $fy = null;
        $nests = 0;
        $chicks = 0;
        $hasNest = false;
        $hasChick = false;

        foreach ($observations as $observation) {
            foreach ($this->speciesFromObservation($observation) as $row) {
                if (strcasecmp($row['nl'], $species) !== 0) {
                    continue;
                }

                if (filled($row['fy'] ?? null)) {
                    $fy = $row['fy'];
                }

                $behavior = strtolower((string) ($observation->annotation?->behavior ?? $observation->attributes['ai_behavior'] ?? ''));

                if (str_contains($behavior, 'nest')) {
                    $hasNest = true;
                    $nests += $this->parseCountHint($behavior, 'nest') ?? 1;
                }

                if (str_contains($behavior, 'kuiken')) {
                    $hasChick = true;
                    $chicks += $this->parseCountHint($behavior, 'kuiken') ?? 1;
                }
            }
        }

        return [
            'fy' => $fy,
            'nests' => $hasNest ? max(1, $nests) : null,
            'chicks' => $hasChick ? max(1, $chicks) : null,
        ];
    }

    /**
     * @return list<array{nl: string, fy?: string, count?: int}>
     */
    private function speciesFromObservation(Observation $observation): array
    {
        $notes = $observation->attributes['ai_notes'] ?? null;

        if (is_string($notes) && $notes !== '') {
            $data = json_decode($notes, true);

            if (is_array($data['species'] ?? null) && $data['species'] !== []) {
                return collect($data['species'])
                    ->filter(fn ($row) => filled($row['nl'] ?? null))
                    ->map(fn ($row) => [
                        'nl' => trim((string) $row['nl']),
                        'fy' => filled($row['fy'] ?? null) ? trim((string) $row['fy']) : null,
                        'count' => max(1, (int) ($row['count'] ?? 1)),
                    ])
                    ->all();
            }
        }

        $species = $observation->annotation?->species ?? $observation->attributes['ai_species'] ?? null;

        if (! filled($species)) {
            return [];
        }

        $count = $this->parseIntLabel($observation->annotation?->count_label ?? $observation->attributes['ai_count'] ?? 1);

        return [[
            'nl' => trim((string) $species),
            'fy' => null,
            'count' => max(1, $count),
        ]];
    }

    /**
     * @param  list<array{nl: string, count: int, nests: ?int, chicks: ?int}>  $speciesRows
     * @param  Collection<int, Observation>  $current
     * @param  Collection<int, Observation>  $previous
     * @return array{species: int, birds: int, nests: ?int, chicks: ?int, deltaPct: ?int, delta_label: string}
     */
    private function aggregateTotals(array $speciesRows, Collection $current, Collection $previous): array
    {
        $birds = array_sum(array_column($speciesRows, 'count'));
        $nests = array_sum(array_filter(array_column($speciesRows, 'nests'), fn ($v) => $v !== null));
        $chicks = array_sum(array_filter(array_column($speciesRows, 'chicks'), fn ($v) => $v !== null));

        $prevBirds = $this->speciesCountMap($previous)->sum();
        $deltaPct = null;

        if ($prevBirds > 0) {
            $deltaPct = (int) round((($birds - $prevBirds) / $prevBirds) * 100);
        }

        return [
            'species' => count($speciesRows),
            'birds' => $birds,
            'nests' => $nests > 0 ? $nests : null,
            'chicks' => $chicks > 0 ? $chicks : null,
            'deltaPct' => $deltaPct,
            'delta_label' => $deltaPct === null ? '—' : (($deltaPct >= 0 ? '+' : '').$deltaPct.'%'),
        ];
    }

    private function richnessScore(int $speciesCount): int
    {
        $cap = max(1, (int) config('esg.richness_species_cap', 8));

        return min(100, (int) round(($speciesCount / $cap) * 100));
    }

    /**
     * @param  Collection<int, Observation>  $observations
     */
    private function distinctTeamDays(Collection $observations): int
    {
        return $observations
            ->map(fn (Observation $o) => ($o->exif_taken_at ?? $o->created_at)?->toDateString())
            ->filter()
            ->unique()
            ->count();
    }

    /**
     * @param  Collection<int, Observation>  $observations
     */
    private function seasonPeriod(Collection $observations, int $season): string
    {
        $dates = $observations
            ->map(fn (Observation $o) => $o->exif_taken_at ?? $o->published_at ?? $o->created_at)
            ->filter();

        if ($dates->isEmpty()) {
            return (string) $season;
        }

        $min = $dates->min();
        $max = $dates->max();

        $months = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
        $minLabel = $months[max(0, (int) $min->format('n') - 1)] ?? $min->format('m');
        $maxLabel = $months[max(0, (int) $max->format('n') - 1)] ?? $max->format('m');

        if ($min->format('Y-m') === $max->format('Y-m')) {
            return "{$minLabel} {$season}";
        }

        return "{$minLabel}–{$maxLabel} {$season}";
    }

    /**
     * @param  list<array{nl: string, count: int}>  $speciesRows
     */
    private function buildShareText(string $company, int $adoptedM2, array $totals, array $speciesRows): string
    {
        $top = $speciesRows[0] ?? null;
        $topLine = $top
            ? sprintf('waaronder %d %s', $top['count'], Str::lower($top['nl']))
            : 'met geverifieerde weidevogelwaarnemingen';

        return sprintf(
            'Trots! %s beschermt %s m² Friese weide. Dit broedseizoen telden we er %d soorten weidevogels — %s. 🪶 #weidevogels #Fryslân #biodiversiteit',
            $company,
            number_format($adoptedM2, 0, ',', '.'),
            $totals['species'],
            $topLine
        );
    }

    private function parseIntLabel(mixed $value): int
    {
        if (is_numeric($value)) {
            return max(1, (int) $value);
        }

        if (is_string($value) && preg_match('/\d+/', $value, $match)) {
            return max(1, (int) $match[0]);
        }

        return 1;
    }

    private function parseCountHint(string $text, string $keyword): ?int
    {
        $pattern = '/(\d+)\s*'.$keyword.'/iu';

        if (preg_match($pattern, $text, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    private function trendLabel(?string $trend, int $delta): string
    {
        return match ($trend) {
            'up' => '▲ +'.abs($delta),
            'down' => '▼ −'.abs($delta),
            'same' => '= gelijk',
            'new' => 'nieuw',
            default => '—',
        };
    }

    private function trendClass(?string $trend): string
    {
        return match ($trend) {
            'up', 'down', 'same', 'new' => $trend,
            default => '',
        };
    }
}
