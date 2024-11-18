<?php
namespace App\Livewire\Traits;



use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;

trait PublicTrait {

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
