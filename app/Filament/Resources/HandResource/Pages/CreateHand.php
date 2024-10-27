<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\HandResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use App\Models\Hand;
use App\Models\Kazena;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHand extends CreateRecord
{
   use AccTrait;
    protected static string $resource = HandResource::class;

    protected function afterCreate(): void
    {

        self::inputKyde(Hand::find(Hand::max('id')));
    }

}
