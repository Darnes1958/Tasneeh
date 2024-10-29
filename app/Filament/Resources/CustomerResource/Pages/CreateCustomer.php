<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\CustomerResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use AccTrait;
    protected static string $resource = CustomerResource::class;
    protected function afterCreate(): void
    {
        $hall=Customer::find(Customer::max('id'));

        $this->AddAcc(AccRef::customers,$hall);
    }
}
