<?php

use App\Http\Controllers\Auth\OtpAuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth - OTP
Route::middleware('guest')->group(function () {
    Route::get('/login', [OtpAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/send-otp', [OtpAuthController::class, 'sendOtp'])->name('auth.send-otp');
    Route::post('/auth/verify-otp', [OtpAuthController::class, 'verifyOtp'])->name('auth.verify-otp');
});

Route::post('/logout', [OtpAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Courses - public
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

// Courses - auth required
Route::middleware('auth')->group(function () {
    Route::get('/courses/{course:slug}/learn', [CourseController::class, 'learn'])->name('courses.learn');
    Route::get('/courses/{course}/lessons/{lessonId}/content', [CourseController::class, 'getLessonContent'])->name('lessons.content');
    Route::get('/courses/{course}/lessons/{lessonId}/audio', [CourseController::class, 'streamAudio'])->name('lessons.audio.stream');
    Route::post('/courses/{course}/progress', [CourseController::class, 'updateProgress'])->name('courses.progress');

    // Payment
    Route::get('/checkout/{course:slug}', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::post('/coupon/validate', [CouponController::class, 'validate'])->name('coupon.validate');

    // User Dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [UserDashboardController::class, 'index'])->name('index');
        Route::get('/my-courses', [UserDashboardController::class, 'myCourses'])->name('my-courses');
        Route::get('/orders', [UserDashboardController::class, 'orders'])->name('orders');
        Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
        Route::post('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
    });
});

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'byCategory'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Static pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Payment callback (no auth middleware - gateway redirect)
Route::get('/payment/callback/{gateway}/{order}', [PaymentController::class, 'callback'])->name('payment.callback');
