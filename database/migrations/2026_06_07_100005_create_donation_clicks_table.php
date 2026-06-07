<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash', 64);
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_clicks');
    }
};
