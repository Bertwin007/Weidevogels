<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('donation_clicks')) {
            Schema::create('donation_clicks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('observation_id')->nullable()->constrained()->nullOnDelete();
                $table->string('ip_hash', 64)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (! Schema::hasColumn('donation_clicks', 'observation_id')) {
            Schema::table('donation_clicks', function (Blueprint $table) {
                $table->unsignedBigInteger('observation_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('donation_clicks', 'ip_hash')) {
            Schema::table('donation_clicks', function (Blueprint $table) {
                $table->string('ip_hash', 64)->nullable()->after('observation_id');
            });
        }

        if (! Schema::hasColumn('donation_clicks', 'user_agent')) {
            Schema::table('donation_clicks', function (Blueprint $table) {
                $table->string('user_agent')->nullable()->after('ip_hash');
            });
        }

        if (! Schema::hasColumn('donation_clicks', 'created_at')) {
            Schema::table('donation_clicks', function (Blueprint $table) {
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('donation_clicks', 'project_id')) {
            DB::statement('ALTER TABLE `donation_clicks` MODIFY `project_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        //
    }
};
