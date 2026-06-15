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
            ->withCount('enrollments');

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

        $course->load(['category', 'sections.lessons', 'reviews.user']);

        $isEnrolled = Auth::check() && Auth::user()->isEnrolledIn($course);
        $enrollment = $isEnrolled ? Auth::user()->getEnrollmentFor($course) : null;

        return Inertia::render('Courses/Show', [
            'course' => [
                ...$this->transformCourse($course),
                'description' => $course->description,
                'instructor_bio' => $course->instructor_bio,
                'instructor_avatar' => $course->instructor_avatar,
                'sections' => $course->sections->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'lessons' => $s->lessons->map(fn($l) => [
                        'id' => $l->id,
                        'title' => $l->title,
                        'duration' => $l->audio_duration_formatted,
                        'is_preview' => $l->is_preview,
                        'has_audio' => (bool) $l->audio_path,
                        'has_text' => (bool) $l->text_content,
                    ]),
                ]),
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

        $course->load(['sections.lessons']);

        $progress = LessonProgress::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->get()
            ->keyBy(fn($p) => "{$p->lesson_id}_{$p->type}");

        return Inertia::render('Courses/Learn', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'cover_image' => $course->cover_image,
                'instructor_name' => $course->instructor_name,
                'sections' => $course->sections->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'lessons' => $s->lessons->filter(fn($l) => $l->is_published)->map(fn($l) => [
                        'id' => $l->id,
                        'title' => $l->title,
                        'has_text' => (bool) $l->text_content && $enrollment->canAccessText(),
                        'has_audio' => (bool) $l->audio_path && $enrollment->canAccessAudio(),
                        'duration' => $l->audio_duration_formatted,
                        'text_completed' => $progress->has("{$l->id}_text") && $progress["{$l->id}_text"]->is_completed,
                        'audio_completed' => $progress->has("{$l->id}_audio") && $progress["{$l->id}_audio"]->is_completed,
                        'audio_position' => $progress->has("{$l->id}_audio") ? $progress["{$l->id}_audio"]->audio_position_seconds : 0,
                    ])->values(),
                ]),
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
            'price' => $course->price,
            'discounted_price' => $course->discounted_price,
            'effective_price' => $course->effective_price,
            'is_discounted' => $course->is_discounted,
            'discount_percent' => $course->discount_percent,
            'has_text' => $course->has_text,
            'has_audio' => $course->has_audio,
            'students_count' => $course->students_count,
            'duration_minutes' => $course->duration_minutes,
            'lessons_count' => $course->lessons_count,
            'rating' => $course->rating,
            'ratings_count' => $course->ratings_count,
            'level' => $course->level,
            'level_label' => $course->level_label,
            'is_featured' => $course->is_featured,
            'category' => $course->category ? ['name' => $course->category->name, 'slug' => $course->category->slug] : null,
        ];
    }
}
