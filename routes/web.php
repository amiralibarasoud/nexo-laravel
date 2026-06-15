<?php

use App\Http\Controllers\Auth\OtpAuthController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Payment\PaymentController;
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

    // Dashboard
    Route::get('/dashboard', fn() => \Inertia\Inertia::render('Dashboard'))->name('dashboard');
});

// Payment callback (no auth middleware - gateway redirect)
Route::get('/payment/callback/{gateway}/{order}', [PaymentController::class, 'callback'])->name('payment.callback');
