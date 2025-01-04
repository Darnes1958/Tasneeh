<?php

namespace App\Livewire\widgets;


use App\Models\Sell;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaSell extends BaseWidget
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
      $this->dispatch('sellQuery',par: $this->getTableQueryForExport()->get());

  }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;
      $this->dispatch('sellQuery',par: $this->getTableQueryForExport()->get());

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
            ->query(function(Sell $rec){

                $rec=Sell::when($this->repDate1,function ($q){
                  $q->where('order_date','>=',$this->repDate1);
                })
                  ->when($this->repDate2,function ($q){
                    $q->where('order_date','<=',$this->repDate2);
                  })
                    ->join('halls','hall_id','halls.id')
                    ->selectRaw('halls.name, sum(tot-ksm) as tot,sum(pay) as pay,sum(tot-ksm-pay) as baky')
                    ->groupBy('halls.name');

                return $rec;
            }

            )
            ->emptyStateHeading('لا توجد بيانات')
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">المبيعات</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
          ->defaultPaginationPageOption(5)
            ->defaultSort('tot')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نقطة البيع')
                    ->color('info'),
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
