<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SmsSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'تنظیمات پیامک';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort     = 10;
    protected static string $view             = 'filament.pages.sms-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'sms_api_key'      => Setting::get('sms_api_key', ''),
            'sms_template_id'  => Setting::get('sms_template_id', '238380'),
            'sms_sandbox'      => Setting::getBool('sms_sandbox', true),
            'sms_sandbox_code' => Setting::get('sms_sandbox_code', '12345'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Section::make('تنظیمات وب‌سرویس SMS.ir')
                ->description('اطلاعات اتصال به پنل پیامکی sms.ir را وارد کنید.')
                ->icon('heroicon-o-signal')
                ->schema([
                    TextInput::make('sms_api_key')
                        ->label('کلید API (x-api-key)')
                        ->password()
                        ->revealable()
                        ->placeholder('کلید API خود را از پنل sms.ir دریافت کنید')
                        ->helperText('از بخش «توسعه‌دهندگان» پنل sms.ir دریافت می‌شود.')
                        ->columnSpanFull(),

                    TextInput::make('sms_template_id')
                        ->label('شناسه قالب OTP (Template ID)')
                        ->numeric()
                        ->required()
                        ->default('238380')
                        ->helperText('شناسه قالب تأیید هویت از پنل sms.ir — پیش‌فرض: ۲۳۸۳۸۰'),
                ]),

            Section::make('حالت تست (Sandbox)')
                ->description('وقتی فعال است، پیامک واقعی ارسال نمی‌شود و کد ثابت برای ورود استفاده می‌شود.')
                ->icon('heroicon-o-beaker')
                ->schema([
                    Toggle::make('sms_sandbox')
                        ->label('فعال‌سازی حالت تست')
                        ->helperText('در حالت تست، کد زیر برای ورود همه شماره‌ها قبول می‌شود.')
                        ->live(),

                    TextInput::make('sms_sandbox_code')
                        ->label('کد تست ثابت')
                        ->default('12345')
                        ->maxLength(6)
                        ->helperText('هر کاربری با این کد می‌تواند وارد شود — فقط در حالت تست')
                        ->visible(fn($get) => $get('sms_sandbox')),
                ]),

            Section::make('راهنمای اتصال')
                ->icon('heroicon-o-information-circle')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Placeholder::make('guide')
                        ->label('')
                        ->content(view('filament.partials.sms-guide')),
                ]),

        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setMany([
            'sms_api_key'      => $data['sms_api_key'] ?? '',
            'sms_template_id'  => $data['sms_template_id'] ?? '238380',
            'sms_sandbox'      => $data['sms_sandbox'] ? '1' : '0',
            'sms_sandbox_code' => $data['sms_sandbox_code'] ?? '12345',
        ], 'sms');

        Notification::make()
            ->title('تنظیمات ذخیره شد ✅')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('ذخیره تنظیمات')
                ->submit('save')
                ->icon('heroicon-o-check')
                ->color('primary'),
        ];
    }
}
