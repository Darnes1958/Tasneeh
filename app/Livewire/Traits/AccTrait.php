<?php
namespace App\Livewire\Traits;



use App\Enums\AccRef;
use App\Models\Account;
use App\Models\Hall;
use App\Models\KydeData;
use App\Models\Place;
use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

trait AccTrait {


  public function AddAcc(AccRef $modelName, $modeltable){
      $model=$modeltable;
      $acc=Account::find($modelName->value);
      if ($acc->acc_level->value==1) {
          $grand_id=$acc->id;
          $num=Account::where('father_id',$grand_id)->max('num')+1;
      } else $grand_id=$acc->grand_id;
      if ($acc->acc_level->value==2) {
          $father_id=$acc->id;
          $num=Account::where('father_id',$father_id)->max('num')+1;
      } else $father_id=$acc->father_id;
      if ($acc->acc_level->value==3) {
          $son_id=$acc->id;
          $num=Account::where('son_id',$son_id)->max('num')+1;
      } else $son_id=$acc->son_id;

      $model->account()->create([
          'name'=>$model->name,
          'grand_id'=>$grand_id,
          'father_id'=>$father_id,
          'son_id'=>$son_id,
          'acc_level'=>$acc->acc_level->value+1,
          'num'=>$num,
          'id'=>$acc->id.'-'.strval($num),
          'is_active'=>1,
          'is_fixed'=>1,

      ]);
      $acc->is_active=0;
      $acc->save();
  }
  public static function AddAcc2(AccRef $modelName, $modeltable){
        $model=$modeltable;
        $acc=Account::find($modelName->value);
        if ($acc->acc_level->value==1) {
            $grand_id=$acc->id;
            $num=Account::where('father_id',$grand_id)->max('num')+1;
        } else $grand_id=$acc->grand_id;
        if ($acc->acc_level->value==2) {
            $father_id=$acc->id;
            $num=Account::where('father_id',$father_id)->max('num')+1;
        } else $father_id=$acc->father_id;
        if ($acc->acc_level->value==3) {
            $son_id=$acc->id;
            $num=Account::where('son_id',$son_id)->max('num')+1;
        } else $son_id=$acc->son_id;

        $model->account()->create([
            'name'=>$model->name,
            'grand_id'=>$grand_id,
            'father_id'=>$father_id,
            'son_id'=>$son_id,
            'acc_level'=>$acc->acc_level->value+1,
            'num'=>$num,
            'id'=>$acc->id.'-'.strval($num),
            'is_active'=>1,
            'is_fixed'=>1,

        ]);
        $acc->is_active=0;
        $acc->save();
    }

  public function AddKyde($mdenName,$daenName ,$model,$val,$date,$kyde_notes){

       $kyde_id= $model->kyde()->create([
            'kyde_date'=>$date,
            'notes'=>$kyde_notes,
        ])->id;
        KydeData::create([
          'kyde_id'=>$kyde_id,
          'account_id'=>$mdenName,
          'mden'=>$val,
          'daen'=>0,
        ]);
      KydeData::create([
          'kyde_id'=>$kyde_id,
          'account_id'=>$daenName,
          'mden'=>0,
          'daen'=>$val,
      ]);
    }
    public static function AddKyde2($mdenName,$daenName ,$model,$val,$date,$kyde_notes){

        $kyde_id= $model->kyde()->create([
            'kyde_date'=>$date,
            'notes'=>$kyde_notes,
        ])->id;
        KydeData::create([
            'kyde_id'=>$kyde_id,
            'account_id'=>$mdenName,
            'mden'=>$val,
            'daen'=>0,
        ]);
        KydeData::create([
            'kyde_id'=>$kyde_id,
            'account_id'=>$daenName,
            'mden'=>0,
            'daen'=>$val,
        ]);
    }


}
