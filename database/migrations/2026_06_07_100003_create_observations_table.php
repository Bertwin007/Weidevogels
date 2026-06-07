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
        Schema::table('observations', function (Blueprint $table) {
            if (! Schema::hasColumn('observations', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('observations', 'guest_email')) {
                $table->string('guest_email')->nullable()->after('guest_name');
            }
            if (! Schema::hasColumn('observations', 'contributor_note')) {
                $table->text('contributor_note')->nullable()->after('photo_path');
            }
            if (! Schema::hasColumn('observations', 'exif_taken_at')) {
                $table->timestamp('exif_taken_at')->nullable()->after('contributor_note');
            }
            if (! Schema::hasColumn('observations', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('status');
            }
            if (! Schema::hasColumn('observations', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
