<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('courses', 'price_text')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedBigInteger('price_text')->nullable()->after('price');
            });
        }

        if (!Schema::hasColumn('courses', 'price_audio')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedBigInteger('price_audio')->nullable()->after('price_text');
            });
        }

        if (!Schema::hasColumn('courses', 'price_both')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedBigInteger('price_both')->nullable()->after('price_audio');
            });
        }

        DB::table('courses')
            ->where(function ($query) {
                $query->whereNull('price_text')
                    ->orWhereNull('price_audio')
                    ->orWhereNull('price_both');
            })
            ->orderBy('id')
            ->chunkById(100, function ($courses) {
                foreach ($courses as $course) {
                    DB::table('courses')->where('id', $course->id)->update([
                        'price_text'  => $course->price_text ?? $course->price,
                        'price_audio' => $course->price_audio ?? $course->price,
                        'price_both'  => $course->price_both ?? $course->price,
                    ]);
                }
            });
    }

    public function down(): void
    {
        $columns = collect(['price_text', 'price_audio', 'price_both'])
            ->filter(fn (string $column) => Schema::hasColumn('courses', $column))
            ->values()
            ->all();

        if (!empty($columns)) {
            Schema::table('courses', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
