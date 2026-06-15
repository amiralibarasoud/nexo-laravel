<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'کدهای تخفیف';
    protected static ?string $modelLabel = 'کد تخفیف';
    protected static ?string $pluralModelLabel = 'کدهای تخفیف';
    protected static ?string $navigationGroup = 'مدیریت مالی';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('code')
                    ->label('کد تخفیف')->required()->maxLength(50)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => $set('code', strtoupper($state)))
                    ->placeholder('مثال: NEXO20')
                    ->helperText('کد به صورت خودکار به حروف بزرگ تبدیل می‌شود'),

                Forms\Components\TextInput::make('description')
                    ->label('توضیح')->maxLength(200)
                    ->placeholder('مثال: تخفیف ویژه دانشجویان'),
            ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع تخفیف')->required()
                    ->options(['percent' => 'درصدی (%)', 'fixed' => 'مبلغ ثابت (تومان)'])
                    ->default('percent')
                    ->live(),

                Forms\Components\TextInput::make('value')
                    ->label('مقدار تخفیف')->required()->numeric()
                    ->suffix(fn($get) => $get('type') === 'percent' ? '٪' : 'تومان')
                    ->helperText(fn($get) => $get('type') === 'percent' ? 'بین ۱ تا ۱۰۰' : 'مبلغ به تومان'),

                Forms\Components\TextInput::make('max_amount')
                    ->label('سقف تخفیف (تومان)')->numeric()->nullable()
                    ->suffix('تومان')
                    ->helperText('فقط برای نوع درصدی — خالی = بدون سقف')
                    ->visible(fn($get) => $get('type') === 'percent'),
            ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('min_order_amount')
                    ->label('حداقل مبلغ سفارش (تومان)')->numeric()->default(0)->suffix('تومان'),

                Forms\Components\TextInput::make('usage_limit')
                    ->label('سقف استفاده کل')->numeric()->nullable()
                    ->helperText('خالی = بدون محدودیت'),

                Forms\Components\TextInput::make('per_user_limit')
                    ->label('سقف استفاده هر کاربر')->numeric()->default(1),
            ]),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
                jalaliDatePicker('starts_at', 'تاریخ شروع')->nullable(),
                jalaliDatePicker('expires_at', 'تاریخ انقضا')->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('کد')->searchable()->copyable()
                    ->weight('bold')->fontFamily('mono'),

                Tables\Columns\TextColumn::make('description')
                    ->label('توضیح')->limit(30)->placeholder('—'),

                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->color(fn($state) => $state === 'percent' ? 'success' : 'info')
                    ->formatStateUsing(fn($state) => $state === 'percent' ? 'درصدی' : 'مبلغ ثابت'),

                Tables\Columns\TextColumn::make('value')
                    ->label('مقدار')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->type === 'percent'
                            ? toFarsiNumber($state) . '٪'
                            : price($state)
                    ),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label('استفاده شده')
                    ->formatStateUsing(fn($state, $record) =>
                        toFarsiNumber($state) . ($record->usage_limit ? ' از ' . toFarsiNumber($record->usage_limit) : '')
                    ),

                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('انقضا')
                    ->formatStateUsing(fn($state) => $state ? toJalali($state, 'Y/m/d') : 'بدون انقضا')
                    ->color(fn($state, $record) => $record->expires_at?->isPast() ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('وضعیت'),
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
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
