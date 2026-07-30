<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE courses MODIFY students_count INT UNSIGNED NOT NULL DEFAULT 40');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE courses ALTER COLUMN students_count SET DEFAULT 40');
        } elseif ($driver === 'sqlite') {
            // SQLite cannot reliably change column defaults; model default covers new rows.
        }

        Course::query()->withCount('enrollments')->each(function (Course $course) {
            $course->updateQuietly([
                'students_count' => Course::BASE_STUDENTS_COUNT + (int) $course->enrollments_count,
            ]);
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE courses MODIFY students_count INT UNSIGNED NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE courses ALTER COLUMN students_count SET DEFAULT 0');
        }

        Course::query()->withCount('enrollments')->each(function (Course $course) {
            $course->updateQuietly([
                'students_count' => (int) $course->enrollments_count,
            ]);
        });
    }
};
