<?php

namespace App\Filament\Resources\HallTranResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\HallTranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHallTran extends EditRecord
{
    protected static string $resource = HallTranResource::class;

    protected ?string $heading='';
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

}
