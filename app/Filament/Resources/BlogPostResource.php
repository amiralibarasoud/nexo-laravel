<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'مقالات';
    protected static ?string $modelLabel      = 'مقاله';
    protected static ?string $pluralModelLabel = 'مقالات';
    protected static ?string $navigationGroup = 'بلاگ';
    protected static ?int $navigationSort     = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('مقاله')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('محتوا')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان مقاله')->required()->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('نامک (Slug)')->required()->unique(ignoreRecord: true),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('blog_category_id')
                            ->label('دسته‌بندی')
                            ->relationship('category', 'name')
                            ->searchable()->preload()->nullable(),
                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options(['draft' => 'پیش‌نویس', 'published' => 'منتشر شده'])
                            ->default('draft')->required(),
                    ]),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('خلاصه مقاله (نمایش در لیست)')
                        ->rows(2)->maxLength(500)
                        ->helperText('اگر خالی باشد، از ابتدای متن استفاده می‌شود.'),

                    Forms\Components\RichEditor::make('body')
                        ->label('متن کامل مقاله')->required()->columnSpanFull()
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('blog/attachments'),
                ]),

                Forms\Components\Tabs\Tab::make('تصویر و زمان')->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('تصویر کاور')->image()
                        ->disk('public')->directory('blog/covers')
                        ->imageResizeMode('cover')->imageCropAspectRatio('16:9')
                        ->maxSize(4096)
                        ->helperText('حداکثر ۴ مگابایت — نسبت ۱۶:۹'),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('تاریخ انتشار')
                        ->helperText('خالی = فوری منتشر می‌شود'),
                ]),

                Forms\Components\Tabs\Tab::make('سئو')->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->label('عنوان سئو (Meta Title)')
                        ->maxLength(60)
                        ->helperText('پیشنهاد: ۵۰-۶۰ کاراکتر — اگر خالی باشد، عنوان مقاله استفاده می‌شود'),
                    Forms\Components\Textarea::make('meta_description')
                        ->label('توضیحات سئو (Meta Description)')
                        ->rows(3)->maxLength(160)
                        ->helperText('پیشنهاد: ۱۲۰-۱۶۰ کاراکتر — اگر خالی باشد، خلاصه مقاله استفاده می‌شود'),
                    Forms\Components\TextInput::make('meta_keywords')
                        ->label('کلمات کلیدی (Meta Keywords)')
                        ->placeholder('مثال: مدیریت پول، پس‌انداز، سرمایه‌گذاری')
                        ->helperText('با کاما جدا کنید'),
                ]),

            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('تصویر')->disk('public')->size(56),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')->searchable()->sortable()->limit(40)->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('دسته')->badge()->color('primary')->placeholder('—'),
                Tables\Columns\TextColumn::make('views')
                    ->label('بازدید')
                    ->formatStateUsing(fn($state) => toFarsiNumber($state))
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->label('وضعیت')
                    ->options(['draft' => 'پیش‌نویس', 'published' => 'منتشر']),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاریخ انتشار')
                    ->formatStateUsing(fn($state) => $state ? toJalali($state, 'Y/m/d') : '—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(['draft' => 'پیش‌نویس', 'published' => 'منتشر']),
                Tables\Filters\SelectFilter::make('blog_category_id')
                    ->label('دسته‌بندی')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit'   => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
