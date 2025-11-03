<?php

namespace App\Filament\Pages\Reports;


use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\SellResource\Pages\EditSell;
use App\Filament\Resources\SellResource\Pages\SellEdit;
use App\Livewire\Traits\PublicTrait;
use App\Models\Cust_tran;
use App\Models\Cust_tran2;
use App\Models\Customer;
use App\Models\Place_stock;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\Sell_tran;
use App\Models\Setting;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class CustTran extends Page implements HasSchemas,HasTable
{
  use InteractsWithTable,InteractsWithSchemas;
  use PublicTrait;
  protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationLabel='حركة زبون';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين ومشغلين';
  protected static ?int $navigationSort=5;
  protected ?string $heading="";
  protected string $view = 'filament.pages.reports.cust-tran';

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin');
  }


  public $cust_id;
  public $repDate;
  public $formData;
  public $order_no;

  public Sell $sell;

  public function mount(){
    $this->repDate=now();

    $this->myForm->fill(['repDate'=>$this->repDate,'raseed'=>0,'mden'=>0,'daen'=>0]);
  }



  public function getTableRecordKey(Model|array $record): string
  {
    return $record->idd;
  }
  public function table(Table $table): Table
  {
    return $table
      ->query(function (){
        $report=Cust_tran2::
          where('customer_id',$this->cust_id)
          ->where('repDate','>=',$this->repDate);
        return $report;
      })
      ->deferLoading()

      ->recordActions([
        Action::make('عرض')
          ->visible(function (Model $record) {return $record->rec_who->value==7;})
          ->modalHeading(false)
          ->action(fn (Cust_tran2 $record) =>  $record->idd)
            ->modalSubmitActionLabel('طباعة')
            ->modalSubmitAction(
                fn (Action $action,Cust_tran2 $record) =>
                $action->color('blue')
                    ->icon('heroicon-o-printer')
                    ->url(function () use($record){
                        $this->order_no=$record->id;
                        return route('pdfsell', ['id' => $record->id]);
                    })
            )
          ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
          ->modalContent(fn (Cust_tran2 $record): View => view(
              'view-sell-tran-widget',
              ['sell_id' => $record->id]  ,
          ))
          ->icon('heroicon-o-eye')
          ->iconButton(),
        ])
      ->columns([
        TextColumn::make('repDate')
          ->sortable()
          ->searchable()
          ->label('التاريخ'),
        TextColumn::make('id')
          ->sortable()
          ->searchable()

          ->label('الرقم الألي'),

        TextColumn::make('rec_who')
          ->sortable()
          ->searchable()
          ->label('البيان'),
        TextColumn::make('pay_type')
          ->sortable()
          ->searchable()
          ->label('طريقة الدفع'),

        TextColumn::make('mden')
          ->color('danger')
          ->searchable()
          ->numeric(
            decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',',
          )
          ->label('مدين'),
        TextColumn::make('daen')
          ->color('info')
          ->searchable()
          ->numeric(
            decimalPlaces: 2,
            decimalSeparator: '.',
            thousandsSeparator: ',',
          )
          ->label('دائن'),
        TextColumn::make('notes')
         ->label('ملاحظات')
      ])
        ->emptyStateHeading('لا توجد بيانات')
      ->defaultSort('idd')
      ->defaultKeySort(false)
      ->striped();
  }

    protected function myForm(Schema $schema): Schema
  {
    return $schema
        ->statePath('formData')
        ->components([
            Section::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('cust_id')
                                ->options(Customer::all()->pluck('name','id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state,Set $set){
                                    $this->cust_id=$state;
                                    if ($this->repDate) {
                                        $mden=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                                        $daen=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                                        $balance=Customer::find($this->cust_id)->balance;
                                        $last=Cust_tran::where('customer_id',$this->cust_id)->where('repDate','<',$this->repDate)->sum('mden')
                                            -Cust_tran::where('customer_id',$this->cust_id)->where('repDate','<',$this->repDate)->sum('daen');
                                        $set('balance',number_format($balance, 2, '.', ','));
                                        $set('last',number_format($last, 2, '.', ','));
                                        $set('raseed',number_format($mden-$daen-$balance+$last, 2, '.', ','));
                                        $set('mden',number_format($mden, 2, '.', ','));
                                        $set('daen',number_format($daen, 2, '.', ','));



                                    }
                                })
                                ->label('الزبون'),
                            DatePicker::make('repDate')
                                ->live()
                                ->afterStateUpdated(function ($state,Set $set){
                                    $this->repDate=$state;
                                    if ($this->repDate && $this->cust_id) {
                                        $mden=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                                        $daen=Cust_tran2::where('customer_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                                        $balance=Customer::find($this->cust_id)->balance;
                                        $last=Cust_tran::where('customer_id',$this->cust_id)->where('repDate','<',$this->repDate)->sum('mden')
                                            -Cust_tran::where('customer_id',$this->cust_id)->where('repDate','<',$this->repDate)->sum('daen');
                                        $set('balance',number_format($balance, 2, '.', ','));
                                        $set('last',number_format($last, 2, '.', ','));
                                        $set('raseed',number_format($mden-$daen-$balance+$last, 2, '.', ','));
                                        $set('mden',number_format($mden, 2, '.', ','));
                                        $set('daen',number_format($daen, 2, '.', ','));



                                    }
                                })
                                ->label('من تاريخ'),
                        ])->columns(6)->columnSpan('full'),

                    TextInput::make('balance')
                        ->prefixIcon('heroicon-m-minus')
                        ->prefixIconColor('danger')
                        ->readOnly()
                        ->label('بداية المدة'),
                    TextInput::make('last')
                        ->prefixIcon('heroicon-m-plus')
                        ->prefixIconColor('info')
                        ->readOnly()
                        ->label('رصيد سابق'),


                    TextInput::make('mden')
                        ->readOnly()
                        ->label('مدين'),
                    TextInput::make('daen')
                        ->readOnly()
                        ->label('دائن'),
                    TextInput::make('raseed')
                        ->readOnly()
                        ->label('الرصيد'),
                    Actions::make([
                        Action::make('printorder')
                            ->label('طباعة')
                            ->visible(function (){
                                return $this->chkDate($this->repDate) && $this->cust_id;
                            })

                            ->button()

                            ->icon('heroicon-m-printer')
                            ->color('info')
                            ->action(function (Get $get){
                                $res=$this->getTableQueryForExport()->get();
                                if ($res->count()==0) return ;
                                return Response::download(self::ret_spatie($res,
                                    'PrnView.pdf-cust-tran',
                                    ['tran_date'=>$this->repDate,
                                        'raseed'=>$get('raseed'),
                                        'mden'=>$get('mden'),
                                        'daen'=>$get('daen'),
                                        'last'=>$get('last'),
                                        'balance'=>$get('balance'),]), 'filename.pdf', self::ret_spatie_header());

                            }),


                    ])->verticalAlignment(VerticalAlignment::End),
                ])
                ->columns(6)
        ]);


  }
  public function chkDate($repDate){
    try {
      Carbon::parse($repDate);
      return true;
    } catch (InvalidFormatException $e) {
      return false;
    }
  }
}
