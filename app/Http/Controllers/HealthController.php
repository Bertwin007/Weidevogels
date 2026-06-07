<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Services\LegacyRecordMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app_key' => (bool) config('app.key'),
            'db' => false,
            'legacy_mapper' => class_exists(LegacyRecordMapper::class),
            'storage_writable' => is_writable(storage_path('logs')),
            'storage_app_writable' => is_writable(storage_path('app/public')),
            'sessions_writable' => is_writable(storage_path('framework/sessions')),
            'views_writable' => is_writable(storage_path('framework/views')),
            'public_storage_link' => is_link(public_path('storage')) || is_dir(public_path('storage')),
            'pending_uploads' => 0,
            'last_error' => $this->lastErrorMessage(),
        ];

        try {
            DB::connection()->getPdo();
            DB::select('select 1 as ok');
            $checks['db'] = true;
            $checks['pending_uploads'] = Observation::pendingAnnotation()->count();

            if (Schema::hasTable('observations')) {
                $checks['observation_columns'] = Schema::getColumnListing('observations');
            }
            if (Schema::hasTable('annotations')) {
                $checks['annotation_columns'] = Schema::getColumnListing('annotations');
            }
        } catch (\Throwable $e) {
            $checks['db_error'] = $e->getMessage();
        }

        $ok = $checks['app_key'] && $checks['db'] && $checks['storage_writable'] && $checks['legacy_mapper'];

        return response()->json([
            'ok' => $ok,
            'checks' => $checks,
        ], $ok ? 200 : 500);
    }

    private function lastErrorMessage(): ?string
    {
        $log = storage_path('logs/laravel.log');

        if (! File::exists($log)) {
            return null;
        }

        $content = File::get($log);
        $pos = strrpos($content, '.ERROR:');

        if ($pos === false) {
            return null;
        }

        $start = strrpos(substr($content, 0, $pos), "\n");
        $start = $start === false ? 0 : $start + 1;

        return trim(substr($content, $start, 1200));
    }
}
