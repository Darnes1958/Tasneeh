<?php

namespace App\Filament\Pages\Reports;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use App\Livewire\Traits\PublicTrait;


use App\Models\Hand;
use App\Models\Man;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

class Hand_tran extends Page implements HasForms,HasTable
{
    use InteractsWithTable,InteractsWithForms;
    use PublicTrait;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.reports.hand_tran';
    protected static ?string $navigationLabel='حركة مشغل';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين ومشغلين';
    protected static ?int $navigationSort=7;
    protected ?string $heading="";

    public $man_id;
    public $repDate;
    public $formData;
    public function mount(){
        $this->repDate=now();

        $this->myForm->fill(['repDate'=>$this->repDate]);
    }

    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "myForm" => $this->makeForm()
                ->components($this->getMyFormSchema())
                ->statePath('formData'),

        ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                $report=Hand::
                where('man_id',$this->man_id)
                    ->where('val_date','>=',$this->repDate);
                return $report;
            })
            ->deferLoading()


            ->columns([
                TextColumn::make('val_date')
                    ->sortable()
                    ->searchable()
                    ->label('التاريخ'),
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('الرقم الألي'),

                TextColumn::make('pay_who')
                    ->sortable()
                    ->searchable()
                    ->description(function ($record){

                        if ($record->Factory)
                            return $record->Factory->Product->name; else return '';
                    })
                    ->label('البيان'),
                TextColumn::make('pay_type')
                    ->sortable()
                    ->searchable()
                    ->label('طريقة الدفع'),

                TextColumn::make('mden')
                    ->state(function ($record){
                        if ($record->pay_who->value!=0){
                            return $record->val;
                        } else return 0;
                    })
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->using(fn (Builder $query): string => $query->where('pay_who','!=',0)->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('مدين'),

                TextColumn::make('daen')
                    ->state(function ($record){
                        if ($record->pay_who->value==0){
                            return $record->val;
                        } else return 0;
                    })
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->using(fn (Builder $query): string => $query->where('pay_who',0)->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('دائن'),

                TextColumn::make('notes')
                    ->label('ملاحظات')
            ])
            ->emptyStateHeading('لا توجد بيانات')

            ->striped();
    }

    protected function getMyFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('man_id')
                                ->options(Man::where('visible',1)->pluck('name','id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state,Set $set){
                                    $this->man_id=$state;
                                    if ($this->repDate) {
                                        $mden=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                            ->where('pay_who','!=',0)->sum('val');
                                        $daen=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                            ->where('pay_who',0)->sum('val');
                                        $balance=Man::find($this->man_id)->balance;
                                        $last=Hand::where('man_id',$this->man_id)->where('val_date','<',$this->repDate)
                                                ->where('pay_who','=',0)->sum('val')
                                            -
                                            Hand::where('man_id',$this->man_id)->where('val_date','<',$this->repDate)
                                                ->where('pay_who','!=',0)->sum('val');
                                        $set('balance',number_format($balance, 2, '.', ','));
                                        $set('last',number_format($last, 2, '.', ','));
                                        $raseed=($daen+$balance+$last)-($mden);
                                        $set('raseed',number_format($raseed, 2, '.', ','));

                                        $set('mden',number_format($mden, 2, '.', ','));
                                        $set('daen',number_format($daen, 2, '.', ','));

                                    }
                                })
                                ->label('المشغل'),
                            DatePicker::make('repDate')
                                ->live()
                                ->afterStateUpdated(function ($state,Set $set){
                                    $this->repDate=$state;
                                    if ($this->repDate && $this->man_id) {
                                        $mden=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                            ->where('pay_who','!=',0)->sum('val');
                                        $daen=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                            ->where('pay_who',0)->sum('val');
                                        $balance=Man::find($this->man_id)->balance;
                                        $last=Hand::where('man_id',$this->man_id)->where('val_date','<',$this->repDate)
                                                ->where('pay_who','=',0)->sum('val')
                                            -
                                            Hand::where('man_id',$this->man_id)->where('val_date','<',$this->repDate)
                                                ->where('pay_who','!=',0)->sum('val');
                                        $set('balance',number_format($balance, 2, '.', ','));
                                        $set('last',number_format($last, 2, '.', ','));
                                        $raseed=($daen+$balance+$last)-($mden);
                                        $set('raseed',number_format($raseed, 2, '.', ','));
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
                                return $this->chkDate($this->repDate) && $this->man_id;
                            })
                            ->button()
                            ->icon('heroicon-m-printer')
                            ->color('info')
                            ->action(function (Get $get){
                                $res=$this->getTableQueryForExport()->get();
                                if ($res->count()==0) return ;
                                return Response::download(self::ret_spatie_land($res,
                                    'PrnView.pdf-man-tran',
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
