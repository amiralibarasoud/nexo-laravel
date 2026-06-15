<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'مدیر سیستم',
            'mobile' => '09120000000',
            'email' => 'admin@nexocourse.ir',
            'is_admin' => true,
            'is_active' => true,
            'mobile_verified_at' => now(),
            'password' => bcrypt('Admin@12345'),
        ]);

        // Create categories
        $categories = [
            ['name' => 'توسعه وب', 'slug' => 'web-development', 'icon' => '💻', 'color' => '#3b82f6'],
            ['name' => 'موبایل', 'slug' => 'mobile', 'icon' => '📱', 'color' => '#8b5cf6'],
            ['name' => 'هوش مصنوعی', 'slug' => 'ai', 'icon' => '🤖', 'color' => '#ec4899'],
            ['name' => 'طراحی', 'slug' => 'design', 'icon' => '🎨', 'color' => '#f59e0b'],
            ['name' => 'کسب و کار', 'slug' => 'business', 'icon' => '💼', 'color' => '#10b981'],
            ['name' => 'زبان', 'slug' => 'language', 'icon' => '🌍', 'color' => '#6366f1'],
        ];

        foreach ($categories as $cat) {
            Category::create([...$cat, 'is_active' => true, 'sort_order' => 0]);
        }

        $webCat = Category::where('slug', 'web-development')->first();

        // Create a sample course
        $course = Course::create([
            'category_id' => $webCat->id,
            'title' => 'آموزش لاراول از صفر تا صد',
            'slug' => 'laravel-from-zero',
            'short_description' => 'یادگیری جامع فریم‌ورک لاراول PHP به همراه محتوای متنی و صوتی کامل',
            'description' => '<p>در این دوره جامع، تمام مفاهیم لاراول را از مبتدی تا پیشرفته یاد می‌گیری. این دوره هم به صورت متنی و هم به صورت صوتی در دسترس است.</p>',
            'instructor_name' => 'استاد محمد احمدی',
            'price' => 350000,
            'discounted_price' => 249000,
            'discount_expires_at' => now()->addDays(30),
            'has_text' => true,
            'has_audio' => true,
            'level' => 'beginner',
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now(),
            'lessons_count' => 3,
            'duration_minutes' => 45,
        ]);

        // Create sections
        $section1 = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'فصل اول: آشنایی با لاراول',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section1->id,
            'title' => 'معرفی لاراول و نصب',
            'sort_order' => 1,
            'text_content' => '<h2>لاراول چیست؟</h2><p>لاراول یک فریم‌ورک PHP است که توسط Taylor Otwell در سال ۲۰۱۱ ساخته شد...</p>',
            'audio_duration_seconds' => 600,
            'is_preview' => true,
            'is_published' => true,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section1->id,
            'title' => 'ساختار پروژه لاراول',
            'sort_order' => 2,
            'text_content' => '<h2>ساختار دایرکتوری</h2><p>یک پروژه لاراول شامل دایرکتوری‌های مختلفی است...</p>',
            'audio_duration_seconds' => 900,
            'is_preview' => false,
            'is_published' => true,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'section_id' => $section1->id,
            'title' => 'مسیریابی در لاراول',
            'sort_order' => 3,
            'text_content' => '<h2>Routes در لاراول</h2><p>سیستم مسیریابی لاراول بسیار قدرتمند است...</p>',
            'audio_duration_seconds' => 1200,
            'is_preview' => false,
            'is_published' => true,
        ]);
    }
}
