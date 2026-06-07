<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('observations')) {
            $this->upgradeExistingTable();

            return;
        }

        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('photo_path');
            $table->text('contributor_note')->nullable();
            $table->timestamp('exif_taken_at')->nullable();
            $table->string('status')->default('pending_annotation');
            $table->string('slug')->nullable()->unique();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    private function upgradeExistingTable(): void
    {
        $this->addStringColumn('observations', 'guest_name');
        $this->addStringColumn('observations', 'guest_email');
        $this->addTextColumn('observations', 'contributor_note');
        $this->addTimestampColumn('observations', 'exif_taken_at');
        $this->addStringColumn('observations', 'slug');
        $this->addTimestampColumn('observations', 'published_at');
    }

    private function addStringColumn(string $table, string $column): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->string($column)->nullable();
        });
    }

    private function addTextColumn(string $table, string $column): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->text($column)->nullable();
        });
    }

    private function addTimestampColumn(string $table, string $column): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->timestamp($column)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
