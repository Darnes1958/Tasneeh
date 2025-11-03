<?php

namespace App\Livewire\widgets;


use Filament\Tables\Columns\TextColumn;
use App\Models\Receipt;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class KlasaCust extends BaseWidget
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
      $this->dispatch('custQuery',par: $this->getTableQueryForExport()->get());

  }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;
      $this->dispatch('custQuery',par: $this->getTableQueryForExport()->get());

  }
    public array $data_list= [
        'calc_columns' => [
            'val',
            'exp',
        ],
    ];
    public function getTableRecordKey(Model|array $record): string
    {
        return uniqid();
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function(){

                $first=Receipt::
                    when($this->repDate1,function ($q){
                      $q->where('receipt_date','>=',$this->repDate1); })
                    ->when($this->repDate2,function ($q){
                      $q->where('receipt_date','<=',$this->repDate2); })

                  ->leftjoin('accs','acc_id','accs.id')
                  ->leftjoin('kazenas','kazena_id','kazenas.id')
                  ->where('imp_exp',0)
                  ->selectRaw('rec_who,pay_type,accs.name accName,kazenas.name kazName,0 as exp,sum(receipts.val) as val')
                  ->groupby('rec_who','pay_type','accs.name','kazenas.name');

                $rec=Receipt::
                    when($this->repDate1,function ($q){
                      $q->where('receipt_date','>=',$this->repDate1); })
                    ->when($this->repDate2,function ($q){
                        $q->where('receipt_date','<=',$this->repDate2); })

                  ->leftjoin('accs','acc_id','accs.id')
                  ->leftjoin('kazenas','kazena_id','kazenas.id')
                  ->where('imp_exp',1)
                  ->selectRaw('rec_who,pay_type,accs.name accName,kazenas.name kazName,sum(receipts.val) as exp,0 as val')
                  ->groupby('rec_who','pay_type','accs.name','kazenas.name')
                    ->union($first);

                return $rec;
            }

            )
          ->emptyStateHeading('لا توجد بيانات')
            ->heading(new HtmlString('<div class="text-primary-400 text-lg">الزبائن</div>'))
            ->contentFooter(view('table.footer', $this->data_list))
          ->defaultPaginationPageOption(5)
            ->defaultSort('val')
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('rec_who')
                    ->label('البيان'),
                TextColumn::make('pay_type')
                    ->label('طريقة الدفع'),
                TextColumn::make('accName')
                  ->state(function (Model $record) {
                      if ($record->accName) return $record->accName; else return $record->kazName;
                  })
                  ->label('بواسطة'),

                TextColumn::make('val')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->state(function (Receipt $record): string {
                        if ($record->val==0)
                        return ''; else return $record->val;
                    })
                    ->label('قبض'),
                TextColumn::make('exp')
                    ->numeric(decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',')
                    ->state(function (Receipt $record): string {
                        if ($record->exp==0)
                            return ''; else return $record->exp;
                    })
                    ->label('دفع'),
            ]);
    }
}
