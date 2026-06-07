<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            $this->upgradeExistingTable();

            return;
        }

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    private function upgradeExistingTable(): void
    {
        if (! Schema::hasColumn('projects', 'name')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('name')->nullable();
            });
        }

        if (! Schema::hasColumn('projects', 'slug')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('slug')->nullable();
            });
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
        Schema::dropIfExists('projects');
    }
};
