<?php

namespace App\Filament\Resources\FactoryResource\Pages;

use App\Filament\Resources\FactoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactories extends ListRecords
{
    protected static string $resource = FactoryResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('ادخال تصنيع'),
        ];
    }
}
