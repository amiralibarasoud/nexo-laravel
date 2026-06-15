<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $enrollments = Enrollment::with(['course' => fn($q) => $q->withCount('lessons')])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $stats = [
            'enrolled_courses' => $enrollments->count(),
            'completed_lessons' => LessonProgress::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count(),
            'total_orders' => Order::where('user_id', $user->id)
                ->where('status', 'paid')
                ->count(),
        ];

        $recentCourses = $enrollments->take(3)->map(fn($e) => $this->transformEnrollment($e));

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recent_courses' => $recentCourses,
        ]);
    }

    public function myCourses(): Response
    {
        $enrollments = Enrollment::with('course')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $courses = $enrollments->map(fn($e) => $this->transformEnrollment($e));

        return Inertia::render('Dashboard/MyCourses', [
            'courses' => $courses,
        ]);
    }

    public function orders(): Response
    {
        $orders = Order::with('course')
            ->where('user_id', Auth::id())
            ->where('status', 'paid')
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'course_title' => $o->course->title,
                'course_slug' => $o->course->slug,
                'course_cover' => $o->course->cover_image,
                'amount' => $o->amount,
                'content_type' => match($o->content_type) {
                    'text' => 'متنی',
                    'audio' => 'صوتی',
                    'both' => 'متنی و صوتی',
                    default => $o->content_type,
                },
                'created_at' => toJalali($o->created_at),
            ]);

        return Inertia::render('Dashboard/Orders', [
            'orders' => $orders,
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('Dashboard/Profile', [
            'user' => [
                'name' => Auth::user()->name,
                'mobile' => Auth::user()->mobile,
                'email' => Auth::user()->email,
                'avatar' => Auth::user()->avatar,
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
        ], [
            'name.required' => 'نام الزامی است.',
            'email.email' => 'ایمیل معتبر نیست.',
        ]);

        Auth::user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'پروفایل با موفقیت به‌روزرسانی شد.');
    }

    private function transformEnrollment(Enrollment $e): array
    {
        $course = $e->course;

        $progressPercent = 0;
        if ($course && $course->lessons_count > 0) {
            $type = match($e->content_type) {
                'text' => 'text',
                'audio' => 'audio',
                default => 'text',
            };
            $completed = LessonProgress::where('user_id', $e->user_id)
                ->where('course_id', $course->id)
                ->where('type', $type)
                ->where('is_completed', true)
                ->count();
            $progressPercent = (int) round($completed / $course->lessons_count * 100);
        }

        return [
            'id' => $e->id,
            'course_id' => $course?->id,
            'title' => $course?->title,
            'slug' => $course?->slug,
            'cover_image' => $course?->cover_image,
            'instructor_name' => $course?->instructor_name,
            'lessons_count' => $course?->lessons_count ?? 0,
            'content_type' => $e->content_type,
            'content_type_label' => match($e->content_type) {
                'text' => 'متنی',
                'audio' => 'صوتی',
                'both' => 'متنی و صوتی',
                default => $e->content_type,
            },
            'progress_percent' => $progressPercent,
            'enrolled_at' => toJalali($e->enrolled_at),
        ];
    }
}
