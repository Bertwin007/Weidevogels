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

        Schema::table('observations', function (Blueprint $table) {
            if (! Schema::hasColumn('observations', 'ai_species')) {
                $table->string('ai_species')->nullable();
            }
            if (! Schema::hasColumn('observations', 'ai_count')) {
                $table->unsignedInteger('ai_count')->nullable();
            }
            if (! Schema::hasColumn('observations', 'ai_behavior')) {
                $table->string('ai_behavior')->nullable();
            }
            if (! Schema::hasColumn('observations', 'ai_season')) {
                $table->string('ai_season')->nullable();
            }
            if (! Schema::hasColumn('observations', 'ai_confidence')) {
                $table->unsignedTinyInteger('ai_confidence')->nullable();
            }
            if (! Schema::hasColumn('observations', 'ai_notes')) {
                $table->text('ai_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
