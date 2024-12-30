<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    use AccTrait;
    protected static string $resource = CustomerResource::class;

    protected ?string $heading="";

    protected function beforeSave(): void
    {
        $res=Customer::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
    }
    protected function afterSave(): void{
        $res=Customer::find($this->data['id']);
        if ($res->kyde) foreach ($res->kyde as $rec) $rec->delete();
        if ($this->data['balance']!=0)
            self::inputKyde($res);
    }
}
