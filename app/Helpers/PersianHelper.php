<?php

if (!function_exists('toFarsiNumber')) {
    function toFarsiNumber(string|int|float $number): string
    {
        $farsiDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace(
            range(0, 9),
            $farsiDigits,
            (string) $number
        );
    }
}

if (!function_exists('price')) {
    function price(int|float|null $amount, bool $showUnit = true): string
    {
        $formatted = number_format((float) ($amount ?? 0), 0, '.', '٬');
        $farsi = toFarsiNumber($formatted);
        return $showUnit ? $farsi . ' تومان' : $farsi;
    }
}

if (!function_exists('toJalali')) {
    function toJalali(mixed $date, string $format = 'Y/m/d'): string
    {
        if (!$date) return '—';
        try {
            if (!($date instanceof \Carbon\Carbon)) {
                $date = \Carbon\Carbon::parse($date);
            }
            return \Morilog\Jalali\Jalalian::fromCarbon($date)->format($format);
        } catch (\Throwable $e) {
            return '—';
        }
    }
}

if (!function_exists('toJalaliTime')) {
    function toJalaliTime(mixed $date): string
    {
        return toJalali($date, 'Y/m/d - H:i');
    }
}

if (!function_exists('toJalaliLong')) {
    function toJalaliLong(mixed $date): string
    {
        return toJalali($date, 'j F Y');
    }
}

if (!function_exists('toJalaliAgo')) {
    function toJalaliAgo(mixed $date): string
    {
        if (!$date) return '—';
        try {
            if (!($date instanceof \Carbon\Carbon)) {
                $date = \Carbon\Carbon::parse($date);
            }
            $diff  = (int) $date->diffInMinutes(now());
            if ($diff < 1)  return 'همین الان';
            if ($diff < 60) return toFarsiNumber($diff) . ' دقیقه پیش';
            $hours = (int) $date->diffInHours(now());
            if ($hours < 24) return toFarsiNumber($hours) . ' ساعت پیش';
            $days  = (int) $date->diffInDays(now());
            if ($days < 30) return toFarsiNumber($days) . ' روز پیش';
            return toJalali($date);
        } catch (\Throwable $e) {
            return '—';
        }
    }
}

if (!function_exists('normalizeMobile')) {
    function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/\D/', '', $mobile);
        if (str_starts_with($mobile, '98')) {
            $mobile = '0' . substr($mobile, 2);
        }
        if (!str_starts_with($mobile, '0')) {
            $mobile = '0' . $mobile;
        }
        return $mobile;
    }
}

if (!function_exists('persianToEnglishNumber')) {
    function persianToEnglishNumber(string $text): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $text = str_replace($persian, range(0, 9), $text);
        return str_replace($arabic, range(0, 9), $text);
    }
}

if (!function_exists('formatDuration')) {
    function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return toFarsiNumber($minutes) . ' دقیقه';
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        if ($mins === 0) {
            return toFarsiNumber($hours) . ' ساعت';
        }
        return toFarsiNumber($hours) . ' ساعت و ' . toFarsiNumber($mins) . ' دقیقه';
    }
}
