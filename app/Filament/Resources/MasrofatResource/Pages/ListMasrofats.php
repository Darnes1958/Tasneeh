<?php

namespace App\Filament\Resources\MasrofatResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\MasrofatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasrofats extends ListRecords
{
    protected static string $resource = MasrofatResource::class;
    protected ?string $heading=' ';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()

            ->label('إضافة'),
        ];
    }
}
