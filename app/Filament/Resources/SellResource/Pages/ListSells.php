<?php

namespace App\Filament\Resources\SellResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\SellResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListSells extends ListRecords
{
    protected static string $resource = SellResource::class;
    protected ?string $heading='مبيعات';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ادخال'),
        ];
    }
}
