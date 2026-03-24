<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $nullCount = DB::table('students')->whereNull('class_group_id')->count();

        if ($nullCount > 0) {
            throw new RuntimeException(
                "Cannot enforce NOT NULL on students.class_group_id because {$nullCount} row(s) still have NULL values. " .
                "Please assign class_group_id for all students first."
            );
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE students MODIFY class_group_id BIGINT UNSIGNED NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE students ALTER COLUMN class_group_id SET NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            // SQLite does not support ALTER COLUMN SET NOT NULL directly.
            // Keep as-is for local sqlite/testing environments.
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE students MODIFY class_group_id BIGINT UNSIGNED NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE students ALTER COLUMN class_group_id DROP NOT NULL');
            return;
        }

        if ($driver === 'sqlite') {
            // SQLite does not support ALTER COLUMN DROP NOT NULL directly.
            // Keep as-is for local sqlite/testing environments.
            return;
        }
    }
};
