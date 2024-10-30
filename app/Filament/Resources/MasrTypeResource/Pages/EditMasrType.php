<?php

namespace App\Filament\Resources\MasrTypeResource\Pages;

use App\Filament\Resources\MasrTypeResource;
use App\Models\Masr_type;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasrType extends EditRecord
{
    protected static string $resource = MasrTypeResource::class;

    protected function beforeSave(): void
    {
        $res=Masr_type::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
    }
}
