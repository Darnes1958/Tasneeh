<?php

namespace App\Filament\Resources\AccResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\AccResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAcc extends CreateRecord
{
    use AccTrait;
    protected static string $resource = AccResource::class;
    protected ?string $heading='';
    protected static bool $canCreateAnother = false;
    public function getMaxContentWidth(): ?string
    {
        return '3xl';
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
    protected function afterCreate(): void
    {
        $acc=Acc::find(Acc::max('id'));

        $this->AddAcc(AccRef::msarf,$acc);
        if ($acc->balance!=0)
            self::inputKyde($acc);
    }
}
