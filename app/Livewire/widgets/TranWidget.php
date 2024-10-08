<?php

namespace App\Livewire\widgets;


use App\Models\Tran;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TranWidget extends BaseWidget
{
    public function mount($factory_id){
        $this->factory_id=$factory_id;
    }
    protected static ?string $heading='';
    public $factory_id;
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                return Tran::where('factory_id',$this->factory_id);

            })
            ->queryStringIdentifier('factory_tran_view')
            ->columns([
                TextColumn::make('item_id')
                    ->label('رقم الصنف')
                    ->sortable(),
                TextColumn::make('Item.name')
                    ->color('primary')
                    ->label('اسم الصنف'),
                TextColumn::make('quant')
                    ->label('الكمية')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('سعر الشراء')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('sub_tot')
                    ->label('الاجمالي')
                    ->numeric(
                        decimalPlaces: 3,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),


            ]);
    }
}
