<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use App\Filament\Resources\KazenaResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Item;
use App\Models\Kazena;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKazena extends EditRecord
{
    use AccTrait;
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
    protected function afterSave(): void{
        $res=Kazena::find($this->data['id']);
        if ($res->kyde) foreach ($res->kyde as $rec) $rec->delete();
        if ($this->data['balance']!=0)
            self::inputKyde($res);
    }
}
