<?php

namespace App\Filament\Resources\MasrTypeResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\MasrTypeResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Masr_type;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasrType extends CreateRecord
{
    use AccTrait;
    protected static string $resource = MasrTypeResource::class;


}
