<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency('daily'))
            ->add(Url::create('/courses')->setPriority(0.9)->setChangeFrequency('daily'))
            ->add(Url::create('/about')->setPriority(0.5))
            ->add(Url::create('/contact')->setPriority(0.5))
            ->add(Url::create('/terms')->setPriority(0.3));

        Course::published()->get()->each(function (Course $course) use ($sitemap) {
            $sitemap->add(
                Url::create("/courses/{$course->slug}")
                    ->setLastModificationDate($course->updated_at)
                    ->setPriority(0.8)
                    ->setChangeFrequency('weekly')
            );
        });

        return response($sitemap->render(), 200, ['Content-Type' => 'application/xml']);
    }
}
