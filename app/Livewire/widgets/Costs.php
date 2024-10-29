<?php

namespace App\Livewire\widgets;

use App\Models\Acc;
use App\Models\Cost;
use App\Models\Kazena;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Costs extends BaseWidget
{
    public function mount($buy_id){
        $this->buy_id=$buy_id;
    }
    protected static ?string $heading='';
    public $buy_id;
    public function table(Table $table): Table
    {
        return $table
            ->query(
               function (Cost $cost){
                   return Cost::where('buy_id',$this->buy_id);
               }
            )
            ->queryStringIdentifier('costs_view')
            ->columns([
                TextColumn::make('Costtype.name')
                    ->label('البيان')
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('val')
                    ->label('المبلغ')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->sortable(),
                TextColumn::make('payBy')
                    ->label('بواسطة')
                    ->state(function (Cost $record) {
                        $name=null;
                        if ($record->acc_id) {$name=Acc::find($record->acc_id)->name;}
                        if ($record->kazena_id) {$name=Kazena::find($record->kazena_id)->name;}
                        return $name;
                    })

            ]);
    }
}
