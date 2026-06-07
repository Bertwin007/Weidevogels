<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('observations')) {
            return;
        }

        if (Schema::hasColumn('observations', 'image_path') && ! Schema::hasColumn('observations', 'photo_path')) {
            DB::statement('ALTER TABLE `observations` CHANGE `image_path` `photo_path` VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        //
    }
};
