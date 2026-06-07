<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects') || ! Schema::hasColumn('projects', 'location')) {
            return;
        }

        DB::statement('ALTER TABLE `projects` MODIFY `location` VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        //
    }
};
