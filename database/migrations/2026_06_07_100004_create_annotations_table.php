<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('annotations')) {
            $this->upgradeExistingTable();

            return;
        }

        Schema::create('annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('annotator_id')->constrained('users')->cascadeOnDelete();
            $table->string('species');
            $table->string('count_label');
            $table->string('behavior');
            $table->string('season');
            $table->string('story_line');
            $table->text('caption')->nullable();
            $table->boolean('is_publishable')->default(true);
            $table->timestamps();
        });
    }

    private function upgradeExistingTable(): void
    {
        Schema::table('annotations', function (Blueprint $table) {
            if (! Schema::hasColumn('annotations', 'is_publishable')) {
                $table->boolean('is_publishable')->default(true)->after('caption');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
