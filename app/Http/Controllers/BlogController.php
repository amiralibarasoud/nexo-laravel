<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = BlogPost::published()
            ->with('category', 'author')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = BlogCategory::where('is_active', true)
            ->withCount(['publishedPosts'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Blog/Index', [
            'posts'      => $posts->through(fn($p) => $this->transformPost($p)),
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): Response
    {
        $post = BlogPost::published()
            ->with('category', 'author')
            ->where('slug', $slug)
            ->firstOrFail();

        $post->incrementViews();

        $related = BlogPost::published()
            ->with('category')
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn($p) => $this->transformPost($p));

        return Inertia::render('Blog/Show', [
            'post'    => [
                'id'                   => $post->id,
                'title'                => $post->title,
                'slug'                 => $post->slug,
                'excerpt'              => $post->excerpt,
                'body'                 => $post->body,
                'cover_image'          => $post->cover_image,
                'meta_title'           => $post->effective_meta_title,
                'meta_description'     => $post->effective_meta_description,
                'meta_keywords'        => $post->meta_keywords,
                'published_at'         => toJalali($post->published_at),
                'views'                => $post->views,
                'category'             => $post->category ? [
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                    'color' => $post->category->color,
                ] : null,
                'author_name'          => $post->author?->name ?? 'تیم نکسو کورس',
            ],
            'related' => $related,
        ]);
    }

    public function byCategory(string $slug): Response
    {
        $category = BlogCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $posts = BlogPost::published()
            ->with('category', 'author')
            ->where('blog_category_id', $category->id)
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = BlogCategory::where('is_active', true)
            ->withCount(['publishedPosts'])
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Blog/Index', [
            'posts'            => $posts->through(fn($p) => $this->transformPost($p)),
            'categories'       => $categories,
            'active_category'  => $category,
        ]);
    }

    private function transformPost(BlogPost $post): array
    {
        return [
            'id'           => $post->id,
            'title'        => $post->title,
            'slug'         => $post->slug,
            'excerpt'      => $post->excerpt,
            'cover_image'  => $post->cover_image,
            'published_at' => toJalali($post->published_at),
            'views'        => $post->views,
            'category'     => $post->category ? [
                'name'  => $post->category->name,
                'slug'  => $post->category->slug,
                'color' => $post->category->color,
            ] : null,
            'author_name'  => $post->author?->name ?? 'تیم نکسو کورس',
        ];
    }
}
