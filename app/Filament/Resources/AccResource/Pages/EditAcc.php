<?php

namespace App\Filament\Resources\AccResource\Pages;

use App\Filament\Resources\AccResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAcc extends EditRecord
{
    protected static string $resource = AccResource::class;

    protected ?string $heading='';
}
