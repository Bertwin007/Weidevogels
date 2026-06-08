<?php

namespace App\Jobs;

use App\Models\Observation;
use App\Services\AiPreScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeObservationPhoto implements ShouldQueue
{
    use Queueable;

    public function __construct(public Observation $observation) {}

    public function handle(AiPreScanService $scanner): void
    {
        try {
            $scanner->analyze($this->observation->fresh());
        } catch (\Throwable $e) {
            Log::error('AI-voorscan job mislukt: '.$e->getMessage(), [
                'observation_id' => $this->observation->id,
                'exception' => $e,
            ]);
        }
    }
}
