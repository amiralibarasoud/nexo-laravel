<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'تنظیمات درگاه پرداخت';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort     = 11;
    protected static string $view             = 'filament.pages.payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $config = Setting::paymentConfig();

        $this->form->fill([
            'zarinpal_enabled'    => $config['zarinpal']['enabled'],
            'zarinpal_merchant_id'=> $config['zarinpal']['merchant_id'],
            'zarinpal_sandbox'    => $config['zarinpal']['sandbox'],
            'zibal_enabled'       => $config['zibal']['enabled'],
            'zibal_merchant'      => $config['zibal']['merchant'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([

            Section::make('درگاه زرین‌پال')
                ->description('مرچنت کد و وضعیت درگاه زرین‌پال را مدیریت کنید.')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Toggle::make('zarinpal_enabled')
                        ->label('فعال‌سازی درگاه زرین‌پال')
                        ->helperText('وقتی فعال باشد، کاربران می‌توانند با زرین‌پال پرداخت کنند.')
                        ->live(),

                    TextInput::make('zarinpal_merchant_id')
                        ->label('مرچنت کد (Merchant ID)')
                        ->placeholder('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
                        ->helperText('از پنل زرین‌پال دریافت می‌شود.')
                        ->visible(fn ($get) => $get('zarinpal_enabled'))
                        ->columnSpanFull(),

                    Toggle::make('zarinpal_sandbox')
                        ->label('حالت تست (Sandbox)')
                        ->helperText('در حالت تست، تراکنش واقعی انجام نمی‌شود.')
                        ->visible(fn ($get) => $get('zarinpal_enabled')),
                ]),

            Section::make('درگاه زیبال')
                ->description('مرچنت کد و وضعیت درگاه زیبال را مدیریت کنید.')
                ->icon('heroicon-o-building-library')
                ->schema([
                    Toggle::make('zibal_enabled')
                        ->label('فعال‌سازی درگاه زیبال')
                        ->helperText('وقتی فعال باشد، کاربران می‌توانند با زیبال پرداخت کنند.')
                        ->live(),

                    TextInput::make('zibal_merchant')
                        ->label('مرچنت کد')
                        ->placeholder('zibal')
                        ->helperText('مرچنت کد دریافتی از پنل زیبال — برای تست از «zibal» استفاده کنید.')
                        ->visible(fn ($get) => $get('zibal_enabled'))
                        ->columnSpanFull(),
                ]),

        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setMany([
            'zarinpal_enabled'     => ($data['zarinpal_enabled'] ?? false) ? '1' : '0',
            'zarinpal_merchant_id' => $data['zarinpal_merchant_id'] ?? '',
            'zarinpal_sandbox'     => ($data['zarinpal_sandbox'] ?? false) ? '1' : '0',
            'zibal_enabled'        => ($data['zibal_enabled'] ?? false) ? '1' : '0',
            'zibal_merchant'       => $data['zibal_merchant'] ?? 'zibal',
        ], 'payment');

        Notification::make()
            ->title('تنظیمات درگاه پرداخت ذخیره شد ✅')
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
