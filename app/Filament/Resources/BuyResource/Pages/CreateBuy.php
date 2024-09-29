<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Filament\Resources\BuyResource;

use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateBuy extends CreateRecord
{
    protected static string $resource = BuyResource::class;
    protected ?string $heading='';
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()

            ->extraAttributes(['type' => 'button', 'wire:click' => 'create']);
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static bool $canCreateAnother = false;
}
