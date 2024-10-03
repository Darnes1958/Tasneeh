<?php

namespace App\Livewire\widgets;

use App\Models\Buy_tran;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BuyTran extends BaseWidget
{
    public function mount($buy_id){
        $this->buy_id=$buy_id;
    }
    protected static ?string $heading='';
    public $buy_id;
    public function table(Table $table): Table
    {
        return $table
            ->query(function (Buy_tran $buy_tran){
                return Buy_tran::where('buy_id',$this->buy_id);

            })
            ->queryStringIdentifier('buy_tran_view')
            ->columns([
                TextColumn::make('item_id')
                    ->label('رقم الصنف')
                    ->sortable(),
                TextColumn::make('Item.name')
                    ->label('اسم الصنف'),
                TextColumn::make('quant')
                    ->label('الكمية')
                    ->sortable(),
                TextColumn::make('price_input')
                    ->label('سعر الشراء')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('price_cost')
                    ->label('سعر التكلفة')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('sub_input')
                    ->label('المجموع')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
            ]);
    }
}
