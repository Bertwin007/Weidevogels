<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (! Schema::hasColumn('annotations', 'annotator_id') && Schema::hasColumn('annotations', 'user_id')) {
            Schema::table('annotations', function (Blueprint $table) {
                $table->unsignedBigInteger('annotator_id')->nullable();
            });

            DB::table('annotations')
                ->whereNull('annotator_id')
                ->whereNotNull('user_id')
                ->update(['annotator_id' => DB::raw('user_id')]);
        }

        $this->addStringColumn('annotations', 'species');
        $this->addStringColumn('annotations', 'count_label');
        $this->addStringColumn('annotations', 'behavior');
        $this->addStringColumn('annotations', 'season');
        $this->addStringColumn('annotations', 'story_line');
        $this->addTextColumn('annotations', 'caption');
        $this->addBooleanColumn('annotations', 'is_publishable', true);

        if (Schema::hasColumn('annotations', 'count') && Schema::hasColumn('annotations', 'count_label')) {
            DB::table('annotations')
                ->whereNull('count_label')
                ->whereNotNull('count')
                ->update(['count_label' => DB::raw('CAST(count AS CHAR)')]);
        }

        if (Schema::hasColumn('annotations', 'youth_story_line') && Schema::hasColumn('annotations', 'story_line')) {
            DB::table('annotations')
                ->whereNull('story_line')
                ->whereNotNull('youth_story_line')
                ->update(['story_line' => DB::raw('youth_story_line')]);
        }

        if (Schema::hasColumn('annotations', 'public_story') && Schema::hasColumn('annotations', 'story_line')) {
            DB::table('annotations')
                ->whereNull('story_line')
                ->whereNotNull('public_story')
                ->update(['story_line' => DB::raw('public_story')]);
        }
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

    private function addBooleanColumn(string $table, string $column, bool $default): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $default) {
            $blueprint->boolean($column)->default($default);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
