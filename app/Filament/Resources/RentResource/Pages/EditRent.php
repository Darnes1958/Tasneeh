<?php

namespace App\Filament\Resources\RentResource\Pages;

use App\Filament\Resources\RentResource;
use App\Models\Masr_type;
use App\Models\Rent;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRent extends EditRecord
{
    protected static string $resource = RentResource::class;
    protected ?string $heading='';

}
