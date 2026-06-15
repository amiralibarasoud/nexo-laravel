<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Category;
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
            Forms\Components\Tabs::make('دوره')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('اطلاعات اصلی')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('عنوان دوره')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),

                                Forms\Components\TextInput::make('slug')
                                    ->label('نامک (Slug)')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                            Forms\Components\Select::make('category_id')
                                ->label('دسته‌بندی')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),

                            Forms\Components\Textarea::make('short_description')
                                ->label('توضیح کوتاه')
                                ->rows(2)
                                ->maxLength(500),

                            Forms\Components\RichEditor::make('description')
                                ->label('توضیحات کامل')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('تصاویر و مدرس')
                        ->schema([
                            Forms\Components\FileUpload::make('cover_image')
                                ->label('تصویر کاور')
                                ->image()
                                ->directory('courses/covers')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('16:9'),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('instructor_name')
                                    ->label('نام مدرس'),

                                Forms\Components\FileUpload::make('instructor_avatar')
                                    ->label('تصویر مدرس')
                                    ->image()
                                    ->directory('instructors'),
                            ]),

                            Forms\Components\Textarea::make('instructor_bio')
                                ->label('بیوگرافی مدرس')
                                ->rows(3),
                        ]),

                    Forms\Components\Tabs\Tab::make('قیمت‌گذاری')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('قیمت اصلی (تومان)')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->suffix('تومان'),

                                Forms\Components\TextInput::make('discounted_price')
                                    ->label('قیمت با تخفیف (تومان)')
                                    ->numeric()
                                    ->suffix('تومان'),

                                Forms\Components\DateTimePicker::make('discount_expires_at')
                                    ->label('انقضای تخفیف')
                                    ->jalali(),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Toggle::make('has_text')
                                    ->label('دارای محتوای متنی')
                                    ->default(true),

                                Forms\Components\Toggle::make('has_audio')
                                    ->label('دارای محتوای صوتی'),
                            ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('تنظیمات')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('level')
                                    ->label('سطح دوره')
                                    ->options([
                                        'beginner' => 'مقدماتی',
                                        'intermediate' => 'متوسط',
                                        'advanced' => 'پیشرفته',
                                        'all' => 'همه سطوح',
                                    ])
                                    ->default('all')
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('وضعیت')
                                    ->options([
                                        'draft' => 'پیش‌نویس',
                                        'published' => 'منتشر شده',
                                        'archived' => 'آرشیو',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('دوره ویژه'),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('تاریخ انتشار')
                                    ->jalali(),
                            ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('تصویر')
                    ->circular(false)
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('دسته‌بندی')
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->label('قیمت')
                    ->formatStateUsing(fn($state) => price($state)),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('دانش‌آموز')
                    ->formatStateUsing(fn($state) => toFarsiNumber($state)),

                Tables\Columns\IconColumn::make('has_text')
                    ->label('متن')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_audio')
                    ->label('صوت')
                    ->boolean(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش‌نویس',
                        'published' => 'منتشر شده',
                        'archived' => 'آرشیو',
                    ]),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش‌نویس',
                        'published' => 'منتشر شده',
                        'archived' => 'آرشیو',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف انتخاب‌شده‌ها'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
