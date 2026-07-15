<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('price_text')->nullable()->after('price');
            $table->unsignedBigInteger('price_audio')->nullable()->after('price_text');
            $table->unsignedBigInteger('price_both')->nullable()->after('price_audio');
        });

        DB::table('courses')->orderBy('id')->chunkById(100, function ($courses) {
            foreach ($courses as $course) {
                DB::table('courses')->where('id', $course->id)->update([
                    'price_text'  => $course->price,
                    'price_audio' => $course->price,
                    'price_both'  => $course->price,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['price_text', 'price_audio', 'price_both']);
        });
    }
};
