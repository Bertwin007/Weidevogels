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

        if (! Schema::hasTable('annotations')) {
            return;
        }

        $this->nullableVarchar('annotations', [
            'title',
            'sponsor_hook',
        ]);

        $this->nullableText('annotations', [
            'story',
            'youth_line',
        ]);

        if (Schema::hasColumn('annotations', 'quality_score')) {
            DB::statement('ALTER TABLE `annotations` MODIFY `quality_score` TINYINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('annotations', 'publishable')) {
            DB::statement('ALTER TABLE `annotations` MODIFY `publishable` TINYINT(1) NOT NULL DEFAULT 1');
        }

        if (Schema::hasColumn('annotations', 'is_highlight')) {
            DB::statement('ALTER TABLE `annotations` MODIFY `is_highlight` TINYINT(1) NOT NULL DEFAULT 0');
        }

        if (Schema::hasColumn('annotations', 'annotated_at')) {
            DB::statement('ALTER TABLE `annotations` MODIFY `annotated_at` TIMESTAMP NULL');
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function nullableVarchar(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) NULL");
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function nullableText(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TEXT NULL");
        }
    }

    public function down(): void
    {
        //
    }
};
