<?php

namespace App\Filament\Pages\Reports;


use Filament\Schemas\Schema;
use App\Livewire\widgets\RepBuy;
use App\Livewire\widgets\RepMasr;
use App\Livewire\widgets\RepReceipt;
use App\Livewire\widgets\RepResSupp;
use App\Livewire\widgets\RepSell;
use App\Models\Recsupp;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RepDaily extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'الحركة اليومية';
    protected static string | \UnitEnum | null $navigationGroup = 'الحركة اليومية';
    protected static ?int $navigationSort=1;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected string $view = 'filament.pages.reports.rep-daily';
    protected ?string $heading="";

    public $repDate1;
    public $repDate2;
    public function mount(){
        $this->repDate1=now();
        $this->repDate2=now();
        $this->form->fill(['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2]);
    }
    public static function getWidgets(): array
    {
        return [
            RepBuy::class,
            RepSell::class,
            Recsupp::class,
            RepReceipt::class,
            RepMasr::class,
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            RepBuy::make([
                'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            RepSell::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            RepResSupp::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            RepReceipt::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),

          RepMasr::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
          ]),


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
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('repDate1')
                    ->live()
                    ->afterStateUpdated(function ($state){
                        $this->repDate1=$state;
                        $this->dispatch('updateDate1', repdate: $state);
                    })
                    ->columnSpan(2)
                    ->label('من تاريخ'),
                DatePicker::make('repDate2')
                  ->live()
                  ->afterStateUpdated(function ($state){
                    $this->repDate2=$state;
                    $this->dispatch('updateDate2', repdate: $state);
                  })
                  ->columnSpan(2)
                  ->label('إلي تاريخ')

            ])->columns(6);
    }
    public function printAction(): Action
    {

        return Action::make('print')
            ->visible(function (){
                return $this->chkDate($this->repDate1) || $this->chkDate($this->repDate2);
            })
            ->label('طباعة')
            ->button()
            ->color('danger')
            ->icon('heroicon-m-printer')
            ->color('info')
           ->url(fn (): string => route('pdfdaily', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,]));
    }
}
