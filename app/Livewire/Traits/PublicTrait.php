<?php
namespace App\Livewire\Traits;



use App\Enums\AccLevel;
use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;

use Carbon\Carbon;
use DateTime;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait PublicTrait {

    protected function getAcc_levelFromComponent(): Radio
    {
        return  Radio::make('acc_level')
            ->options(AccLevel::class)
            ->inline()
            ->live()
            ->inlineLabel(false)
            ->label('المستوي');
    }
    protected function getKydedataFormComponent($name): TextColumn
    {
        if ($name === 'kyde_id') {
            return TextColumn::make('kyde_id')
                ->label('رقم القيد')
                ->searchable()
                ->sortable();
        }
        if ($name === 'notes') {
            return TextColumn::make('Kyde.notes')
                ->label('بيان القيد')
                ->tooltip('انقر هنا لعرض القيد')
                ->searchable()
                ->sortable();
        }
        if ($name === 'account_id') {
            return TextColumn::make('account_id')
                ->label('رقم الحساب')
                ->searchable()
                ->sortable();
        }
        if ($name === 'full_name') {
            return TextColumn::make('Account.full_name')
                ->label('اسم الحساب')
                ->searchable()
                ->sortable();
        }
        if ($name === 'kyde_date') {
            return TextColumn::make('Kyde.kyde_date')
                ->label('التاريخ')
                ->searchable()
                ->sortable();
        }


    }
    protected function getMdenFormComponent(): TextColumn
    {
        return  TextColumn::make('mden')
            ->state(function (Model $recoed){
                if ($recoed->mden==0) return null;
                else return $recoed->mden;
            })
            ->color('danger')
            ->label('مدين')
            ->summarize(Sum::make()->label('')->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            ))
            ->numeric(
                decimalPlaces: 2,
                decimalSeparator: '.',
                thousandsSeparator: ',',
            );
    }
    protected function getDaenFormComponent(): TextColumn
    {
        return
            TextColumn::make('daen')
                ->state(function (Model $recoed){
                    if ($recoed->daen==0) return null;
                    else return $recoed->daen;
                })
                ->label('دائن')
                ->color('info')
                ->summarize(Sum::make()->label('')->numeric(
                    decimalPlaces: 2,
                    decimalSeparator: '.',
                    thousandsSeparator: ',',
                ))
                ->numeric(
                    decimalPlaces: 2,
                    decimalSeparator: '.',
                    thousandsSeparator: ',',
                );
    }
    protected function getMden2FormComponent(): TextColumn
    {
        return
            TextColumn::make('mden2')
                ->state(function (Model $recoed){
                    if ($recoed->mden2==0) return null;
                    else return $recoed->mden2;
                })
                ->summarize(Summarizer::make()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->using(function (Table $table) {
                        return $table->getRecords()->sum('mden2');
                    })
                )
                ->numeric(
                    decimalPlaces: 2,
                    decimalSeparator: '.',
                    thousandsSeparator: ',',
                )
                ->color('danger')
                ->label('مدين');
    }
    protected function getDaen2FormComponent(): TextColumn
    {
        return
            TextColumn::make('daen2')
                ->state(function (Model $recoed){
                    if ($recoed->daen2==0) return null;
                    else return $recoed->daen2;
                })
                ->summarize(Summarizer::make()
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->using(function (Table $table) {
                        return $table->getRecords()->sum('daen2');
                    })
                )
                ->numeric(
                    decimalPlaces: 2,
                    decimalSeparator: '.',
                    thousandsSeparator: ',',
                )
                ->color('info')
                ->label('دائن');
    }
  public static function ret_spatie_header(){
      return       $headers = [
          'Content-Type' => 'application/pdf',
      ];

  }
  public static function ret_spatie($res,$blade,$arr=[])
  {
      \Spatie\LaravelPdf\Facades\Pdf::view($blade,
          ['res'=>$res,'arr'=>$arr])
          ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
      return public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

  }
    public static function ret_spatie_land($res,$blade,$arr=[])
    {
        \Spatie\LaravelPdf\Facades\Pdf::view($blade,
            ['res'=>$res,'arr'=>$arr])
            ->landscape()
            ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
        return public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

    }

  public function RetMonthName($month){
      switch ($month) {
          case 1:
              return 'يناير';
              break;
          case 1:
              $name= 'فبراير';
              break;
          case 1:
              $name= 'مارس';
              break;
          case 1:
              $name= 'ابريل';
              break;
          case 1:
              $name= 'مايو';
              break;
          case 1:
              $name= 'يونيو';
              break;
          case 1:
              $name= 'يويلو';
              break;
          case 1:
              $name= 'اغسطس';
              break;
          case 1:
              $name= 'سبتمبر';
              break;
          case 1:
              $name= 'اكتوبر';
              break;
          case 1:
              $name= 'نوفمبر';
              break;
          case 1:
              $name= 'ديسمبر';
              break;


      }
      return $name;

  }

  public function TarseedTrans(){
    $res=Salary::all();
    foreach ($res as $item)
      Salary::find($item->id)->update([
        'raseed'=>
          Salarytran::where('salary_id',$item->id)->where('tran_type','مرتب')->sum('val')+
          Salarytran::where('salary_id',$item->id)->where('tran_type','اضافة')->sum('val')-
          Salarytran::where('salary_id',$item->id)->where('tran_type','سحب')->sum('val')-
          Salarytran::where('salary_id',$item->id)->where('tran_type','خصم')->sum('val')
        ]);
  }
    public function TarseedRents(){
        $res=Rent::all();
        foreach ($res as $item)
            Rent::find($item->id)->update([
                'raseed'=>
                    Renttran::where('rent_id',$item->id)->where('tran_type','إيجار')->sum('val')-
                    Renttran::where('rent_id',$item->id)->where('tran_type','سحب')->sum('val'),
            ]);
    }


}
