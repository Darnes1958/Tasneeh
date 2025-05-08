<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\KazenaResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Kazena;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKazena extends CreateRecord
{
    use AccTrait;
    protected static string $resource = KazenaResource::class;

}
