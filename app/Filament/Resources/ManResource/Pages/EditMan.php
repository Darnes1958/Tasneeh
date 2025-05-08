<?php

namespace App\Filament\Resources\ManResource\Pages;

use App\Filament\Resources\ManResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hand;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMan extends EditRecord
{
    use AccTrait;
    protected static string $resource = ManResource::class;




}
