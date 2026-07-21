<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
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
        $this->form->fill([
            'header_logo'                  => Setting::get('header_logo') ?: null,
            'header_logo_letter'           => Setting::get('header_logo_letter', 'N'),
            'header_show_text_logo'        => Setting::getBool('header_show_text_logo', true),
            'header_site_name'             => Setting::get('header_site_name', 'نکسو'),
            'header_site_name_highlight'   => Setting::get('header_site_name_highlight', 'نکسووست'),
            'header_logo_position'         => Setting::get('header_logo_position', 'start'),
            'header_sticky'                => Setting::getBool('header_sticky', true),
            'header_login_text'            => Setting::get('header_login_text', 'ورود / ثبت‌نام'),
            'header_nav_links'             => Setting::getJson('header_nav_links', Setting::defaultNavLinks()),
            'header_announcement_enabled'  => Setting::getBool('header_announcement_enabled', false),
            'header_announcement_text'     => Setting::get('header_announcement_text', ''),
            'header_announcement_link'     => Setting::get('header_announcement_link', ''),
            'header_widgets'               => Setting::getJson('header_widgets', []),
            'home_hero_enabled'            => Setting::getBool('home_hero_enabled', true),
            'home_hero_badge'              => Setting::get('home_hero_badge', ''),
            'home_hero_title_before'       => Setting::get('home_hero_title_before', 'یادگیری با'),
            'home_hero_highlight1'         => Setting::get('home_hero_highlight1', 'صدای'),
            'home_hero_title_middle'       => Setting::get('home_hero_title_middle', 'استاد یا'),
            'home_hero_highlight2'         => Setting::get('home_hero_highlight2', 'متن'),
            'home_hero_title_suffix'       => Setting::get('home_hero_title_suffix', 'انتخاب با توست.'),
            'home_hero_description'        => Setting::get('home_hero_description', ''),
            'home_hero_cta_text'           => Setting::get('home_hero_cta_text', 'مشاهده دوره‌ها'),
            'home_hero_cta_route'          => Setting::get('home_hero_cta_route', 'courses.index'),
            'home_hero_image'              => Setting::get('home_hero_image') ?: null,
            'home_stats_enabled'           => Setting::getBool('home_stats_enabled', true),
            'home_stats'                   => Setting::getJson('home_stats', Setting::defaultHomeStats()),
            'home_steps_enabled'           => Setting::getBool('home_steps_enabled', true),
            'home_steps_title'             => Setting::get('home_steps_title', 'چطور کار می‌کنه؟'),
            'home_steps_subtitle'          => Setting::get('home_steps_subtitle', 'در چند قدم ساده شروع کن'),
            'home_steps'                   => Setting::getJson('home_steps', Setting::defaultHomeSteps()),
            'home_categories_enabled'      => Setting::getBool('home_categories_enabled', true),
            'home_categories_title'        => Setting::get('home_categories_title', 'دسته‌بندی‌ها'),
            'home_featured_enabled'        => Setting::getBool('home_featured_enabled', true),
            'home_featured_title'          => Setting::get('home_featured_title', 'دوره‌های ویژه'),
            'home_featured_subtitle'       => Setting::get('home_featured_subtitle', 'بهترین دوره‌ها برای شما'),
            'home_featured_link_text'      => Setting::get('home_featured_link_text', 'مشاهده همه'),
            'home_blog_enabled'            => Setting::getBool('home_blog_enabled', true),
            'home_blog_title'              => Setting::get('home_blog_title', 'آخرین مقالات'),
            'home_blog_subtitle'           => Setting::get('home_blog_subtitle', 'بخوان، یاد بگیر، رشد کن'),
            'home_blog_link_text'          => Setting::get('home_blog_link_text', 'همه مقالات'),
            'home_content_types_enabled'   => Setting::getBool('home_content_types_enabled', true),
            'home_content_types_title'     => Setting::get('home_content_types_title', 'دو راه برای یادگیری'),
            'home_content_types_subtitle'  => Setting::get('home_content_types_subtitle', 'بعد از خرید، خودت انتخاب می‌کنی'),
            'home_content_cards'           => Setting::getJson('home_content_cards', Setting::defaultHomeContentCards()),
            'footer_logo'                  => Setting::get('footer_logo') ?: null,
            'footer_logo_letter'           => Setting::get('footer_logo_letter', 'N'),
            'footer_site_name'             => Setting::get('footer_site_name', 'نکسووست'),
            'footer_description'           => Setting::get('footer_description', ''),
            'footer_links_title'           => Setting::get('footer_links_title', 'دسترسی سریع'),
            'footer_links'                 => Setting::getJson('footer_links', Setting::defaultFooterLinks()),
            'footer_contact_title'         => Setting::get('footer_contact_title', 'تماس با ما'),
            'footer_email'                 => Setting::get('footer_email', ''),
            'footer_phone'                 => Setting::get('footer_phone', ''),
            'footer_address'               => Setting::get('footer_address', 'قم ـ بلوار جمهوری اسلامی ـ کوچه ۳۶ ـ ساختمان آسیا ـ طبقه دوم'),
            'footer_show_contact_link'     => Setting::getBool('footer_show_contact_link', true),
            'footer_contact_link_text'     => Setting::get('footer_contact_link_text', 'فرم تماس'),
            'footer_enamad_enabled'        => Setting::getBool('footer_enamad_enabled', true),
            'footer_enamad_html'           => Setting::get('footer_enamad_html', ''),
            'footer_copyright'             => Setting::get('footer_copyright', 'تمامی حقوق برای نکسووست محفوظ است © {year}'),
            'contact_seo_title'            => Setting::get('contact_seo_title', 'تماس با ما'),
            'contact_page_title'           => Setting::get('contact_page_title', 'تماس با ما'),
            'contact_page_subtitle'        => Setting::get('contact_page_subtitle', ''),
            'contact_info_items'           => Setting::getJson('contact_info_items', Setting::defaultContactInfoItems()),
            'contact_form_enabled'         => Setting::getBool('contact_form_enabled', true),
            'contact_form_title'           => Setting::get('contact_form_title', 'ارسال پیام'),
            'contact_form_name_label'      => Setting::get('contact_form_name_label', 'نام و نام خانوادگی *'),
            'contact_form_mobile_label'    => Setting::get('contact_form_mobile_label', 'شماره موبایل *'),
            'contact_form_subject_label'   => Setting::get('contact_form_subject_label', 'موضوع *'),
            'contact_form_message_label'   => Setting::get('contact_form_message_label', 'پیام *'),
            'contact_form_name_placeholder'=> Setting::get('contact_form_name_placeholder', 'نام شما'),
            'contact_form_mobile_placeholder'=> Setting::get('contact_form_mobile_placeholder', '09xxxxxxxxx'),
            'contact_form_subject_placeholder'=> Setting::get('contact_form_subject_placeholder', 'موضوع پیام'),
            'contact_form_message_placeholder'=> Setting::get('contact_form_message_placeholder', 'پیام خود را بنویسید...'),
            'contact_form_submit_text'     => Setting::get('contact_form_submit_text', 'ارسال پیام'),
            'contact_form_loading_text'    => Setting::get('contact_form_loading_text', 'در حال ارسال...'),
            'contact_form_success_message' => Setting::get('contact_form_success_message', ''),
            'about_seo_title'              => Setting::get('about_seo_title', 'درباره ما'),
            'about_hero_enabled'           => Setting::getBool('about_hero_enabled', true),
            'about_hero_title'             => Setting::get('about_hero_title', 'درباره نکسووست'),
            'about_hero_description'       => Setting::get('about_hero_description', ''),
            'about_hero_image'             => Setting::get('about_hero_image') ?: null,
            'about_mission_enabled'        => Setting::getBool('about_mission_enabled', true),
            'about_mission_title'          => Setting::get('about_mission_title', 'مأموریت ما'),
            'about_mission_paragraph1'     => Setting::get('about_mission_paragraph1', ''),
            'about_mission_paragraph2'     => Setting::get('about_mission_paragraph2', ''),
            'about_mission_image'          => Setting::get('about_mission_image') ?: null,
            'about_mission_stats'          => Setting::getJson('about_mission_stats', Setting::defaultAboutStats()),
            'about_values_enabled'         => Setting::getBool('about_values_enabled', true),
            'about_values_title'           => Setting::get('about_values_title', 'ارزش‌های ما'),
            'about_values'                 => Setting::getJson('about_values', Setting::defaultAboutValues()),
            'about_cta_enabled'            => Setting::getBool('about_cta_enabled', true),
            'about_cta_title'              => Setting::get('about_cta_title', 'آماده شروع هستی؟'),
            'about_cta_subtitle'           => Setting::get('about_cta_subtitle', ''),
            'about_cta_button_text'        => Setting::get('about_cta_button_text', 'مشاهده دوره‌ها'),
            'about_cta_button_route'       => Setting::get('about_cta_button_route', 'courses.index'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('theme_tabs')
                ->tabs([
                    Tabs\Tab::make('هدر')->icon('heroicon-o-bars-3-bottom-left')->schema($this->headerSchema()),
                    Tabs\Tab::make('صفحه اصلی')->icon('heroicon-o-home')->schema($this->homepageSchema()),
                    Tabs\Tab::make('فوتر')->icon('heroicon-o-rectangle-stack')->schema($this->footerSchema()),
                    Tabs\Tab::make('تماس با ما')->icon('heroicon-o-envelope')->schema($this->contactSchema()),
                    Tabs\Tab::make('درباره ما')->icon('heroicon-o-information-circle')->schema($this->aboutSchema()),
                ])
                ->persistTabInQueryString(),
        ])->statePath('data');
    }

    protected function headerSchema(): array
    {
        return [
            Section::make('برندینگ و لوگو')->icon('heroicon-o-photo')->schema([
                FileUpload::make('header_logo')->label('تصویر لوگو')->image()
                    ->disk('public')->directory('theme/logo')->visibility('public')->maxSize(2048)->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('header_site_name')->label('نام سایت')->required()->maxLength(50),
                    TextInput::make('header_site_name_highlight')->label('بخش برجسته نام')->maxLength(30),
                    TextInput::make('header_logo_letter')->label('حرف لوگوی متنی')->maxLength(2),
                    Select::make('header_logo_position')->label('موقعیت لوگو')->options([
                        'start' => 'راست', 'center' => 'وسط', 'end' => 'چپ',
                    ])->required()->native(false),
                    Toggle::make('header_show_text_logo')->label('نمایش نام کنار لوگو'),
                    Toggle::make('header_sticky')->label('هدر چسبان'),
                ]),
            ]),
            Section::make('منوی ناوبری')->icon('heroicon-o-link')->schema([
                Repeater::make('header_nav_links')->label('آیتم‌های منو')->schema($this->linkRepeaterSchema())
                    ->columns(2)->collapsible()->reorderable()->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'آیتم منو'),
            ]),
            Section::make('نوار اعلان')->schema([
                Toggle::make('header_announcement_enabled')->label('فعال')->live(),
                TextInput::make('header_announcement_text')->label('متن')->maxLength(200)
                    ->visible(fn ($get) => $get('header_announcement_enabled')),
                TextInput::make('header_announcement_link')->label('لینک')->url()->nullable()
                    ->visible(fn ($get) => $get('header_announcement_enabled')),
            ]),
            Section::make('ویجت‌های متنی')->schema([
                Repeater::make('header_widgets')->label('ویجت‌ها')->schema([
                    Select::make('type')->label('نوع')->options(['text' => 'متن', 'link' => 'لینک', 'badge' => 'برچسب'])->required()->native(false),
                    TextInput::make('content')->label('متن')->required()->maxLength(100),
                    TextInput::make('link')->label('لینک')->url()->nullable(),
                    Select::make('position')->label('موقعیت')->options(['before_auth' => 'قبل از ورود', 'after_nav' => 'بعد از منو'])->native(false),
                    Toggle::make('visible')->label('نمایش')->default(true),
                ])->columns(2)->collapsible()->reorderable()->columnSpanFull(),
            ]),
            Section::make('دکمه ورود')->schema([
                TextInput::make('header_login_text')->label('متن دکمه')->required()->maxLength(50),
            ]),
        ];
    }

    protected function homepageSchema(): array
    {
        return [
            Section::make('بخش Hero')->description('بنر اصلی بالای صفحه')->icon('heroicon-o-sparkles')->schema([
                Toggle::make('home_hero_enabled')->label('نمایش بخش Hero')->default(true),
                TextInput::make('home_hero_badge')->label('برچسب بالای عنوان')->maxLength(100)->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('home_hero_title_before')->label('متن قبل از برجسته ۱')->maxLength(80),
                    TextInput::make('home_hero_highlight1')->label('برجسته ۱ (زرد)')->maxLength(40),
                    TextInput::make('home_hero_title_middle')->label('متن میانی')->maxLength(80),
                    TextInput::make('home_hero_highlight2')->label('برجسته ۲ (سبز)')->maxLength(40),
                    TextInput::make('home_hero_title_suffix')->label('زیرعنوان')->maxLength(80)->columnSpanFull(),
                ]),
                Textarea::make('home_hero_description')->label('توضیحات')->rows(3)->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('home_hero_cta_text')->label('متن دکمه')->maxLength(50),
                    Select::make('home_hero_cta_route')->label('لینک دکمه')->options($this->routeOptions())->searchable()->native(false),
                ]),
                FileUpload::make('home_hero_image')->label('تصویر پس‌زمینه (اختیاری)')->image()
                    ->disk('public')->directory('theme/home')->visibility('public')->maxSize(4096)->columnSpanFull(),
            ]),
            Section::make('آمار Hero')->schema([
                Toggle::make('home_stats_enabled')->label('نمایش آمار')->default(true),
                Repeater::make('home_stats')->label('آیتم‌های آمار')->schema([
                    Select::make('type')->label('نوع مقدار')->options([
                        'dynamic_courses'  => 'تعداد دوره‌ها (خودکار)',
                        'dynamic_students' => 'تعداد دانش‌آموز (خودکار)',
                        'manual'           => 'عدد دستی',
                    ])->required()->live()->native(false),
                    TextInput::make('value')->label('مقدار دستی')->maxLength(20)
                        ->visible(fn ($get) => $get('type') === 'manual'),
                    TextInput::make('label')->label('برچسب')->required()->maxLength(50),
                    TextInput::make('suffix')->label('پسوند')->maxLength(5)->placeholder('+'),
                ])->columns(2)->collapsible()->reorderable()->maxItems(4)->columnSpanFull(),
            ]),
            Section::make('چطور کار می‌کنه؟')->schema([
                Toggle::make('home_steps_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('home_steps_title')->label('عنوان')->maxLength(100),
                TextInput::make('home_steps_subtitle')->label('زیرعنوان')->maxLength(150),
                Repeater::make('home_steps')->label('مراحل')->schema([
                    TextInput::make('emoji')->label('ایموجی')->maxLength(4),
                    TextInput::make('title')->label('عنوان')->required()->maxLength(80),
                    Textarea::make('desc')->label('توضیح')->rows(2)->maxLength(300),
                    Select::make('bg')->label('رنگ پس‌زمینه')->options([
                        'bg-blue-50' => 'آبی', 'bg-green-50' => 'سبز', 'bg-purple-50' => 'بنفش',
                        'bg-yellow-50' => 'زرد', 'bg-pink-50' => 'صورتی',
                    ])->native(false),
                ])->columns(2)->collapsible()->reorderable()->maxItems(6)->columnSpanFull(),
            ]),
            Section::make('دسته‌بندی‌ها')->schema([
                Toggle::make('home_categories_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('home_categories_title')->label('عنوان')->maxLength(100),
            ]),
            Section::make('دوره‌های ویژه')->schema([
                Toggle::make('home_featured_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('home_featured_title')->label('عنوان')->maxLength(100),
                TextInput::make('home_featured_subtitle')->label('زیرعنوان')->maxLength(150),
                TextInput::make('home_featured_link_text')->label('متن لینک «مشاهده همه»')->maxLength(50),
            ]),
            Section::make('آخرین مقالات')->schema([
                Toggle::make('home_blog_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('home_blog_title')->label('عنوان')->maxLength(100),
                TextInput::make('home_blog_subtitle')->label('زیرعنوان')->maxLength(150),
                TextInput::make('home_blog_link_text')->label('متن لینک')->maxLength(50),
            ]),
            Section::make('دو راه برای یادگیری')->schema([
                Toggle::make('home_content_types_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('home_content_types_title')->label('عنوان')->maxLength(100),
                TextInput::make('home_content_types_subtitle')->label('زیرعنوان')->maxLength(150),
                Repeater::make('home_content_cards')->label('کارت‌ها')->schema([
                    TextInput::make('emoji')->label('ایموجی')->maxLength(4),
                    TextInput::make('title')->label('عنوان کارت')->required()->maxLength(80),
                    Repeater::make('items')->label('ویژگی‌ها')->simple(
                        TextInput::make('item')->label('متن')->required()->maxLength(120),
                    )->columnSpanFull(),
                ])->columns(2)->collapsible()->reorderable()->maxItems(4)->columnSpanFull(),
            ]),
        ];
    }

    protected function footerSchema(): array
    {
        return [
            Section::make('برندینگ فوتر')->icon('heroicon-o-photo')->schema([
                FileUpload::make('footer_logo')->label('لوگوی فوتر')->image()
                    ->disk('public')->directory('theme/footer')->visibility('public')->maxSize(2048)->columnSpanFull(),
                Grid::make(2)->schema([
                    TextInput::make('footer_logo_letter')->label('حرف لوگوی متنی')->maxLength(2),
                    TextInput::make('footer_site_name')->label('نام سایت')->required()->maxLength(80),
                ]),
                Textarea::make('footer_description')->label('توضیحات')->rows(3)->columnSpanFull(),
            ]),
            Section::make('لینک‌های سریع')->schema([
                TextInput::make('footer_links_title')->label('عنوان ستون')->maxLength(80),
                Repeater::make('footer_links')->label('لینک‌ها')->schema($this->linkRepeaterSchema())
                    ->columns(2)->collapsible()->reorderable()->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'لینک'),
            ]),
            Section::make('تماس با ما')->schema([
                TextInput::make('footer_contact_title')->label('عنوان ستون')->maxLength(80),
                TextInput::make('footer_email')->label('ایمیل')->email()->maxLength(120),
                TextInput::make('footer_phone')->label('تلفن')->tel()->maxLength(30),
                Textarea::make('footer_address')->label('آدرس')->rows(3)->maxLength(300)->columnSpanFull(),
                Toggle::make('footer_show_contact_link')->label('نمایش لینک فرم تماس'),
                TextInput::make('footer_contact_link_text')->label('متن لینک تماس')->maxLength(50),
            ]),
            Section::make('نماد اعتماد (اینماد)')->icon('heroicon-o-shield-check')->schema([
                Toggle::make('footer_enamad_enabled')->label('نمایش نماد اعتماد')->default(true),
                Textarea::make('footer_enamad_html')->label('کد HTML اینماد')
                    ->helperText('کد دریافتی از enamad.ir (تگ a/img یا script) را اینجا قرار دهید. زیر بخش تماس در فوتر با پس‌زمینه سفید نمایش داده می‌شود.')
                    ->rows(6)->columnSpanFull()
                    ->extraInputAttributes(['dir' => 'ltr', 'class' => 'font-mono text-sm']),
            ]),
            Section::make('کپی‌رایت')->schema([
                TextInput::make('footer_copyright')->label('متن پایین فوتر')
                    ->helperText('از {year} برای سال جلالی استفاده کنید.')
                    ->maxLength(200)->columnSpanFull(),
            ]),
        ];
    }

    protected function contactSchema(): array
    {
        return [
            Section::make('عنوان صفحه')->schema([
                TextInput::make('contact_seo_title')->label('عنوان SEO')->maxLength(80),
                TextInput::make('contact_page_title')->label('عنوان صفحه')->required()->maxLength(100),
                TextInput::make('contact_page_subtitle')->label('زیرعنوان')->maxLength(200)->columnSpanFull(),
            ])->columns(2),
            Section::make('اطلاعات تماس')->schema([
                Repeater::make('contact_info_items')->label('کارت‌های اطلاعات')->schema([
                    TextInput::make('icon')->label('ایموجی')->maxLength(4),
                    TextInput::make('title')->label('عنوان')->required()->maxLength(50),
                    TextInput::make('value')->label('مقدار')->required()->maxLength(150),
                    Select::make('bg')->label('رنگ پس‌زمینه')->options($this->bgColorOptions())->native(false),
                    Toggle::make('visible')->label('نمایش')->default(true),
                ])->columns(2)->collapsible()->reorderable()->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'آیتم'),
            ]),
            Section::make('فرم تماس')->schema([
                Toggle::make('contact_form_enabled')->label('نمایش فرم')->default(true),
                TextInput::make('contact_form_title')->label('عنوان فرم')->maxLength(80),
                Grid::make(2)->schema([
                    TextInput::make('contact_form_name_label')->label('برچسب نام')->maxLength(80),
                    TextInput::make('contact_form_mobile_label')->label('برچسب موبایل')->maxLength(80),
                    TextInput::make('contact_form_subject_label')->label('برچسب موضوع')->maxLength(80),
                    TextInput::make('contact_form_message_label')->label('برچسب پیام')->maxLength(80),
                    TextInput::make('contact_form_name_placeholder')->label('Placeholder نام')->maxLength(80),
                    TextInput::make('contact_form_mobile_placeholder')->label('Placeholder موبایل')->maxLength(80),
                    TextInput::make('contact_form_subject_placeholder')->label('Placeholder موضوع')->maxLength(80),
                    TextInput::make('contact_form_message_placeholder')->label('Placeholder پیام')->maxLength(120),
                    TextInput::make('contact_form_submit_text')->label('متن دکمه ارسال')->maxLength(50),
                    TextInput::make('contact_form_loading_text')->label('متن در حال ارسال')->maxLength(50),
                ]),
                TextInput::make('contact_form_success_message')->label('پیام موفقیت پس از ارسال')->maxLength(200)->columnSpanFull(),
            ]),
        ];
    }

    protected function aboutSchema(): array
    {
        return [
            Section::make('Hero')->schema([
                Toggle::make('about_hero_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('about_seo_title')->label('عنوان SEO')->maxLength(80),
                TextInput::make('about_hero_title')->label('عنوان')->maxLength(100),
                Textarea::make('about_hero_description')->label('توضیحات')->rows(3)->columnSpanFull(),
                FileUpload::make('about_hero_image')->label('تصویر پس‌زمینه (اختیاری)')->image()
                    ->disk('public')->directory('theme/about')->visibility('public')->maxSize(4096)->columnSpanFull(),
            ]),
            Section::make('مأموریت')->schema([
                Toggle::make('about_mission_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('about_mission_title')->label('عنوان')->maxLength(100),
                Textarea::make('about_mission_paragraph1')->label('پاراگراف ۱')->rows(3)->columnSpanFull(),
                Textarea::make('about_mission_paragraph2')->label('پاراگراف ۲')->rows(3)->columnSpanFull(),
                FileUpload::make('about_mission_image')->label('تصویر کنار متن (اختیاری)')->image()
                    ->disk('public')->directory('theme/about')->visibility('public')->maxSize(4096)->columnSpanFull(),
                Repeater::make('about_mission_stats')->label('آمار')->schema([
                    TextInput::make('value')->label('مقدار')->required()->maxLength(20),
                    TextInput::make('label')->label('برچسب')->required()->maxLength(50),
                ])->columns(2)->collapsible()->reorderable()->maxItems(6)->columnSpanFull(),
            ]),
            Section::make('ارزش‌ها')->schema([
                Toggle::make('about_values_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('about_values_title')->label('عنوان')->maxLength(100),
                Repeater::make('about_values')->label('ارزش‌ها')->schema([
                    TextInput::make('icon')->label('ایموجی')->maxLength(4),
                    TextInput::make('title')->label('عنوان')->required()->maxLength(60),
                    Textarea::make('desc')->label('توضیح')->rows(2)->maxLength(300),
                ])->columns(2)->collapsible()->reorderable()->maxItems(8)->columnSpanFull(),
            ]),
            Section::make('دعوت به اقدام (CTA)')->schema([
                Toggle::make('about_cta_enabled')->label('نمایش بخش')->default(true),
                TextInput::make('about_cta_title')->label('عنوان')->maxLength(100),
                TextInput::make('about_cta_subtitle')->label('زیرعنوان')->maxLength(150),
                Grid::make(2)->schema([
                    TextInput::make('about_cta_button_text')->label('متن دکمه')->maxLength(50),
                    Select::make('about_cta_button_route')->label('لینک دکمه')->options($this->routeOptions())->searchable()->native(false),
                ]),
            ]),
        ];
    }

    protected function bgColorOptions(): array
    {
        return [
            'bg-blue-50' => 'آبی', 'bg-green-50' => 'سبز', 'bg-purple-50' => 'بنفش',
            'bg-yellow-50' => 'زرد', 'bg-pink-50' => 'صورتی', 'bg-orange-50' => 'نارنجی',
        ];
    }

    protected function linkRepeaterSchema(): array
    {
        return [
            TextInput::make('label')->label('عنوان')->required()->maxLength(50),
            Select::make('route_name')->label('صفحه')->options($this->routeOptions())->searchable()->live(),
            TextInput::make('url')->label('لینک سفارشی')->url()->nullable()
                ->visible(fn ($get) => empty($get('route_name'))),
            Toggle::make('visible')->label('نمایش')->default(true),
        ];
    }

    protected function routeOptions(): array
    {
        return [
            'home' => 'خانه', 'courses.index' => 'دوره‌ها', 'blog.index' => 'بلاگ',
            'about' => 'درباره ما', 'contact' => 'تماس', 'terms' => 'قوانین', 'login' => 'ورود',
            '' => '— لینک سفارشی —',
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setMany([
            'header_logo'                  => $this->extractUploadPath($data['header_logo'] ?? null) ?? '',
            'header_logo_letter'           => $data['header_logo_letter'] ?? 'N',
            'header_show_text_logo'        => ($data['header_show_text_logo'] ?? true) ? '1' : '0',
            'header_site_name'             => $data['header_site_name'] ?? 'نکسو',
            'header_site_name_highlight'   => $data['header_site_name_highlight'] ?? 'وست',
            'header_logo_position'         => $data['header_logo_position'] ?? 'start',
            'header_sticky'                => ($data['header_sticky'] ?? true) ? '1' : '0',
            'header_login_text'            => $data['header_login_text'] ?? 'ورود / ثبت‌نام',
            'header_nav_links'             => json_encode($data['header_nav_links'] ?? [], JSON_UNESCAPED_UNICODE),
            'header_announcement_enabled'  => ($data['header_announcement_enabled'] ?? false) ? '1' : '0',
            'header_announcement_text'     => $data['header_announcement_text'] ?? '',
            'header_announcement_link'     => $data['header_announcement_link'] ?? '',
            'header_widgets'               => json_encode($data['header_widgets'] ?? [], JSON_UNESCAPED_UNICODE),
            'home_hero_enabled'            => ($data['home_hero_enabled'] ?? true) ? '1' : '0',
            'home_hero_badge'              => $data['home_hero_badge'] ?? '',
            'home_hero_title_before'       => $data['home_hero_title_before'] ?? '',
            'home_hero_highlight1'         => $data['home_hero_highlight1'] ?? '',
            'home_hero_title_middle'       => $data['home_hero_title_middle'] ?? '',
            'home_hero_highlight2'         => $data['home_hero_highlight2'] ?? '',
            'home_hero_title_suffix'       => $data['home_hero_title_suffix'] ?? '',
            'home_hero_description'        => $data['home_hero_description'] ?? '',
            'home_hero_cta_text'           => $data['home_hero_cta_text'] ?? '',
            'home_hero_cta_route'          => $data['home_hero_cta_route'] ?? 'courses.index',
            'home_hero_image'              => $this->extractUploadPath($data['home_hero_image'] ?? null) ?? '',
            'home_stats_enabled'           => ($data['home_stats_enabled'] ?? true) ? '1' : '0',
            'home_stats'                   => json_encode($data['home_stats'] ?? [], JSON_UNESCAPED_UNICODE),
            'home_steps_enabled'           => ($data['home_steps_enabled'] ?? true) ? '1' : '0',
            'home_steps_title'             => $data['home_steps_title'] ?? '',
            'home_steps_subtitle'          => $data['home_steps_subtitle'] ?? '',
            'home_steps'                   => json_encode($data['home_steps'] ?? [], JSON_UNESCAPED_UNICODE),
            'home_categories_enabled'      => ($data['home_categories_enabled'] ?? true) ? '1' : '0',
            'home_categories_title'        => $data['home_categories_title'] ?? '',
            'home_featured_enabled'        => ($data['home_featured_enabled'] ?? true) ? '1' : '0',
            'home_featured_title'          => $data['home_featured_title'] ?? '',
            'home_featured_subtitle'       => $data['home_featured_subtitle'] ?? '',
            'home_featured_link_text'      => $data['home_featured_link_text'] ?? '',
            'home_blog_enabled'            => ($data['home_blog_enabled'] ?? true) ? '1' : '0',
            'home_blog_title'              => $data['home_blog_title'] ?? '',
            'home_blog_subtitle'           => $data['home_blog_subtitle'] ?? '',
            'home_blog_link_text'          => $data['home_blog_link_text'] ?? '',
            'home_content_types_enabled'   => ($data['home_content_types_enabled'] ?? true) ? '1' : '0',
            'home_content_types_title'     => $data['home_content_types_title'] ?? '',
            'home_content_types_subtitle'  => $data['home_content_types_subtitle'] ?? '',
            'home_content_cards'           => json_encode($this->normalizeContentCards($data['home_content_cards'] ?? []), JSON_UNESCAPED_UNICODE),
            'footer_logo'                  => $this->extractUploadPath($data['footer_logo'] ?? null) ?? '',
            'footer_logo_letter'           => $data['footer_logo_letter'] ?? 'N',
            'footer_site_name'             => $data['footer_site_name'] ?? '',
            'footer_description'           => $data['footer_description'] ?? '',
            'footer_links_title'           => $data['footer_links_title'] ?? '',
            'footer_links'                 => json_encode($data['footer_links'] ?? [], JSON_UNESCAPED_UNICODE),
            'footer_contact_title'         => $data['footer_contact_title'] ?? '',
            'footer_email'                 => $data['footer_email'] ?? '',
            'footer_phone'                 => $data['footer_phone'] ?? '',
            'footer_address'               => $data['footer_address'] ?? '',
            'footer_show_contact_link'     => ($data['footer_show_contact_link'] ?? true) ? '1' : '0',
            'footer_contact_link_text'     => $data['footer_contact_link_text'] ?? '',
            'footer_enamad_enabled'        => ($data['footer_enamad_enabled'] ?? true) ? '1' : '0',
            'footer_enamad_html'           => $data['footer_enamad_html'] ?? '',
            'footer_copyright'             => $data['footer_copyright'] ?? '',
            'contact_seo_title'            => $data['contact_seo_title'] ?? 'تماس با ما',
            'contact_page_title'           => $data['contact_page_title'] ?? 'تماس با ما',
            'contact_page_subtitle'        => $data['contact_page_subtitle'] ?? '',
            'contact_info_items'           => json_encode($data['contact_info_items'] ?? [], JSON_UNESCAPED_UNICODE),
            'contact_form_enabled'         => ($data['contact_form_enabled'] ?? true) ? '1' : '0',
            'contact_form_title'           => $data['contact_form_title'] ?? '',
            'contact_form_name_label'      => $data['contact_form_name_label'] ?? '',
            'contact_form_mobile_label'    => $data['contact_form_mobile_label'] ?? '',
            'contact_form_subject_label'   => $data['contact_form_subject_label'] ?? '',
            'contact_form_message_label'   => $data['contact_form_message_label'] ?? '',
            'contact_form_name_placeholder'=> $data['contact_form_name_placeholder'] ?? '',
            'contact_form_mobile_placeholder'=> $data['contact_form_mobile_placeholder'] ?? '',
            'contact_form_subject_placeholder'=> $data['contact_form_subject_placeholder'] ?? '',
            'contact_form_message_placeholder'=> $data['contact_form_message_placeholder'] ?? '',
            'contact_form_submit_text'     => $data['contact_form_submit_text'] ?? '',
            'contact_form_loading_text'    => $data['contact_form_loading_text'] ?? '',
            'contact_form_success_message' => $data['contact_form_success_message'] ?? '',
            'about_seo_title'              => $data['about_seo_title'] ?? 'درباره ما',
            'about_hero_enabled'           => ($data['about_hero_enabled'] ?? true) ? '1' : '0',
            'about_hero_title'             => $data['about_hero_title'] ?? '',
            'about_hero_description'       => $data['about_hero_description'] ?? '',
            'about_hero_image'             => $this->extractUploadPath($data['about_hero_image'] ?? null) ?? '',
            'about_mission_enabled'        => ($data['about_mission_enabled'] ?? true) ? '1' : '0',
            'about_mission_title'          => $data['about_mission_title'] ?? '',
            'about_mission_paragraph1'     => $data['about_mission_paragraph1'] ?? '',
            'about_mission_paragraph2'     => $data['about_mission_paragraph2'] ?? '',
            'about_mission_image'          => $this->extractUploadPath($data['about_mission_image'] ?? null) ?? '',
            'about_mission_stats'          => json_encode($data['about_mission_stats'] ?? [], JSON_UNESCAPED_UNICODE),
            'about_values_enabled'         => ($data['about_values_enabled'] ?? true) ? '1' : '0',
            'about_values_title'           => $data['about_values_title'] ?? '',
            'about_values'                 => json_encode($data['about_values'] ?? [], JSON_UNESCAPED_UNICODE),
            'about_cta_enabled'            => ($data['about_cta_enabled'] ?? true) ? '1' : '0',
            'about_cta_title'              => $data['about_cta_title'] ?? '',
            'about_cta_subtitle'           => $data['about_cta_subtitle'] ?? '',
            'about_cta_button_text'        => $data['about_cta_button_text'] ?? '',
            'about_cta_button_route'       => $data['about_cta_button_route'] ?? 'courses.index',
        ], 'theme');

        $this->fillFormFromSettings();

        Notification::make()->title('تنظیمات قالب ذخیره شد ✅')->success()->send();
    }

    protected function normalizeContentCards(array $cards): array
    {
        return array_values(array_map(function (array $card) {
            $items = $card['items'] ?? [];
            if (isset($items[0]) && is_array($items[0]) && array_key_exists('item', $items[0])) {
                $items = array_values(array_filter(array_map(fn ($row) => $row['item'] ?? null, $items)));
            }

            return [
                'emoji' => $card['emoji'] ?? '',
                'title' => $card['title'] ?? '',
                'items' => array_values(array_filter($items)),
            ];
        }, $cards));
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
            Action::make('save')->label('ذخیره تنظیمات')->submit('save')
                ->icon('heroicon-o-check')->color('primary'),
        ];
    }
}
