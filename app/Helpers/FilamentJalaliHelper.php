<?php

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

/**
 * ایجاد DateTimePicker با نمایش و ورودی تاریخ شمسی
 * کاربر تاریخ شمسی تایپ می‌کند، سیستم به میلادی ذخیره می‌کند
 */
if (!function_exists('jalaliDatePicker')) {
    function jalaliDatePicker(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->placeholder('مثال: ۱۴۰۵/۰۴/۱۵ ۱۲:۰۰')
            ->helperText('تاریخ شمسی وارد کنید (YYYY/MM/DD)')
            ->afterStateHydrated(function ($state, $set) use ($name) {
                if (!$state) return;
                try {
                    $carbon = $state instanceof \Carbon\Carbon ? $state : \Carbon\Carbon::parse($state);
                    $jalali = Jalalian::fromCarbon($carbon);
                    $set($name, $jalali->format('Y/m/d H:i'));
                } catch (\Throwable $e) {
                    // keep as is
                }
            })
            ->dehydrateStateUsing(function ($state) {
                if (!$state) return null;
                try {
                    // تبدیل عدد فارسی به انگلیسی
                    $state = persianToEnglishNumber($state);
                    // parse YYYY/MM/DD or YYYY/MM/DD HH:ii
                    preg_match('/(\d{4})\/(\d{1,2})\/(\d{1,2})(?:\s+(\d{1,2}):(\d{2}))?/', $state, $m);
                    if (empty($m)) return null;
                    $jy = (int) $m[1];
                    $jm = (int) $m[2];
                    $jd = (int) $m[3];
                    $h  = isset($m[4]) ? (int) $m[4] : 0;
                    $i  = isset($m[5]) ? (int) $m[5] : 0;
                    [$gy, $gm, $gd] = CalendarUtils::toGregorian($jy, $jm, $jd);
                    return \Carbon\Carbon::create($gy, $gm, $gd, $h, $i, 0);
                } catch (\Throwable $e) {
                    return null;
                }
            });
    }
}
