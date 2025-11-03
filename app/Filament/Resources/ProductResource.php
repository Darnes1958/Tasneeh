<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Factory;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static ?string $label='منتجات';
    protected static ?int $navigationSort=2;
    protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
    protected static ?string $pluralLabel='منتجات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                                    ->unique(ignoreRecord: true)
                                    ->label('الاسم'),
                            ])->columns(2)
                    ]),
                Textarea::make('description')
                    ->label('الوصف (اختياري)'),
                FileUpload::make('image')
                    ->directory('productImages')
                    ->label('صورة'),
                Hidden::make('user_id')
                    ->default(auth()->id()),
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
                TextColumn::make('price')
                    ->label('السعر')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cost')
                    ->label('التكلفة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('الرصيد')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->placeholder('الصورة')
                    ->label(''),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                 ->hidden(fn(Model $record): bool => Factory::where('product_id',$record->id)->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => !Factory::where('product_id',$record->id)->exists(),
            );
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
