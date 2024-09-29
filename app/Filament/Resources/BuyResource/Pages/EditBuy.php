<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Filament\Resources\BuyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuy extends EditRecord
{
    protected static string $resource = BuyResource::class;
    protected ?string $heading='تعديل فاتورة شراء';
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
