<?php

namespace App\Livewire\widgets;


use Filament\Support\Enums\TextSize;
use App\Models\Receipt;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepReceipt extends BaseWidget
{

  public $repDate1;
  public $repDate2;
  public $raseed;
  public function mount(){
    $this->repDate1=now();
    $this->repDate2=now();

  }

  #[On('updateDate1')]
  public function updatedate1($repdate)
  {
    $this->repDate1=$repdate;

  }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;

  }
    public array $data_list= [
        'calc_columns' => [
            'val',
        ],
    ];

    public function table(Table $table): Table
    {

        return $table
            ->query(function (Receipt $buy){


              if ($this->repDate1 && !$this->repDate2)
                $buy=Receipt::where('receipt_date','>=',$this->repDate1);
              if ($this->repDate2 && !$this->repDate1)
                $buy=Receipt::where('receipt_date','<=',$this->repDate1);
              if ($this->repDate1 && $this->repDate2)
                $buy=Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2]);

              $this->raseed=Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])
                                         ->where('imp_exp',0)->sum('val') -
                                     Receipt::whereBetween('receipt_date',[$this->repDate1,$this->repDate2])
                                       ->where('imp_exp',1)->sum('val') ;

                return $buy;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">إيصالات الزبائن</div>'))
            ->defaultPaginationPageOption(5)


            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم الألي'),
                TextColumn::make('Customer.name')
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) < 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->size(TextSize::ExtraSmall)
                    ->label('المورد'),
                TextColumn::make('val')
                    ->label('المبلغ'),
                TextColumn::make('Kazena.name')
                    ->label('الخزينة'),
                TextColumn::make('rec_who')
                    ->label('البيان')
                    ->badge(),
                TextColumn::make('notes')
                    ->label('ملاحظات'),

            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(function (){return view('table.Recfooter', $this->data_list,['raseed'=>$this->raseed,]);} )

            ;
    }
}
