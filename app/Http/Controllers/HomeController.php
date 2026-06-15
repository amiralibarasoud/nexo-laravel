<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredCourses = Course::published()
            ->featured()
            ->with('category')
            ->withCount('enrollments')
            ->limit(6)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'short_description' => $c->short_description,
                'cover_image' => $c->cover_image,
                'instructor_name' => $c->instructor_name,
                'price' => $c->price,
                'discounted_price' => $c->discounted_price,
                'effective_price' => $c->effective_price,
                'is_discounted' => $c->is_discounted,
                'discount_percent' => $c->discount_percent,
                'students_count' => $c->students_count,
                'rating' => $c->rating,
                'duration_minutes' => $c->duration_minutes,
                'has_text' => $c->has_text,
                'has_audio' => $c->has_audio,
                'level_label' => $c->level_label,
                'category' => $c->category ? ['name' => $c->category->name] : null,
            ]);

        $categories = Category::where('is_active', true)
            ->withCount(['courses' => fn($q) => $q->where('status', 'published')])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon', 'color', 'description']);

        $stats = [
            'courses_count' => Course::published()->count(),
            'students_count' => \App\Models\Enrollment::count(),
        ];

        return Inertia::render('Home', [
            'featured_courses' => $featuredCourses,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
