<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        if (! Schema::hasColumn('projects', 'description')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }

        if (! Schema::hasColumn('projects', 'active')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('active')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'active')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }
    }
};
