<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Course::published()
            ->with('category')
            ->withCount(['enrollments', 'lessons']);

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->level) {
            $query->where('level', $request->level);
        }

        $courses = $query->latest('published_at')->paginate(12)->withQueryString();

        return Inertia::render('Courses/Index', [
            'courses' => $courses->through(fn($c) => $this->transformCourse($c)),
            'categories' => Category::where('is_active', true)->get(['id', 'name', 'slug', 'icon', 'color']),
            'filters' => $request->only(['category', 'search', 'level']),
        ]);
    }

    public function show(Course $course): Response
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $course->load([
            'category',
            'sections' => fn ($q) => $q->orderBy('sort_order'),
            'lessons' => fn ($q) => $q->orderBy('sort_order'),
            'reviews.user',
        ]);

        // Always keep denormalized lessons_count equal to real lesson rows.
        $realLessonsCount = $course->lessons->count();
        $course->updateQuietly([
            'lessons_count' => $realLessonsCount,
            'students_count' => $course->enrollments()->count(),
        ]);

        $course->loadCount(['enrollments', 'lessons']);

        $isEnrolled = Auth::check() && Auth::user()->isEnrolledIn($course);
        $enrollment = $isEnrolled ? Auth::user()->getEnrollmentFor($course) : null;

        $mapLesson = fn ($l) => [
            'id' => $l->id,
            'title' => $l->title,
            'duration' => $l->audio_duration_formatted,
            'is_preview' => $l->is_preview,
            'has_audio' => (bool) $l->audio_path,
            'has_text' => (bool) $l->text_content,
        ];

        $lessonsBySection = $course->lessons->groupBy(fn ($l) => $l->section_id ?: 0);

        $sections = $course->sections->map(function ($s) use ($lessonsBySection, $mapLesson) {
            $lessons = ($lessonsBySection->get($s->id) ?? collect())->values();

            return [
                'id' => $s->id,
                'title' => $s->title,
                'lessons' => $lessons->map($mapLesson)->values(),
            ];
        })->filter(fn ($s) => count($s['lessons']) > 0)->values();

        $orphanLessons = ($lessonsBySection->get(0) ?? collect())->values();
        if ($orphanLessons->isNotEmpty()) {
            $sections->push([
                'id' => 0,
                'title' => 'جلسات دوره',
                'lessons' => $orphanLessons->map($mapLesson)->values(),
            ]);
        }

        // If course has lessons but no sections at all, still show them.
        if ($sections->isEmpty() && $course->lessons->isNotEmpty()) {
            $sections = collect([[
                'id' => 0,
                'title' => 'سرفصل‌ها',
                'lessons' => $course->lessons->map($mapLesson)->values(),
            ]]);
        }

        return Inertia::render('Courses/Show', [
            'course' => [
                ...$this->transformCourse($course),
                'description' => $course->description,
                'instructor_bio' => $course->instructor_bio,
                'instructor_avatar' => $course->instructor_avatar,
                'lessons_count' => $course->lessons->count(),
                'sections' => $sections,
                'reviews' => $course->reviews->take(5)->map(fn($r) => [
                    'id' => $r->id,
                    'user_name' => $r->user->name,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'created_at' => toJalali($r->created_at),
                ]),
            ],
            'is_enrolled' => $isEnrolled,
            'enrollment' => $enrollment ? [
                'content_type' => $enrollment->content_type,
            ] : null,
        ]);
    }

    public function learn(Course $course): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $enrollment = Auth::user()->getEnrollmentFor($course);

        if (!$enrollment) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'ابتدا باید دوره را خریداری کنید.');
        }

        $course->load([
            'sections' => fn ($q) => $q->orderBy('sort_order'),
            'lessons' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $progress = LessonProgress::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->get()
            ->keyBy(fn($p) => "{$p->lesson_id}_{$p->type}");

        $mapLearnLesson = function ($l) use ($enrollment, $progress) {
            if (!$l->is_published) {
                return null;
            }

            return [
                'id' => $l->id,
                'title' => $l->title,
                'has_text' => (bool) $l->text_content && $enrollment->canAccessText(),
                'has_audio' => (bool) $l->audio_path && $enrollment->canAccessAudio(),
                'duration' => $l->audio_duration_formatted,
                'text_completed' => $progress->has("{$l->id}_text") && $progress["{$l->id}_text"]->is_completed,
                'audio_completed' => $progress->has("{$l->id}_audio") && $progress["{$l->id}_audio"]->is_completed,
                'audio_position' => $progress->has("{$l->id}_audio") ? $progress["{$l->id}_audio"]->audio_position_seconds : 0,
            ];
        };

        $lessonsBySection = $course->lessons->groupBy(fn ($l) => $l->section_id ?: 0);

        $sections = $course->sections->map(function ($s) use ($lessonsBySection, $mapLearnLesson) {
            $lessons = ($lessonsBySection->get($s->id) ?? collect())
                ->map($mapLearnLesson)
                ->filter()
                ->values();

            return [
                'id' => $s->id,
                'title' => $s->title,
                'lessons' => $lessons,
            ];
        })->filter(fn ($s) => count($s['lessons']) > 0)->values();

        $orphanLessons = ($lessonsBySection->get(0) ?? collect())
            ->map($mapLearnLesson)
            ->filter()
            ->values();

        if ($orphanLessons->isNotEmpty()) {
            $sections->push([
                'id' => 0,
                'title' => 'جلسات دوره',
                'lessons' => $orphanLessons,
            ]);
        }

        if ($sections->isEmpty()) {
            $sections = collect([[
                'id' => 0,
                'title' => 'سرفصل‌ها',
                'lessons' => $course->lessons->map($mapLearnLesson)->filter()->values(),
            ]]);
        }

        return Inertia::render('Courses/Learn', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'cover_image' => $course->cover_image,
                'instructor_name' => $course->instructor_name,
                'sections' => $sections,
            ],
            'enrollment' => [
                'content_type' => $enrollment->content_type,
                'can_access_text' => $enrollment->canAccessText(),
                'can_access_audio' => $enrollment->canAccessAudio(),
            ],
        ]);
    }

    public function getLessonContent(Request $request, Course $course, int $lessonId): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $enrollment = Auth::user()->getEnrollmentFor($course);

        if (!$enrollment) {
            return response()->json(['error' => 'Not enrolled'], 403);
        }

        $lesson = $course->lessons()->findOrFail($lessonId);

        $content = [];

        if ($request->type === 'text' && $enrollment->canAccessText() && $lesson->text_content) {
            $content = ['type' => 'text', 'content' => $lesson->text_content];
        } elseif ($request->type === 'audio' && $enrollment->canAccessAudio() && $lesson->audio_path) {
            // Generate a signed temporary URL to prevent direct download
            $content = [
                'type' => 'audio',
                'stream_url' => route('lessons.audio.stream', ['course' => $course->id, 'lesson' => $lesson->id]),
            ];
        } else {
            return response()->json(['error' => 'Content not available'], 403);
        }

        return response()->json($content);
    }

    public function streamAudio(Course $course, int $lessonId)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $enrollment = Auth::user()->getEnrollmentFor($course);

        if (!$enrollment || !$enrollment->canAccessAudio()) {
            abort(403);
        }

        $lesson = $course->lessons()->findOrFail($lessonId);

        if (!$lesson->audio_path) {
            abort(404);
        }

        $path = storage_path('app/private/' . $lesson->audio_path);

        if (!file_exists($path)) {
            abort(404);
        }

        $size = filesize($path);
        $start = 0;
        $end = $size - 1;
        $status = 200;
        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-store, no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if (request()->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', request()->header('Range'), $matches);
            $start = (int) $matches[1];
            $end = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : $size - 1;
            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $headers['Content-Length'] = $end - $start + 1;

        return response()->stream(function () use ($path, $start, $end) {
            $fp = fopen($path, 'rb');
            fseek($fp, $start);
            $remaining = $end - $start + 1;
            while ($remaining > 0 && !feof($fp)) {
                $chunk = fread($fp, min(8192, $remaining));
                echo $chunk;
                $remaining -= strlen($chunk);
                flush();
            }
            fclose($fp);
        }, $status, $headers);
    }

    public function updateProgress(Request $request, Course $course): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'type' => 'required|in:text,audio',
            'is_completed' => 'boolean',
            'audio_position' => 'integer|min:0',
        ]);

        LessonProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lesson_id' => $request->lesson_id,
                'type' => $request->type,
            ],
            [
                'course_id' => $course->id,
                'is_completed' => $request->boolean('is_completed'),
                'audio_position_seconds' => $request->input('audio_position', 0),
                'completed_at' => $request->boolean('is_completed') ? now() : null,
            ]
        );

        return response()->json(['success' => true]);
    }

    private function transformCourse(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'short_description' => $course->short_description,
            'cover_image' => $course->cover_image,
            'instructor_name' => $course->instructor_name,
            'price' => $course->starting_price,
            'discounted_price' => $course->discounted_price,
            'effective_price' => $course->starting_effective_price,
            'is_discounted' => $course->is_discounted,
            'discount_percent' => $course->discount_percent,
            'has_variable_pricing' => $course->has_variable_pricing,
            'content_type_prices' => $course->getContentTypePrices(),
            'has_text' => $course->has_text,
            'has_audio' => $course->has_audio,
            'students_count' => $course->resolveStudentsCount(),
            'duration_minutes' => $course->duration_minutes,
            'lessons_count' => $course->resolveLessonsCount(),
            'rating' => $course->rating,
            'ratings_count' => $course->ratings_count,
            'level' => $course->level,
            'level_label' => $course->level_label,
            'is_featured' => $course->is_featured,
            'category' => $course->category ? ['name' => $course->category->name, 'slug' => $course->category->slug] : null,
        ];
    }
}
