<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'دسته‌بندی‌ها';
    protected static ?string $modelLabel = 'دسته‌بندی';
    protected static ?string $pluralModelLabel = 'دسته‌بندی‌ها';
    protected static ?string $navigationGroup = 'مدیریت دوره‌ها';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->label('نام')->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label('نامک')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('icon')->label('آیکون (ایموجی)')->placeholder('💻'),
                Forms\Components\ColorPicker::make('color')->label('رنگ'),
                Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            ]),
            Forms\Components\Textarea::make('description')->label('توضیحات')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')->label('آیکون')->alignCenter(),
                Tables\Columns\ColorColumn::make('color')->label('رنگ'),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('courses_count')->counts('courses')->label('دوره‌ها'),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتیب')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('ویرایش')])
            ->reorderable('sort_order')->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
