<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\HandResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hand;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Closure;
use Illuminate\Support\Facades\Auth;

class ListHands extends ListRecords
{
    use AccTrait;
    protected static string $resource = HandResource::class;
    protected ?string $heading='دفع وخصم مبالغ من مشغل';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('ادخال'),

        ];
    }

}
