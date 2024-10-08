<?php

namespace App\Filament\Resources\HallTranResource\Pages;

use App\Filament\Resources\HallTranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHallTran extends CreateRecord
{
    protected static string $resource = HallTranResource::class;
    protected ?string $heading='نقل منتجات';
}
