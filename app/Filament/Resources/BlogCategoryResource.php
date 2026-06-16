<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCategoryResource\Pages;
use App\Models\BlogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;
    protected static ?string $navigationIcon  = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'دسته‌بندی‌های بلاگ';
    protected static ?string $modelLabel      = 'دسته‌بندی';
    protected static ?string $pluralModelLabel = 'دسته‌بندی‌های بلاگ';
    protected static ?string $navigationGroup = 'بلاگ';
    protected static ?int $navigationSort     = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام دسته')->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->label('نامک')->required()->unique(ignoreRecord: true),
                Forms\Components\ColorPicker::make('color')->label('رنگ'),
                Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            ]),
            Forms\Components\Textarea::make('description')
                ->label('توضیحات')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')->label('رنگ'),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('published_posts_count')
                    ->counts('publishedPosts')->label('مقالات'),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتیب')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->reorderable('sort_order')->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogCategories::route('/'),
            'create' => Pages\CreateBlogCategory::route('/create'),
            'edit'   => Pages\EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
