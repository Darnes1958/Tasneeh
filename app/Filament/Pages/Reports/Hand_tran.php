<?php

namespace App\Filament\Pages\Reports;

use App\Livewire\Traits\PublicTrait;


use App\Models\Hand;
use App\Models\Man;
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
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports.hand_tran';
    protected static ?string $navigationLabel='حركة مشغل';
    protected static ?string $navigationGroup='زبائن وموردين ومشغلين';
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
                ->schema($this->getMyFormSchema())
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
                        if ($record->pay_who->value==1 || $record->pay_who->value==2){
                            return $record->val;
                        } else return 0;
                    })
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->using(fn (Builder $query): string => $query->whereIn('pay_who',[1,2])->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('مدين'),

                TextColumn::make('daen')
                    ->state(function ($record){
                        if ($record->pay_who->value==0 || $record->pay_who->value==3){
                            return $record->val;
                        } else return 0;
                    })
                    ->summarize(Summarizer::make()
                        ->label('')
                        ->using(fn (Builder $query): string => $query->whereIn('pay_who',[0,3])->sum('val')))
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
                    Select::make('man_id')
                        ->options(Man::all()->pluck('name','id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state,Set $set){
                            $this->man_id=$state;
                            if ($this->repDate) {
                                $mden=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                    ->whereIn('pay_who',[1,2])->sum('val');
                                $daen=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                    ->whereIn('pay_who',[0,3])->sum('val');
                                $set('mden',number_format($mden, 2, '.', ','));
                                $set('daen',number_format($daen, 2, '.', ','));
                                $set('raseed',number_format($mden-$daen, 2, '.', ','));
                            }
                        })
                        ->label('المشغل'),
                    DatePicker::make('repDate')
                        ->live()
                        ->afterStateUpdated(function ($state,Set $set){
                            $this->repDate=$state;
                            if ($this->repDate && $this->man_id) {
                                $mden=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                    ->whereIn('pay_who',[1,2])->sum('val');
                                $daen=Hand::where('man_id',$this->man_id)->where('val_date','>=',$this->repDate)
                                    ->whereIn('pay_who',[0,3])->sum('val');
                                $set('mden',number_format($mden, 2, '.', ','));
                                $set('daen',number_format($daen, 2, '.', ','));
                                $set('raseed',number_format($mden-$daen, 2, '.', ','));
                            }
                        })
                        ->label('من تاريخ'),

                    TextInput::make('mden')
                        ->readOnly()
                        ->label('مدين'),
                    TextInput::make('daen')
                        ->readOnly()
                        ->label('دائن'),
                    TextInput::make('raseed')
                        ->readOnly()
                        ->label('الرصيد'),
                    \Filament\Forms\Components\Actions::make([
                        \Filament\Forms\Components\Actions\Action::make('printorder')
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
                                        'daen'=>$get('daen')]), 'filename.pdf', self::ret_spatie_header());

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
