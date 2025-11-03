<?php

namespace App\Filament\Pages\Reports;

use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Setting;
use App\Models\views\Rep_makzoone;
use Faker\Core\Uuid;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder;

class RepMakzoon extends Page implements HasTable

{
    use InteractsWithTable;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.reports.rep-makzoon';
    protected static ?string $navigationLabel='تقرير عن مخزون الاصناف';
    protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=3;
    protected ?string $heading="";

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }
    public function getTableRecordKey(Model|array $record): string
    {
        return Uuid::class;
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
            ->query(function (){
                $place_stock=Rep_makzoone::query();
                return $place_stock;
            })
            ->defaultSort('item_id')
            ->columns([
                TextColumn::make('placeName')
                    ->sortable()
                    ->searchable()
                    ->label('المكان'),
                TextColumn::make('item_id')
                    ->sortable()
                    ->searchable()
                    ->label('رقم الصنف'),
                TextColumn::make('itemName')
                    ->sortable()
                    ->searchable()
                    ->label('اسم الصنف'),
                TextColumn::make('itemStock')
                    ->label('الرصيد الكلي'),
                TextColumn::make('placeStock')
                    ->label('رصيد المكان'),
                TextColumn::make('price_cost')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('سعر المتوسط'),
                TextColumn::make('sub_cost')
                    ->summarize(Sum::make()->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
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
