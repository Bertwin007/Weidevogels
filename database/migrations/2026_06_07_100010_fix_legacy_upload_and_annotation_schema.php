<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('observations') && Schema::hasColumn('observations', 'status')) {
            DB::statement("ALTER TABLE `observations` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'pending'");

            DB::table('observations')
                ->where('status', 'pending_annotation')
                ->update(['status' => 'pending']);
        }

        if (
            Schema::hasTable('observations')
            && Schema::hasColumn('observations', 'image_path')
            && ! Schema::hasColumn('observations', 'photo_path')
        ) {
            DB::statement('ALTER TABLE `observations` CHANGE `image_path` `photo_path` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('observations') && Schema::hasColumn('observations', 'user_id')) {
            DB::statement('ALTER TABLE `observations` MODIFY `user_id` BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('annotations')) {
            $nullableColumns = [
                'youth_story_line',
                'public_story',
                'caption',
                'story_line',
            ];

            foreach ($nullableColumns as $column) {
                if (! Schema::hasColumn('annotations', $column)) {
                    continue;
                }

                DB::statement("ALTER TABLE `annotations` MODIFY `{$column}` TEXT NULL");
            }

            if (Schema::hasColumn('annotations', 'count_label')) {
                DB::statement('ALTER TABLE `annotations` MODIFY `count_label` VARCHAR(255) NULL');
            }
        }
    }

    public function down(): void
    {
        //
    }
};
