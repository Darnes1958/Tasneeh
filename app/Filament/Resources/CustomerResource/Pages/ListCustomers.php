<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة زبون جديد'),
        ];
    }
}
