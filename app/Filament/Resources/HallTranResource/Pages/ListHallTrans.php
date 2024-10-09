<?php

namespace App\Filament\Resources\HallTranResource\Pages;

use App\Filament\Resources\HallTranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHallTrans extends ListRecords
{
    protected static string $resource = HallTranResource::class;
    protected ?string $heading='نقل منتجات';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->label('ادخال'),
        ];
    }
}
