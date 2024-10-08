<?php

namespace App\Livewire\widgets;

use App\Models\Hand;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;

class HandWidget extends BaseWidget
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
                return Hand::where('factory_id',$this->factory_id);
            })
            ->queryStringIdentifier('hand_tran_view')

            ->columns([

                TextColumn::make('Man.name')
                    ->color('primary')
                    ->label('الاسم '),
                TextColumn::make('val')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )->suffix(new HtmlString('<label class="text-primary-600">&nbsp;&nbsp;اجمالي تكلفة التشغيل</label>')))
                    ->label('المبلغ')
                    ->sortable(),
            ]);
    }
}
