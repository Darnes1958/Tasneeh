<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Filament\Resources\HandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHand extends EditRecord
{
    protected static string $resource = HandResource::class;
    protected ?string $heading='تعديل بيانات دفع لمشغل';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
