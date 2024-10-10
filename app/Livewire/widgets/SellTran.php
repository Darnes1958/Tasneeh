<?php

namespace App\Livewire\widgets;

use App\Models\Sell_tran;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SellTran extends BaseWidget
{
    public function mount($sell_id){
        $this->sell_id=$sell_id;
    }
    protected static ?string $heading='';
    public $sell_id;
    public function table(Table $table): Table
    {
        return $table
            ->query(function (Sell_tran $sell_tran){
                return Sell_tran::where('sell_id',$this->sell_id);

            })
            ->queryStringIdentifier('sell_tran_view')
            ->columns([
                TextColumn::make('product_id')
                    ->label('رقم المنتج')
                    ->sortable(),
                TextColumn::make('Product.name')
                    ->color('primary')
                    ->label('اسم المنتج'),
                TextColumn::make('q')
                    ->label('الكمية')
                    ->sortable(),
                TextColumn::make('p')
                    ->label('السعر ')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),

                TextColumn::make('sub_tot')
                    ->label('المجموع ')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),

            ]);
    }
}
