<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('Pages/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Pages/Contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'mobile'  => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required'    => 'نام الزامی است.',
            'mobile.required'  => 'شماره موبایل الزامی است.',
            'mobile.regex'     => 'شماره موبایل معتبر نیست.',
            'subject.required' => 'موضوع الزامی است.',
            'message.required' => 'متن پیام الزامی است.',
            'message.min'      => 'پیام باید حداقل ۱۰ کاراکتر باشد.',
        ]);

        Log::info('Contact form submitted', $request->only(['name', 'mobile', 'subject']));

        return back()->with('success', Setting::get('contact_form_success_message', 'پیام شما دریافت شد. به زودی با شما تماس می‌گیریم.'));
    }

    public function terms(): Response
    {
        return Inertia::render('Pages/Terms');
    }
}
