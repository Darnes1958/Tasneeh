<?php

namespace App\Filament\Resources\KydeResource\Pages;

use App\Filament\Resources\KydeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKydes extends ListRecords
{
    protected static string $resource = KydeResource::class;
    protected ?string $heading=' ';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ادخال قيد'),
        ];
    }
}
