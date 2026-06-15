<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'جلسات';
    protected static ?string $modelLabel = 'جلسه';
    protected static ?string $pluralModelLabel = 'جلسات';
    protected static ?string $navigationGroup = 'مدیریت دوره‌ها';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('course_id')
                    ->label('دوره')->required()
                    ->relationship('course', 'title')->searchable()->preload()
                    ->live()
                    ->afterStateUpdated(fn($set) => $set('section_id', null)),

                Forms\Components\Select::make('section_id')
                    ->label('فصل')
                    ->relationship('section', 'title')
                    ->searchable()->preload()
                    ->nullable(),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتیب')->numeric()->default(0),
            ]),

            Forms\Components\TextInput::make('title')
                ->label('عنوان جلسه')->required()->maxLength(255)->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label('توضیح کوتاه')->rows(2)->columnSpanFull(),

            Forms\Components\Tabs::make('محتوا')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('محتوای متنی')->schema([
                    Forms\Components\RichEditor::make('text_content')
                        ->label('متن جلسه')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('lessons/attachments')
                        ->helperText('محتوای کامل جلسه را اینجا بنویسید.'),
                ]),

                Forms\Components\Tabs\Tab::make('فایل صوتی')->schema([
                    Forms\Components\FileUpload::make('audio_path')
                        ->label('فایل صوتی MP3')
                        ->disk('local')
                        ->directory('private/lessons/audio')
                        ->visibility('private')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'])
                        ->maxSize(102400)
                        ->helperText('حداکثر ۱۰۰ مگابایت — فرمت MP3/WAV/M4A')
                        ->downloadable(false),

                    Forms\Components\TextInput::make('audio_duration_seconds')
                        ->label('مدت زمان (ثانیه)')->numeric()->nullable()
                        ->helperText('مدت زمان فایل صوتی به ثانیه — مثلاً ۳۶۰۰ برای ۱ ساعت'),
                ]),

            ]),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Toggle::make('is_preview')
                    ->label('پیش‌نمایش رایگان')
                    ->helperText('این جلسه بدون خرید قابل مشاهده باشد'),
                Forms\Components\Toggle::make('is_published')
                    ->label('منتشر شده')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.title')
                    ->label('دوره')->searchable()->sortable()->limit(25)->badge()->color('primary'),
                Tables\Columns\TextColumn::make('section.title')
                    ->label('فصل')->limit(20)->placeholder('—'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')->sortable()->alignCenter()->width('50px'),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان جلسه')->searchable()->sortable()->limit(40)->weight('semibold'),
                Tables\Columns\IconColumn::make('text_content')
                    ->label('متن')->boolean()->getStateUsing(fn(\App\Models\Lesson $record) => !empty($record->text_content)),
                Tables\Columns\IconColumn::make('audio_path')
                    ->label('صوت')->boolean()->getStateUsing(fn(\App\Models\Lesson $record) => !empty($record->audio_path)),
                Tables\Columns\TextColumn::make('audio_duration_seconds')
                    ->label('مدت')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '—';
                        $minutes = intdiv((int) $state, 60);
                        $seconds = (int) $state % 60;
                        return sprintf('%d:%02d', $minutes, $seconds);
                    }),
                Tables\Columns\IconColumn::make('is_preview')->label('رایگان')->boolean(),
                Tables\Columns\IconColumn::make('is_published')->label('منتشر')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')->label('دوره')
                    ->relationship('course', 'title')->searchable()->preload(),
                Tables\Filters\TernaryFilter::make('is_published')->label('منتشر شده'),
                Tables\Filters\TernaryFilter::make('is_preview')->label('پیش‌نمایش رایگان'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('course_id')->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit'   => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
