<?php

namespace App\Filament\Resources\HallResource\Pages;

use Filament\Actions\CreateAction;
use App\Enums\AccRef;
use App\Filament\Resources\HallResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hall;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListHalls extends ListRecords
{
    use AccTrait;
    protected static string $resource = HallResource::class;

    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ادخال'),


        ];
    }
}
