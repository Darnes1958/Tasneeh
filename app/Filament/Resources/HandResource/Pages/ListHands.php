<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Filament\Resources\HandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Closure;

class ListHands extends ListRecords
{
    protected static string $resource = HandResource::class;
    protected ?string $heading='دفع وخصم مبالغ من مشغل';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('ادخال'),
        ];
    }

}
