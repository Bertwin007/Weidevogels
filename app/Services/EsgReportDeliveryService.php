<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EsgReportDeliveryService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function log(string $company, string $email, array $data, int $adminUserId): void
    {
        $entry = [
            'at' => now()->toIso8601String(),
            'company' => $company,
            'email' => $email,
            'report_nr' => $data['report']['nr'],
            'season' => $data['report']['season'],
            'observation_ids' => $data['observation_ids'] ?? [],
            'sent_by' => $adminUserId,
        ];

        Log::info('ESG-rapport verstuurd naar partner', $entry);

        $logFile = storage_path('logs/esg-report-deliveries.log');

        file_put_contents(
            $logFile,
            json_encode($entry, JSON_UNESCAPED_UNICODE).PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
