<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\SupplierResource;

use App\Livewire\Traits\AccTrait;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    use AccTrait;
  protected ?string $heading="";
    protected static string $resource = SupplierResource::class;

    protected function afterCreate(): void
    {
        $hall=Supplier::find(Supplier::max('id'));

        $this->AddAcc(AccRef::suppliers,$hall);
    }
}
