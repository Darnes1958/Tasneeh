<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\KazenaResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Kazena;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKazena extends CreateRecord
{
    use AccTrait;
    protected static string $resource = KazenaResource::class;
    protected function afterCreate(): void
    {
        $kazena=Kazena::find(Kazena::max('id'));

        $this->AddAcc(AccRef::kazena,$kazena);
        if ($kazena->balance!=0)
            self::inputKyde($kazena);
    }
}
