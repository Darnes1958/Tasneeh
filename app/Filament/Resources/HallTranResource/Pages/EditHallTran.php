<?php

namespace App\Filament\Resources\HallTranResource\Pages;

use App\Filament\Resources\HallTranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHallTran extends EditRecord
{
    protected static string $resource = HallTranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
