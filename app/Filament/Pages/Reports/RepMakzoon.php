<?php

namespace App\Filament\Pages\Reports;

use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Setting;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder;

class RepMakzoon extends Page implements HasTable

{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.rep-makzoon';
    protected static ?string $navigationLabel='تقرير عن المخزون';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=6;
    protected ?string $heading="";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }


    public array $data_list= [
        'calc_columns' => [
            'Item.price_cost',
            'Item.price_buy',
        ],
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Place_stock $place_stock){
                $place_stock=Place_stock::query();
                return $place_stock;
            })
            ->columns([
                TextColumn::make('Place.name')
                    ->sortable()
                    ->searchable()
                    ->label('المكان'),
                TextColumn::make('item_id')
                    ->sortable()
                    ->searchable()
                    ->label('رقم الصنف'),
                TextColumn::make('Item.name')
                    ->sortable()
                    ->searchable()
                    ->label('اسم الصنف'),
                TextColumn::make('Item.stock')
                    ->label('الرصيد الكلي'),
                TextColumn::make('stock')
                    ->label('رصيد المكان'),
                TextColumn::make('Item.price_buy')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('سعر الشراء'),
                TextColumn::make('Item.price_cost')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('سعر المتوسط'),
                TextColumn::make('sub_input')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('اجمالي الشراء'),
                TextColumn::make('sub_cost')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('اجمالي المتوسط'),

            ])
       //     ->contentFooter(view('table.footer', $this->data_list))
            ->striped();
    }
}
