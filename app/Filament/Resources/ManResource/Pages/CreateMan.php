<?php

namespace App\Filament\Resources\ManResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\ManResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hand;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMan extends CreateRecord
{
    use AccTrait;
    protected static string $resource = ManResource::class;
    protected ?string $heading='';
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

}
