<?php
namespace App\Livewire\Traits;



use App\Enums\AccRef;
use App\Models\Acc;
use App\Models\Account;
use App\Models\Buy;
use App\Models\Hall;
use App\Models\Kazena;
use App\Models\KydeData;
use App\Models\Man;
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

    public static function retAccData($model,$spacial = null)
    {
        $arr=[];
        switch ($model->getTable()) {
            case 'buys': {
                $arr['kyde_date']=$model->order_date;
                $arr['val']=$model->tot;
                break;
            }
            case 'hands': {
                $arr['kyde_date']=$model->val_date;
                if ($model->factory){
                    $arr['mden']=AccRef::mans;
                    $arr['daen']=Man::find($model->man_id)->account->id;
                    $arr['data']='من مشغلين الي تكلفة التصنيع';
                }
                if (!$model->factory){
                    if ($model->kazena_id) $nakd=kazena::find($model->kazena_id)->account->id;
                    else $nakd=Acc::find($model->acc_id)->account->id;
                    if ($model->pay_who->value==0 || $model->pay_who->value==3)
                    {
                        $arr['mden']=$nakd;
                        $arr['daen']=Man::find($model->man_id)->account->id;
                        $arr['data']='من مشغلين الي النقدية';
                    } else{
                        $arr['daen']=$nakd;
                        $arr['mden']=Man::find($model->man_id)->account->id;
                        $arr['data']='من النقدية الي المشغلين';

                    }
                }

                $arr['val']=$model->val;
                break;
            }
            case 'costs': {
                $buy=Buy::find($model->buy_id);
                if ($model->kazena_id) $nakd=kazena::find($model->kazena_id)->account->id;
                else $nakd=Acc::find($model->acc_id)->account->id;
                $arr['kyde_date']=$buy->order_date;
                $arr['val']=$buy->cost;
                $arr['mden']=AccRef::costs->value;
                $arr['daen']=$nakd;
                $arr['data']='تكاليف اضافية علي المشتريات - '.$model->Costtype->name;

                break;
            }
            case 'factories': {
                if ($spacial)
                    $arr['kyde_date']=$model->ready_date;
                else
                    $arr['kyde_date']=$model->process_date;
                $arr['val']=$model->tot;
                if ($spacial)
                    $arr['mden']=Hall::find($model->hall_id)->account->id;
                else
                    $arr['mden']=AccRef::factories;

                if ($spacial)
                    $arr['daen']=AccRef::factories;
                else
                    $arr['daen']=Place::find($model->place_id)->account->id;

                if ($spacial)
                    $arr['data']='من التصنيع إلي مخازن المنتجات';
                else
                    $arr['data']='منتجات تحت التصنيع';
                break;
            }


        }
        return $arr;
    }

    public static function inputKyde($model,$special = null)
    {

        $arr=self::retAccData($model,$special);
        $kyde_id= $model->kyde()->create([
            'kyde_date'=>$arr['kyde_date'],
            'notes'=>$arr['data'],
        ])->id;
        KydeData::create([
            'kyde_id'=>$kyde_id,
            'account_id'=>$arr['mden'],
            'mden'=>$arr['val'],
            'daen'=>0,
        ]);
        KydeData::create([
            'kyde_id'=>$kyde_id,
            'account_id'=>$arr['daen'],
            'mden'=>0,
            'daen'=>$arr['val'],
        ]);

    }
    public static function inputKydewithDelete($model){
        if ($model->kyde)
            foreach ($model->kyde as $rec) $rec->delete();
        self::inputKyde($model);
    }


}
