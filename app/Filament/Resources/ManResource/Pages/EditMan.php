<?php

namespace App\Filament\Resources\ManResource\Pages;

use App\Filament\Resources\ManResource;
use App\Models\Hand;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMan extends EditRecord
{
    protected static string $resource = ManResource::class;

    protected function beforeSave(): void
    {
        $res=Man::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
    }
}
