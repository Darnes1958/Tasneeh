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



}
