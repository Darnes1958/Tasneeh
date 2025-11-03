<?php

namespace App\Livewire\widgets;


use Filament\Support\Enums\TextSize;
use Filament\Actions\Action;
use App\Livewire\Traits\PublicTrait;
use App\Models\Buy;
use App\Models\Sell;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RepSell extends BaseWidget
{
use PublicTrait;
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

  }
  #[On('updateDate2')]
  public function updatedate2($repdate)
  {
    $this->repDate2=$repdate;

  }
    public array $data_list= [
        'calc_columns' => [
            'total',
            'pay',
            'baky',
        ],
    ];

    public function table(Table $table): Table
    {

        return $table
            ->query(function (){


              if ($this->repDate1 && !$this->repDate2)
                $sell=Sell::where('order_date','>=',$this->repDate1);
              if ($this->repDate2 && !$this->repDate1)
                $sell=Sell::where('order_date','<=',$this->repDate1);
              if ($this->repDate1 && $this->repDate2)
                $sell=Sell::whereBetween('order_date',[$this->repDate1,$this->repDate2]);


                return $sell;
            }
            // ...
            )
            ->heading(new HtmlString('<div class="text-danger-600 text-lg">فواتير المبيعات</div>'))
            ->defaultPaginationPageOption(5)

            ->defaultSort('order_date','desc')
            ->striped()
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('رقم الفاتورة'),
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
                    ->label('الزبون'),
                TextColumn::make('total')
                    ->label('الإجمالي'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->label('المتبقي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),

            ])
            ->recordActions([
                Action::make('عرض')
                    ->modalHeading(false)
                    ->action(fn (Sell $record) => $record->id())
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
                    ->modalContent(fn (Sell $record): View => view(
                        'view-sell-tran-widget',
                        ['sell_id' => $record->id]  ,
                    ))
                    ->icon('heroicon-o-eye')
                    ->iconButton(),
                Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->color('blue')
                    ->action(function (Sell $record){
                        return Response::download(self::ret_spatie($record,'PrnView.pdf-sell-order' ));
                    })

            ])
            ->emptyStateHeading('لا توجد بيانات')
            ->contentFooter(view('table.footer', $this->data_list))
            ;
    }
}
