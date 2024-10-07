<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label='منتجات';
    protected static ?string $pluralLabel='منتج';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->live()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ]),
                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('Category','name')
                    ->required()
                    ->createOptionForm([
                        Section::make('ادخال تصنيف منتجات')
                            ->description('ادخال تصنيف  (صالون , دولاب . طاولة .... الخ)')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique()
                                    ->label('الاسم'),
                            ])
                    ])
                    ->editOptionForm([
                        Section::make('تعديل تصنيف ')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique()
                                    ->label('الاسم'),
                            ])->columns(2)
                    ]),
                Textarea::make('description')
                    ->label('الوصف (اختياري)'),
                FileUpload::make('image')
                    ->directory('productImages')
                    ->label('صورة'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Category.name')
                    ->label('التصنيف')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->placeholder('الصورة')
                    ->label('')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
