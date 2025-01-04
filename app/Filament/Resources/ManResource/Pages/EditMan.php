<?php

namespace App\Filament\Resources\ManResource\Pages;

use App\Filament\Resources\ManResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hand;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMan extends EditRecord
{
    use AccTrait;
    protected static string $resource = ManResource::class;

    protected function beforeSave(): void
    {
        $res=Man::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);

    }
    protected function afterSave(): void{
        $res=Man::find($this->data['id']);
        if ($res->kyde) foreach ($res->kyde as $rec) $rec->delete();
        if ($this->data['balance']!=0)
            self::inputKyde($res);
    }

}
