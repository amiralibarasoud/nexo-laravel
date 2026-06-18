<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'       => $request->user()->id,
                    'name'     => $request->user()->name,
                    'mobile'   => $request->user()->mobile,
                    'email'    => $request->user()->email,
                    'avatar'   => $request->user()->avatar,
                    'is_admin' => $request->user()->is_admin,
                ] : null,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
                'info'    => fn() => $request->session()->get('info'),
            ],
            'jalali_now' => toJalali(now(), 'Y/m/d'),
            'theme'      => fn () => [
                'header'   => Setting::headerConfig(),
                'homepage' => Setting::homepageConfig(),
                'footer'   => Setting::footerConfig(),
                'contact'  => Setting::contactConfig(),
                'about'    => Setting::aboutConfig(),
            ],
        ];
    }
}
