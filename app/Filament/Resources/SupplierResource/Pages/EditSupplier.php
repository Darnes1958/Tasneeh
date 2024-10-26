<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Models\Place;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;
  protected ?string $heading="";
    protected function beforeSave(): void
    {
        $res=Supplier::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
    }
}
