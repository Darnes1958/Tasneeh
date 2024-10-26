<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\HandResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use App\Models\Hand;
use App\Models\Kazena;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHand extends CreateRecord
{
   use AccTrait;
    protected static string $resource = HandResource::class;

    protected function afterCreate(): void
    {
        $hand=Hand::find(Hand::max('id'));
        $man=Man::find($hand->man_id);
        if ($hand->kazena_id) $nakd=kazena::find($hand->kazena_id);
        else $nakd=Acc::find($hand->acc_id);
        if ($hand->pay_who->value==0 || $hand->pay_who->value==3)
         $this->AddKyde($nakd->account->id,$man->account->id,$hand,$hand->val,$hand->val_date,'من مشغلين الي النقدية');
        else
         $this->AddKyde($man->account->id,$nakd->account->id,$hand,$hand->val,$hand->val_date,'من مشغلين الي النقدية');
    }

}
