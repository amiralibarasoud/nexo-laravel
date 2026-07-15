<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'دوره‌ها';
    protected static ?string $modelLabel = 'دوره';
    protected static ?string $pluralModelLabel = 'دوره‌ها';
    protected static ?string $navigationGroup = 'مدیریت دوره‌ها';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('دوره')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('اطلاعات اصلی')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان دوره')->required()->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('نامک')->required()->unique(ignoreRecord: true),
                    ]),
                    Forms\Components\Select::make('category_id')
                        ->label('دسته‌بندی')->relationship('category', 'name')->searchable()->preload(),
                    Forms\Components\Textarea::make('short_description')
                        ->label('توضیح کوتاه')->rows(2)->maxLength(500),
                    Forms\Components\RichEditor::make('description')
                        ->label('توضیحات کامل')->columnSpanFull()
                        ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('courses/content'),
                ]),

                Forms\Components\Tabs\Tab::make('تصاویر و مدرس')->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('تصویر کاور')->image()
                        ->disk('public')->directory('courses/covers')
                        ->imageResizeMode('cover')->imageCropAspectRatio('16:9')
                        ->maxSize(5120)->helperText('حداکثر ۵ مگابایت — نسبت ۱۶:۹'),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('instructor_name')->label('نام مدرس'),
                        Forms\Components\FileUpload::make('instructor_avatar')
                            ->label('تصویر مدرس')->image()
                            ->disk('public')->directory('instructors')->maxSize(2048),
                    ]),
                    Forms\Components\Textarea::make('instructor_bio')->label('بیوگرافی مدرس')->rows(3),
                ]),

                Forms\Components\Tabs\Tab::make('قیمت‌گذاری')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Toggle::make('has_text')
                            ->label('محتوای متنی')
                            ->default(true)
                            ->live(),
                        Forms\Components\Toggle::make('has_audio')
                            ->label('محتوای صوتی')
                            ->live(),
                        Forms\Components\Toggle::make('is_featured')->label('دوره ویژه'),
                    ]),
                    Forms\Components\Section::make('قیمت بر اساس نوع محتوا')
                        ->description('برای هر نوع محتوایی که فعال است، قیمت جداگانه تعیین کنید.')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('price_text')
                                    ->label('قیمت محتوای متنی (تومان)')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->nullable()
                                    ->suffix('تومان')
                                    ->visible(fn (Forms\Get $get): bool => (bool) $get('has_text'))
                                    ->required(fn (Forms\Get $get): bool => (bool) $get('has_text'))
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('price_audio')
                                    ->label('قیمت محتوای صوتی (تومان)')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->nullable()
                                    ->suffix('تومان')
                                    ->visible(fn (Forms\Get $get): bool => (bool) $get('has_audio'))
                                    ->required(fn (Forms\Get $get): bool => (bool) $get('has_audio'))
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('price_both')
                                    ->label('قیمت هر دو (متن + صوت) (تومان)')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->nullable()
                                    ->suffix('تومان')
                                    ->visible(fn (Forms\Get $get): bool => (bool) $get('has_text') && (bool) $get('has_audio'))
                                    ->required(fn (Forms\Get $get): bool => (bool) $get('has_text') && (bool) $get('has_audio'))
                                    ->dehydrated(),
                            ]),
                        ]),
                    Forms\Components\Section::make('تخفیف دوره')
                        ->description('قیمت با تخفیف را نسبت به کمترین قیمت محتوا وارد کنید؛ نسبت تخفیف روی همه نوع‌های محتوا اعمال می‌شود.')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('discounted_price')
                                    ->label('کمترین قیمت با تخفیف (تومان)')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->suffix('تومان')
                                    ->dehydrateStateUsing(fn ($state) => ($state === '' || $state === null) ? null : (int) $state),
                                jalaliDatePicker('discount_expires_at', 'انقضای تخفیف'),
                            ]),
                        ]),
                    Forms\Components\Hidden::make('price')->default(0)->dehydrated(),
                ]),

                Forms\Components\Tabs\Tab::make('تنظیمات')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('level')->label('سطح دوره')
                            ->options(['beginner'=>'مقدماتی','intermediate'=>'متوسط','advanced'=>'پیشرفته','all'=>'همه سطوح'])
                            ->default('all')->required(),
                        Forms\Components\Select::make('status')->label('وضعیت')
                            ->options(['draft'=>'پیش‌نویس','published'=>'منتشر شده','archived'=>'آرشیو'])
                            ->default('draft')->required(),
                        jalaliDatePicker('published_at', 'تاریخ انتشار'),
                    ]),
                ]),

            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')->label('تصویر')->disk('public')->size(56)->defaultImageUrl(asset('images/placeholder.png')),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable()->sortable()->limit(35)->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')->label('دسته')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('starting_price')
                    ->label('از قیمت')
                    ->formatStateUsing(function ($state, Course $record) {
                        $amount = (int) ($state ?? $record->price ?? 0);

                        return $record->has_variable_pricing ? 'از ' . price($amount) : price($amount);
                    })
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('price', $direction)),
                Tables\Columns\TextColumn::make('students_count')->label('دانش‌آموز')->formatStateUsing(fn($state) => toFarsiNumber($state))->sortable(),
                Tables\Columns\IconColumn::make('has_text')->label('متن')->boolean(),
                Tables\Columns\IconColumn::make('has_audio')->label('صوت')->boolean(),
                Tables\Columns\SelectColumn::make('status')->label('وضعیت')
                    ->options(['draft'=>'پیش‌نویس','published'=>'منتشر','archived'=>'آرشیو']),
                Tables\Columns\IconColumn::make('is_featured')->label('ویژه')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('وضعیت')
                    ->options(['draft'=>'پیش‌نویس','published'=>'منتشر','archived'=>'آرشیو']),
                Tables\Filters\SelectFilter::make('category')->label('دسته')->relationship('category','name'),
            ])
            ->actions([
                Tables\Actions\Action::make('lessons')
                    ->label('جلسات')->icon('heroicon-o-list-bullet')
                    ->url(fn(Course $record) => route('filament.admin.resources.lessons.index', ['tableFilters[course][value]' => $record->id]))
                    ->color('gray'),
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()->label('حذف')])])
            ->defaultSort('created_at','desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit'   => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
