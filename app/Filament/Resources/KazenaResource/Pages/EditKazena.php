<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use App\Filament\Resources\KazenaResource;
use App\Models\Kazena;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKazena extends EditRecord
{
    protected static string $resource = KazenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible( Auth::user()->can('الغاء مصارف')),
        ];
    }
    protected function beforeSave(): void
    {
        $res=Kazena::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
    }
}
