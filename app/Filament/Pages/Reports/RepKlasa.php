<?php

namespace App\Filament\Pages\Reports;



use App\Livewire\Traits\PublicTrait;
use App\Livewire\widgets\KlasaBuy;
use App\Livewire\widgets\KlasaCust;
use App\Livewire\widgets\klasakzaen;
use App\Livewire\widgets\KlasaMasr;
use App\Livewire\widgets\KlasaSell;
use App\Livewire\widgets\KlasaSupp;

use App\Livewire\widgets\StatsKlasa;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Attributes\On;

class RepKlasa extends Page implements HasForms,HasActions
{
    use InteractsWithForms,InteractsWithActions;
    use PublicTrait;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'خلاصة الحركة اليومية';
    protected static ?string $navigationGroup = 'الحركة اليومية';
    protected static ?int $navigationSort=2;

    public function chkDate($repDate){
        try {
            Carbon::parse($repDate);
            return true;
        } catch (InvalidFormatException $e) {
            return false;
        }
    }
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    protected static string $view = 'filament.pages.reports.rep-klasa';
    protected ?string $heading="";
    public $buy,$sell,$masr,$kaz,$cust,$supp;
    #[On('buyQuery')]
    public function buyQuery($buy){
        $this->buy=$buy;
    }
    #[On('custQuery')]
    public function custQuery($par){$this->cust=$par;}
    #[On('suppQuery')]
    public function suppQuery($par){$this->supp=$par;}
    #[On('sellQuery')]
    public function sellQuery($par){$this->sell=$par;}
    #[On('masrQuery')]
    public function masrQuery($par){$this->masr=$par;}
    #[On('kazQuery')]
    public function kazQuery($par){$this->kaz=$par;}

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
            KlasaBuy::class,
            KlasaSell::class,
            KlasaSupp::class,
            KlasaCust::class,
            KlasaMasr::class,
            StatsKlasa::class,
            klasakzaen::class,
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [

          StatsKlasa::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
          ]),

            KlasaBuy::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
              ]),
            KlasaSell::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            KlasaSupp::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
            KlasaCust::make([
              'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
            ]),
          KlasaMasr::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
          ]),
          klasakzaen::make([
            'repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,
          ]),

        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('repDate1')
                 ->live()
                 ->afterStateUpdated(function ($state){
                   if ($this->chkDate($state))  $this->repDate1=$state;
                     $this->dispatch('updateDate1', repdate: $state);
                 })

                ->prefix('من تاريخ')
                ->hiddenLabel(),
                DatePicker::make('repDate2')
                  ->live()
                  ->afterStateUpdated(function ($state){
                    if ($this->chkDate($state)) $this->repDate2=$state;
                    $this->dispatch('updateDate2', repdate: $state);
                  })
                  ->prefix('حتي تاريخ')
                  ->hiddenLabel()

            ])->columns(2);
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
            ->action(function (){
                return Response::download(self::ret_spatie($this->buy,'PrnView.pdf-klasa',
                    ['RepDate1'=>$this->repDate1,'RepDate2'=>$this->repDate2,'buy'=>$this->buy,'sell'=>$this->sell
                        ,'masr'=>$this->masr,'kaz'=>$this->kaz,'cust'=>$this->cust,'supp'=>$this->supp]));

            });
        //    ->url(fn (): string => route('pdfklasa', ['repDate1'=>$this->repDate1,'repDate2'=>$this->repDate2,]));
    }
}
