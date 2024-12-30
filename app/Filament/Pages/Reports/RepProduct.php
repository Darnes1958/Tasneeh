<?php

namespace App\Filament\Pages\Reports;

use App\Models\Customer;
use App\Models\Hall;
use App\Models\Hall_stock;
use App\Models\Product;
use App\Models\views\Rep_makzoone;
use Faker\Core\Uuid;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RepProduct extends Page implements HasTable

{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.rep-product';
    protected static ?string $navigationLabel='تقرير عن مخزون المنتجات';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=4;
    protected ?string $heading="";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }
    public function getTableRecordKey(Model $record): string
    {
        return Uuid::class;
    }




    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $place_stock=Hall_stock::query();
                return $place_stock;
            })

            ->columns([
                TextColumn::make('Hall.name')
                    ->sortable()
                    ->searchable()
                    ->label('المكان'),
                TextColumn::make('Product.id')
                    ->sortable()
                    ->searchable()
                    ->label('رقم المنتج'),
                TextColumn::make('Product.name')
                    ->sortable()
                    ->searchable()
                    ->label('اسم المنتج'),


                TextColumn::make('stock')
                    ->label('رصيد المكان'),
                TextColumn::make('Product.stock')
                   ->label('الرصيد الكلي'),

                TextColumn::make('Product.cost')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('تكلفة المنتج'),
                TextColumn::make('Product.price')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('السعر'),
                ImageColumn::make('Product.image')
                    ->circular()
                    ->label(''),
            ])
            ->filters([
                SelectFilter::make('hall_id')
                    ->searchable()
                    ->placeholder('كل اماكن التخزين')
                    ->label('')
                    ->options(Hall::all()->pluck('name','id'))
                    ->preload(),
                SelectFilter::make('product_id')
                    ->searchable()
                    ->placeholder('كل المنتجات')
                    ->label('')
                    ->options(Product::all()->pluck('name','id'))
                    ->preload(),
                Filter::make('anyfilter')
                    ->form([
                        Checkbox::make('showZero')
                            ->label('اطهار الاصفار'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ! $data['showZero'],
                                fn (Builder $query, $date): Builder => $query->where('stock','!=',0),
                            );
                    })
                    ,


            ], layout: FiltersLayout::AboveContent)
            ->filtersFormWidth(MaxWidth::SevenExtraLarge)
            ->filtersFormColumns(4)
            ->striped();
    }
}
