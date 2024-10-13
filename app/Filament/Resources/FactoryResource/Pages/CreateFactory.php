<?php

namespace App\Filament\Resources\FactoryResource\Pages;

use App\Filament\Resources\FactoryResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateFactory extends CreateRecord
{
    protected static string $resource = FactoryResource::class;
    protected ?string $heading='';
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()

            ->extraAttributes(['type' => 'button', 'wire:click' => 'create'])
            ;
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
