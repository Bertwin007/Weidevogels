<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
