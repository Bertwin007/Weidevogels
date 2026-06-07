<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->nullableVarchar('observations', [
            'original_filename',
            'mime_type',
            'source',
            'uuid',
            'contributor_type',
        ]);

        if (Schema::hasTable('observations') && Schema::hasColumn('observations', 'file_size')) {
            DB::statement('ALTER TABLE `observations` MODIFY `file_size` BIGINT UNSIGNED NULL');
        }

        $this->nullableVarchar('annotations', [
            'species',
            'behavior',
            'season',
            'count_label',
            'story_line',
            'youth_story_line',
            'public_story',
        ]);
    }

    /**
     * @param  list<string>  $columns
     */
    private function nullableVarchar(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) NULL");
        }
    }

    public function down(): void
    {
        //
    }
};
