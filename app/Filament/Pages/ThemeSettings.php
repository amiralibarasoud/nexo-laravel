<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;

class ThemeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'تنظیمات قالب';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort     = 20;
    protected static string $view             = 'filament.pages.theme-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillFormFromSettings();
    }

    protected function fillFormFromSettings(): void
    {
        $logo = Setting::get('header_logo');

        $this->form->fill([
            'header_logo'                  => $logo ?: null,
            'header_logo_letter'           => Setting::get('header_logo_letter', 'N'),
            'header_show_text_logo'        => Setting::getBool('header_show_text_logo', true),
            'header_site_name'             => Setting::get('header_site_name', 'نکسو'),
            'header_site_name_highlight'   => Setting::get('header_site_name_highlight', 'کورس'),
            'header_logo_position'         => Setting::get('header_logo_position', 'start'),
            'header_sticky'                => Setting::getBool('header_sticky', true),
            'header_login_text'            => Setting::get('header_login_text', 'ورود / ثبت‌نام'),
            'header_nav_links'             => Setting::getJson('header_nav_links', Setting::defaultNavLinks()),
            'header_announcement_enabled'  => Setting::getBool('header_announcement_enabled', false),
            'header_announcement_text'     => Setting::get('header_announcement_text', ''),
            'header_announcement_link'     => Setting::get('header_announcement_link', ''),
            'header_widgets'               => Setting::getJson('header_widgets', []),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('theme_tabs')
                ->tabs([
                    Tabs\Tab::make('هدر')
                        ->icon('heroicon-o-bars-3-bottom-left')
                        ->schema($this->headerSchema()),

                    Tabs\Tab::make('صفحه اصلی')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Placeholder::make('homepage_coming_soon')
                                ->label('')
                                ->content('تنظیمات صفحه اصلی به زودی اضافه می‌شود.'),
                        ]),

                    Tabs\Tab::make('فوتر')
                        ->icon('heroicon-o-rectangle-stack')
                        ->schema([
                            Placeholder::make('footer_coming_soon')
                                ->label('')
                                ->content('تنظیمات فوتر به زودی اضافه می‌شود.'),
                        ]),
                ])
                ->persistTabInQueryString(),
        ])->statePath('data');
    }

    protected function headerSchema(): array
    {
        return [
            Section::make('برندینگ و لوگو')
                ->description('لوگو، نام سایت و موقعیت نمایش در هدر')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('header_logo')
                        ->label('تصویر لوگو')
                        ->image()
                        ->disk('public')
                        ->directory('theme/logo')
                        ->visibility('public')
                        ->imageResizeMode('contain')
                        ->maxSize(2048)
                        ->helperText('در صورت آپلود، تصویر جایگزین لوگوی متنی می‌شود.')
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        TextInput::make('header_site_name')
                            ->label('نام سایت')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('header_site_name_highlight')
                            ->label('بخش برجسته نام')
                            ->maxLength(30)
                            ->helperText('مثلاً «کورس» در «نکسو کورس»'),

                        TextInput::make('header_logo_letter')
                            ->label('حرف لوگوی متنی')
                            ->maxLength(2)
                            ->helperText('وقتی تصویر لوگو ندارید، این حرف نمایش داده می‌شود.'),

                        Select::make('header_logo_position')
                            ->label('موقعیت لوگو')
                            ->options([
                                'start'  => 'راست (پیش‌فرض)',
                                'center' => 'وسط',
                                'end'    => 'چپ',
                            ])
                            ->required()
                            ->native(false),

                        Toggle::make('header_show_text_logo')
                            ->label('نمایش نام سایت کنار لوگو')
                            ->helperText('اگر غیرفعال باشد فقط تصویر/حرف لوگو نمایش داده می‌شود.'),

                        Toggle::make('header_sticky')
                            ->label('هدر چسبان (Sticky)')
                            ->helperText('هدر هنگام اسکرول در بالای صفحه ثابت بماند.'),
                    ]),
                ]),

            Section::make('منوی ناوبری')
                ->description('لینک‌های منوی اصلی سایت')
                ->icon('heroicon-o-link')
                ->schema([
                    Repeater::make('header_nav_links')
                        ->label('آیتم‌های منو')
                        ->schema([
                            TextInput::make('label')
                                ->label('عنوان')
                                ->required()
                                ->maxLength(50),

                            Select::make('route_name')
                                ->label('صفحه')
                                ->options($this->routeOptions())
                                ->searchable()
                                ->live(),

                            TextInput::make('url')
                                ->label('لینک سفارشی')
                                ->url()
                                ->nullable()
                                ->placeholder('https://...')
                                ->visible(fn ($get) => empty($get('route_name')))
                                ->helperText('فقط وقتی «لینک سفارشی» انتخاب شده باشد.'),

                            Toggle::make('visible')
                                ->label('نمایش')
                                ->default(true),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'آیتم منو')
                        ->defaultItems(0)
                        ->addActionLabel('افزودن آیتم منو')
                        ->reorderable()
                        ->columnSpanFull(),
                ]),

            Section::make('نوار اعلان')
                ->description('یک نوار متنی بالای هدر (مثلاً تخفیف یا اطلاعیه)')
                ->icon('heroicon-o-megaphone')
                ->schema([
                    Toggle::make('header_announcement_enabled')
                        ->label('فعال‌سازی نوار اعلان')
                        ->live(),

                    TextInput::make('header_announcement_text')
                        ->label('متن اعلان')
                        ->maxLength(200)
                        ->visible(fn ($get) => $get('header_announcement_enabled')),

                    TextInput::make('header_announcement_link')
                        ->label('لینک اعلان (اختیاری)')
                        ->url()
                        ->nullable()
                        ->visible(fn ($get) => $get('header_announcement_enabled')),
                ]),

            Section::make('ویجت‌های متنی')
                ->description('متن‌های اضافی در کنار منو یا دکمه ورود')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->schema([
                    Repeater::make('header_widgets')
                        ->label('ویجت‌ها')
                        ->schema([
                            Select::make('type')
                                ->label('نوع')
                                ->options([
                                    'text'  => 'متن ساده',
                                    'link'  => 'لینک',
                                    'badge' => 'برچسب',
                                ])
                                ->required()
                                ->native(false),

                            TextInput::make('content')
                                ->label('متن')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('link')
                                ->label('لینک')
                                ->url()
                                ->nullable()
                                ->visible(fn ($get) => in_array($get('type'), ['link', 'badge'], true)),

                            Select::make('position')
                                ->label('موقعیت')
                                ->options([
                                    'before_auth' => 'قبل از دکمه ورود',
                                    'after_nav'   => 'بعد از منو',
                                ])
                                ->default('before_auth')
                                ->native(false),

                            Toggle::make('visible')
                                ->label('نمایش')
                                ->default(true),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['content'] ?? 'ویجت')
                        ->defaultItems(0)
                        ->addActionLabel('افزودن ویجت')
                        ->reorderable()
                        ->columnSpanFull(),
                ]),

            Section::make('دکمه ورود')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    TextInput::make('header_login_text')
                        ->label('متن دکمه ورود')
                        ->required()
                        ->maxLength(50)
                        ->default('ورود / ثبت‌نام'),
                ]),
        ];
    }

    protected function routeOptions(): array
    {
        return [
            'home'           => 'خانه',
            'courses.index'  => 'دوره‌ها',
            'blog.index'     => 'بلاگ',
            'about'          => 'درباره ما',
            'contact'        => 'تماس',
            'terms'          => 'قوانین و مقررات',
            'login'          => 'ورود',
            ''               => '— لینک سفارشی —',
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $logo = $this->extractUploadPath($data['header_logo'] ?? null) ?? '';

        Setting::setMany([
            'header_logo'                  => $logo,
            'header_logo_letter'           => $data['header_logo_letter'] ?? 'N',
            'header_show_text_logo'        => ($data['header_show_text_logo'] ?? true) ? '1' : '0',
            'header_site_name'             => $data['header_site_name'] ?? 'نکسو',
            'header_site_name_highlight'   => $data['header_site_name_highlight'] ?? 'کورس',
            'header_logo_position'         => $data['header_logo_position'] ?? 'start',
            'header_sticky'                => ($data['header_sticky'] ?? true) ? '1' : '0',
            'header_login_text'            => $data['header_login_text'] ?? 'ورود / ثبت‌نام',
            'header_nav_links'             => json_encode($data['header_nav_links'] ?? [], JSON_UNESCAPED_UNICODE),
            'header_announcement_enabled'  => ($data['header_announcement_enabled'] ?? false) ? '1' : '0',
            'header_announcement_text'     => $data['header_announcement_text'] ?? '',
            'header_announcement_link'     => $data['header_announcement_link'] ?? '',
            'header_widgets'               => json_encode($data['header_widgets'] ?? [], JSON_UNESCAPED_UNICODE),
        ], 'theme');

        $this->fillFormFromSettings();

        Notification::make()
            ->title('تنظیمات هدر ذخیره شد ✅')
            ->success()
            ->send();
    }

    protected function extractUploadPath(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $path = Arr::first(Arr::flatten($value));

            return is_string($path) ? $path : null;
        }

        return null;
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
