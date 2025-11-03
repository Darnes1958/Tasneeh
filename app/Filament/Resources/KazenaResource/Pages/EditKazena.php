<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\KazenaResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Item;
use App\Models\Kazena;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditKazena extends EditRecord
{
    use AccTrait;
    protected static string $resource = KazenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible( Auth::user()->can('الغاء مصارف')),
        ];
    }


}
