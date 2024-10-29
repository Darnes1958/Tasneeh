<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected ?string $heading="";
    protected function beforeSave(): void
    {
        $res=Customer::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
    }
}
