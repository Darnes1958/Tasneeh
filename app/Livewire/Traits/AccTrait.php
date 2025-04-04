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
use App\Models\Masr_type;
use App\Models\Place;
use App\Models\Rent;
use App\Models\Renttran;
use App\Models\Salary;
use App\Models\Salarytran;

use App\Models\Supplier;
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
            case 'suppliers': {
                $arr['kyde_date']=$model->created_at;
                if ($model->balance>0){
                    $arr['mden']=AccRef::rasmal;
                    $arr['daen']=$model->account->id;
                } else
                {
                    $arr['daen']=AccRef::rasmal;
                    $arr['mden']=$model->account->id;
                }

                $arr['data']='رصيد بداية المدة';
                $arr['val']=abs($model->balance);
                break;
            }
            case 'kazenas': {
                $arr['kyde_date']=$model->created_at;
                if ($model->balance>0){
                    $arr['mden']=AccRef::rasmal;
                    $arr['daen']=$model->account->id;
                } else
                {
                    $arr['daen']=AccRef::rasmal;
                    $arr['mden']=$model->account->id;
                }

                $arr['data']='رصيد بداية المدة';
                $arr['val']=abs($model->balance);
                break;
            }
            case 'accs': {
                $arr['kyde_date']=$model->created_at;
                if ($model->balance>0){
                    $arr['mden']=AccRef::rasmal;
                    $arr['daen']=$model->account->id;
                } else
                {
                    $arr['daen']=AccRef::rasmal;
                    $arr['mden']=$model->account->id;
                }

                $arr['data']='رصيد بداية المدة';
                $arr['val']=abs($model->balance);
                break;
            }
            case 'customers': {
                $arr['kyde_date']=$model->created_at;
                if ($model->balance>0){
                    $arr['mden']=AccRef::rasmal;
                    $arr['daen']=$model->account->id;
                } else
                {
                    $arr['daen']=AccRef::rasmal;
                    $arr['mden']=$model->account->id;
                }

                $arr['data']='رصيد بداية المدة';
                $arr['val']=abs($model->balance);
                break;
            }
            case 'men': {
                $arr['kyde_date']=$model->created_at;
                if ($model->balance>0){
                    $arr['mden']=AccRef::rasmal;
                    $arr['daen']=$model->account->id;
                } else
                {
                    $arr['daen']=AccRef::rasmal;
                    $arr['mden']=$model->account->id;
                }

                $arr['data']='رصيد بداية المدة';

                $arr['val']=abs($model->balance);

                break;
            }
            case 'buys': {
                if ($spacial=='buyCosts'){
                    $arr['kyde_date']=$model->order_date;
                    $arr['val']=$model->cost;
                    $arr['mden']=AccRef::costs->value;
                    $arr['daen']=AccRef::costDaen->value;
                    $arr['data']='تكاليف اضافية علي المشتريات - '.$model->costs[0]->Costtype->name;
                }
                if ($spacial=='order'){
                    $arr['kyde_date']=$model->order_date;
                    $arr['val']=$model->total;
                    $arr['mden']=AccRef::buys->value;
                    $arr['daen']=Supplier::find($model->supplier_id)->account->id;
                    $arr['data']='فاتورة مشتريات';
                }
                if ($spacial=='store'){
                    $arr['kyde_date']=$model->order_date;
                    $arr['val']=$model->total;
                    $arr['mden']=Place::find($model->place_id)->account->id;
                    $arr['daen']=AccRef::buys->value;
                    $arr['data']='فاتورة مشتريات';
                }

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
            case 'sells': {
                $hall=Hall::find($model->hall_id)->account->id;
                $arr['kyde_date']=$model->order_date;
                $arr['val']=$model->total;
                $arr['mden']=AccRef::sells->value;
                $arr['daen']=$hall;
                $arr['data']='فاتورة مبيعات';
                break;
            }
            case 'masrofats': {
                $masr_type=Masr_type::find($model->masr_type_id)->account->id;
                if ($model->kazena_id) $nakd=kazena::find($model->kazena_id)->account->id;
                else $nakd=Acc::find($model->acc_id)->account->id;
                $arr['kyde_date']=$model->masr_date;
                $arr['val']=$model->val;
                $arr['mden']=$masr_type;
                $arr['daen']=$nakd;
                $arr['data']='مصروفات ادارية وعمومية';
                break;
            }
            case 'salarytrans': {

                $arr['kyde_date']=$model->tran_date;
                $arr['val']=$model->val;
                if ($model->tran_type=='مرتب' || $model->tran_type=='اضافة'){
                    $arr['mden']=AccRef::salaries_mden;
                    $arr['daen']=Salary::find($model->salary_id)->account->id;
                    $arr['data']='ادراج واضافة للمرتبات';
                }
                if ($model->tran_type=='خصم' ) {
                    $arr['mden']=Salary::find($model->salary_id)->account->id;
                    $arr['daen']=AccRef::salaries_mden;
                    $arr['data']='خصم من مرتب';
                }
                if ($model->tran_type=='سحب'){
                    if ($model->kazena_id) $nakd=kazena::find($model->kazena_id)->account->id;
                    else $nakd=Acc::find($model->acc_id)->account->id;
                    $arr['mden']=Salary::find($model->salary_id)->account->id;
                    $arr['daen']=$nakd;
                    $arr['data']='سحب من مرتب';
                }
                break;
            }
            case 'renttrans': {

                $arr['kyde_date']=$model->tran_date;
                $arr['val']=$model->val;
                if ($model->tran_type=='إيجار' ){
                    $arr['mden']=AccRef::rents_mden;
                    $arr['daen']=Rent::find($model->rent_id)->account->id;
                    $arr['data']='ادراج ايجارات';
                }

                if ($model->tran_type=='سحب'){
                    if ($model->kazena_id) $nakd=kazena::find($model->kazena_id)->account->id;
                    else $nakd=Acc::find($model->acc_id)->account->id;
                    $arr['mden']=Rent::find($model->rent_id)->account->id;
                    $arr['daen']=$nakd;
                    $arr['data']='سحب من ايجار';
                }
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
