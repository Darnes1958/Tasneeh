<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\Traits\PublicTrait;
use App\Models\Buy;
use App\Models\Cust_tran2;
use App\Models\Sell;
use App\Models\Supp_tran;
use App\Models\Customer;
use App\Models\Supp_tran2;
use App\Models\Supplier;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class SuppTran extends Page implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    use PublicTrait;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel='حركة مورد';
    protected static ?string $navigationGroup='زبائن وموردين';
  protected static ?int $navigationSort=7;
    protected ?string $heading="";
    protected static string $view = 'filament.pages.reports.supp-tran';

  public static function shouldRegisterNavigation(): bool
  {
    return Auth::user()->hasRole('admin');
  }


    public $cust_id;
    public $repDate;
    public $formData;

    public function mount(){
        $this->repDate=now();

        $this->myForm->fill(['repDate'=>$this->repDate,'raseed'=>0,'mden'=>0,'daen'=>0]);
    }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "myForm" => $this->makeForm()
                ->schema($this->getMyFormSchema())
                ->statePath('formData'),

        ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->idd;
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Supp_tran2::
                where('supplier_id',$this->cust_id)
                    ->where('repDate','>=',$this->repDate);
                return $report;
            })
            ->emptyStateHeading('لا توجد بيانات')
            ->actions([
                \Filament\Tables\Actions\Action::make('عرض')
                    ->visible(function (Model $record) {return $record->rec_who->value==8;})
                    ->modalHeading(false)
                    ->action(fn (Supp_tran2 $record) =>  $record->idd)
                    ->modalSubmitActionLabel('طباعة')
                    ->modalSubmitAction(
                        fn (\Filament\Actions\StaticAction $action,Supp_tran2 $record) =>
                        $action->color('blue')
                            ->icon('heroicon-o-printer')
                            ->action(function () use ($record) {
                                return Response::download(self::ret_spatie(Buy::find($record->id),
                                    'PrnView.pdf-buy-order',
                                    ), 'filename.pdf', self::ret_spatie_header());

                            })

                    )
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
                    ->modalContent(fn (Supp_tran2 $record): View => view(

                        'view-buy-tran-widget',
                        ['buy_id' => $record->id]  ,
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
            ->defaultSort('created_at')
            ->striped();
    }

    protected function getMyFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Select::make('cust_id')
                        ->prefixIcon('heroicon-m-user-circle')
                        ->prefixIconColor('Fuchsia')
                        ->columnSpan(2)
                        ->options(Supplier::all()->pluck('name','id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state,Set $set){
                            $this->cust_id=$state;
                            if ($this->repDate) {
                                $mden=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                                $daen=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                                $set('mden',number_format($mden, 2, '.', ','));
                                $set('daen',number_format($daen, 2, '.', ','));
                                $set('raseed',number_format($mden-$daen, 2, '.', ','));


                            }
                        })
                        ->label('المورد'),
                    DatePicker::make('repDate')
                        ->live()
                        ->afterStateUpdated(function ($state,Set $set){
                            $this->repDate=$state;
                            if ($this->repDate && $this->cust_id) {
                                $mden=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('mden');
                                $daen=Supp_tran::where('supplier_id',$this->cust_id)->where('repDate','>=',$this->repDate)->sum('daen');
                                $set('mden',number_format($mden, 2, '.', ','));
                                $set('daen',number_format($daen, 2, '.', ','));
                                $set('raseed',number_format($mden-$daen, 2, '.', ','));


                            }
                        })
                        ->label('من تاريخ'),

                    TextInput::make('mden')
                        ->prefixIcon('heroicon-m-minus')
                        ->prefixIconColor('danger')
                        ->readOnly()
                        ->label('مدين'),
                    TextInput::make('daen')
                        ->prefixIcon('heroicon-m-plus')
                        ->prefixIconColor('info')
                        ->readOnly()
                        ->label('دائن'),
                    TextInput::make('raseed')
                        ->prefixIcon('heroicon-m-bars-2')
                        ->prefixIconColor('success')
                        ->readOnly()
                        ->label('الرصيد'),
                  \Filament\Forms\Components\Actions::make([
                    \Filament\Forms\Components\Actions\Action::make('printorder')
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
                              'PrnView.pdf-supp-tran',
                              ['tran_date'=>$this->repDate,
                                  'raseed'=>$get('raseed'),
                                  'mden'=>$get('mden'),
                                  'daen'=>$get('daen')]), 'filename.pdf', self::ret_spatie_header());

                      })

                  ])->verticalAlignment(VerticalAlignment::End),
                ])
                ->columns(7)
        ];
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
