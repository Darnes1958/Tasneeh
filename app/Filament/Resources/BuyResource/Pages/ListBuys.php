<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Filament\Resources\BuyResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Buy;
use App\Models\Hand;
use App\Models\Item;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuys extends ListRecords
{
    use AccTrait;
    protected static string $resource = BuyResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('ادخال فاتورة جديدة'),

        ];
    }

}
