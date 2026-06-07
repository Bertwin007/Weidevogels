<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app_key' => (bool) config('app.key'),
            'db' => false,
            'storage_writable' => is_writable(storage_path('logs')),
            'sessions_writable' => is_writable(storage_path('framework/sessions')),
            'views_writable' => is_writable(storage_path('framework/views')),
            'public_storage_link' => is_link(public_path('storage')) || is_dir(public_path('storage')),
        ];

        try {
            DB::connection()->getPdo();
            DB::select('select 1 as ok');
            $checks['db'] = true;
        } catch (\Throwable) {
            $checks['db'] = false;
        }

        $checks['log_tail'] = $this->lastLogLines();

        $ok = $checks['app_key'] && $checks['db'] && $checks['storage_writable'];

        return response()->json([
            'ok' => $ok,
            'checks' => $checks,
        ], $ok ? 200 : 500);
    }

    /**
     * @return list<string>
     */
    private function lastLogLines(): array
    {
        $log = storage_path('logs/laravel.log');

        if (! File::exists($log)) {
            return [];
        }

        $lines = file($log, FILE_IGNORE_NEW_LINES) ?: [];

        return array_slice($lines, -8);
    }
}
