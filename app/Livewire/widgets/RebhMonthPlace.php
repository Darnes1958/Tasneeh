<?php

namespace App\Livewire\widgets;

use App\Livewire\Traits\AksatTrait;
use App\Models\Place;
use App\Models\Rebh_first_place;
use App\Models\Rebh_second;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class RebhMonthPlace extends BaseWidget
{

  public $year;
  public $place;


  public function mount()
  {
    $this->form->fill(['year'=>$this->year]);
  }
  #[On('updateyearplace')]
  public function updateyearplace($year,$place)
  {
    $this->year=$year;
    $this->place=$place;

  }

    public array $data_list= [
        'calc_columns' => [
            'rebh',
            'rent',
            'masr',
            'sal',
            'safi',
        ],
    ];
  public function getTableRecordKey(Model $record): string
  {
    return uniqid();
  }

  public function table(Table $table): Table
    {
        return $table
            ->query(function (){

                $res=Rebh_first_place::selectRaw('
                wyear,
                wmonth ,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rebh\'),0) rebh,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'masr\'),0) masr,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rent\'),0) rent,
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'sal\'),0) sal,

                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rebh\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'masr\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'rent\'),0) -
                round(dbo.RebhPlace(wyear,wmonth,'.$this->place.',\'sal\'),0) safi
                ')
                    ->Where('wyear',$this->year)
                ->groupBy('wyear','wmonth')
                    ;

              return $res;
            }

            )
            ->emptyStateHeading('لا توجد بيانات')
          ->heading(new HtmlString(
              '<div class="text-primary-400 text-lg">'.'الارباح بالأشهر لسنه '.$this->year.'</div>'

                    ))
          ->contentFooter(view('table.footerNoDecimal', $this->data_list))
          ->defaultSort('wmonth')
            ->columns([
                Tables\Columns\TextColumn::make('wmonth')
                 ->label('الشهر'),
                Tables\Columns\TextColumn::make('rebh')
                    ->numeric(decimalPlaces: 0,
                        decimalSeparator: '',
                        thousandsSeparator: ',')
                 ->label('هامش الربح'),
              Tables\Columns\TextColumn::make('masr')
                  ->numeric(decimalPlaces: 0,
                      decimalSeparator: '',
                      thousandsSeparator: ',')
                ->label('مصروفات'),
              Tables\Columns\TextColumn::make('sal')
                  ->numeric(decimalPlaces: 0,
                      decimalSeparator: '',
                      thousandsSeparator: ',')
                ->label('مرتبات'),
              Tables\Columns\TextColumn::make('rent')
                  ->numeric(decimalPlaces: 0,
                      decimalSeparator: '',
                      thousandsSeparator: ',')
                ->label('ايجارات'),
              Tables\Columns\TextColumn::make('safi')
                  ->numeric(decimalPlaces: 0,
                      decimalSeparator: '',
                      thousandsSeparator: ',')
                    ->label('صافي الأرباح'),


            ]);
    }
}
