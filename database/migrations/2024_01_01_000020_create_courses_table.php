<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('preview_video')->nullable();
            $table->string('instructor_name')->nullable();
            $table->string('instructor_avatar')->nullable();
            $table->text('instructor_bio')->nullable();

            // Pricing
            $table->unsignedBigInteger('price')->default(0); // in Toman
            $table->unsignedBigInteger('discounted_price')->nullable();
            $table->timestamp('discount_expires_at')->nullable();

            // Content types available
            $table->boolean('has_text')->default(true);
            $table->boolean('has_audio')->default(false);

            // Stats
            $table->unsignedInteger('students_count')->default(0);
            $table->unsignedInteger('duration_minutes')->default(0); // total audio minutes
            $table->unsignedInteger('lessons_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);

            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'all'])->default('all');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('course_sections')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);

            // Text content
            $table->longText('text_content')->nullable();

            // Audio content - stored on server, no direct download
            $table->string('audio_path')->nullable();
            $table->unsignedInteger('audio_duration_seconds')->nullable();

            $table->boolean('is_preview')->default(false); // free preview lesson
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('course_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_tags');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('course_sections');
        Schema::dropIfExists('courses');
    }
};
