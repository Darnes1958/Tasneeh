<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Place;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSupplier extends EditRecord
{
    use AccTrait;
    protected static string $resource = SupplierResource::class;
  protected ?string $heading="";
    protected function beforeSave(): void
    {
        $res=Supplier::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);

    }
    protected function afterSave(): void{
        $res=Supplier::find($this->data['id']);
        if ($res->kyde) foreach ($res->kyde as $rec) $rec->delete();
        if ($this->data['balance']!=0)
            self::inputKyde($res);
    }
}
