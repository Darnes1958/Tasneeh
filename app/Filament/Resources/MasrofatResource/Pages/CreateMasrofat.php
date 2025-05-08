<?php

namespace App\Filament\Resources\MasrofatResource\Pages;

use App\Filament\Resources\MasrofatResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Kazena;
use App\Models\Masrofat;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasrofat extends CreateRecord
{
    use AccTrait;
    protected ?string $heading='';
    protected static string $resource = MasrofatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


}
