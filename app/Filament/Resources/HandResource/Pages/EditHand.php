<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Filament\Resources\HandResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use App\Models\Hand;
use App\Models\Kazena;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHand extends EditRecord
{
    use AccTrait;
    protected static string $resource = HandResource::class;
    protected ?string $heading='تعديل بيانات دفع لمشغل';


    protected function beforeSave(): void {
        $hand=Hand::find($this->data['id']);
        $man=Man::find($this->data['man_id']);

        if ($hand->kyde)
            foreach ($hand->kyde as $rec) $rec->delete();

        if ($hand->kazena_id) $nakd=Kazena::find($hand->kazena_id);
        else $nakd=Acc::find($hand->acc_id);

        if ($hand->pay_who->value==0 || $hand->pay_who->value==3)
            $this->AddKyde($nakd->account->id,$man->account->id,$hand,$this->data['val'],$this->data['val_date'],'من مشغلين الي النقدية');
        else
            $this->AddKyde($man->account->id,$nakd->account->id,$hand,$this->data['val'],$this->data['val_date'],'من مشغلين الي النقدية');



    }
}
