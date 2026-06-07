<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('observations')) {
            return;
        }

        if (! Schema::hasColumn('observations', 'contributor_type')) {
            Schema::table('observations', function (Blueprint $table) {
                $table->string('contributor_type')->nullable()->default('guest');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
