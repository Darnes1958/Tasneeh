<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Filament\Resources\HandResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHand extends CreateRecord
{
    protected static string $resource = HandResource::class;
    protected ?string $heading='';
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
