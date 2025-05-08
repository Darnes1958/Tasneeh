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


}
