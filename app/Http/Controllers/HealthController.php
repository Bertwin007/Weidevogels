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
            'public_storage_is_symlink' => is_link(public_path('storage')),
            'public_storage_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : null,
            'pending_uploads' => 0,
            'pending_photos' => [],
            'last_error' => $this->lastErrorMessage(),
        ];

        try {
            DB::connection()->getPdo();
            DB::select('select 1 as ok');
            $checks['db'] = true;
            $checks['pending_uploads'] = Observation::pendingAnnotation()->count();
            $checks['pending_photos'] = Observation::query()
                ->pendingAnnotation()
                ->latest()
                ->limit(5)
                ->get(['id', 'photo_path', 'thumbnail_path'])
                ->map(fn (Observation $observation) => [
                    'id' => $observation->id,
                    'photo_path' => $observation->getAttributes()['photo_path'] ?? null,
                    'stored_path' => $observation->storedPhotoPath(),
                    'file_exists' => $observation->photoExistsOnDisk(),
                    'public_url' => $observation->photo_url,
                ])
                ->all();

            if (Schema::hasTable('observations')) {
                $checks['observation_columns'] = Schema::getColumnListing('observations');
            }
            if (Schema::hasTable('annotations')) {
                $checks['annotation_columns'] = Schema::getColumnListing('annotations');
            }
            if (Schema::hasTable('donation_clicks')) {
                $checks['donation_click_columns'] = Schema::getColumnListing('donation_clicks');
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
