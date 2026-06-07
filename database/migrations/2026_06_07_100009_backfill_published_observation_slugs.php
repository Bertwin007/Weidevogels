<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('observations') || ! Schema::hasColumn('observations', 'slug')) {
            return;
        }

        $rows = DB::table('observations')
            ->whereIn('status', ['published', 'approved'])
            ->whereNull('slug')
            ->get(['id']);

        foreach ($rows as $row) {
            $storyLine = null;

            if (Schema::hasTable('annotations')) {
                $storyLine = DB::table('annotations')
                    ->where('observation_id', $row->id)
                    ->value('story_line');
            }

            $base = is_string($storyLine) ? Str::slug(Str::limit($storyLine, 40, '')) : '';
            $slug = $base !== '' ? $base.'-'.$row->id : 'moment-'.$row->id;

            $update = ['slug' => $slug];

            if (Schema::hasColumn('observations', 'published_at')) {
                DB::table('observations')
                    ->where('id', $row->id)
                    ->whereNull('published_at')
                    ->update(['published_at' => now()]);
            }

            DB::table('observations')->where('id', $row->id)->update($update);
        }
    }

    public function down(): void
    {
        //
    }
};
