<?php

namespace App\Livewire\widgets;

use App\Models\Buy;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaBuy extends BaseWidget
{
  public $repDate1;
  public $repDate2;
  public function mount(){
    $this->repDate1=now();
    $this->repDate2=now();
  }
    #[On('updateDate1')]
    public function updatedate1($repdate)
    {
        $this->repDate1=$repdate;
        $this->dispatch('buyQuery',buy: $this->getTableQueryForExport()->get());
    }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;
      $this->dispatch('buyQuery',buy: $this->getTableQueryForExport()->get());

  }
    public array $data_list= [
        'calc_columns' => [
            'tot',
            'pay',
            'baky',
        ],
    ];
    public function getTableRecordKey(Model $record): string
    {
        return uniqid();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function(Buy $buy){
               $buy=Buy::when($this->repDate1,function ($q){
                 $q->where('order_date','>=',$this->repDate1);
               })
                 ->when($this->repDate2,function ($q){
                   $q->where('order_date','<=',$this->repDate2);
                 })
                   ->join('places','place_id','places.id')
                   ->selectRaw('places.name, sum(tot-ksm) as tot,sum(pay) as pay,sum(tot-ksm-pay) as baky')
                   ->groupBy('places.name');

               return $buy;
            }

            )
          ->emptyStateHeading('لا توجد بيانات')
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">المشتريات</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
          ->defaultPaginationPageOption(5)
            ->defaultSort('tot')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                 ->color('info')
                 ->label('نقطة البيع'),
                Tables\Columns\TextColumn::make('tot')
                 ->numeric(decimalPlaces: 2,
                     decimalSeparator: '.',
                     thousandsSeparator: ',')
                 ->label('الإجمالي'),
                Tables\Columns\TextColumn::make('pay')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('المدفوع'),
                Tables\Columns\TextColumn::make('baky')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->label('الباقي'),

            ]);
    }
}
