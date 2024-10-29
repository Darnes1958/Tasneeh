<?php

namespace App\Filament\Resources\CustomerResource\Pages;

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
            Actions\CreateAction::make()->label('إضافة زبون جديد'),
        ];
    }
}
