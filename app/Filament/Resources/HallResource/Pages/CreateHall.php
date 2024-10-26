<?php

namespace App\Filament\Resources\HallResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\HallResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use App\Models\Account;
use App\Models\Hall;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateHall extends CreateRecord
{
    use AccTrait;
    protected static string $resource = HallResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function afterCreate(): void
    {
        $hall=Hall::find(Hall::max('id'));

        $this->AddAcc(AccRef::halls,$hall);
    }
}
