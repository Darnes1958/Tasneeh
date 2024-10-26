<?php

namespace App\Filament\Resources\HallResource\Pages;

use App\Filament\Resources\HallResource;
use App\Models\Hall;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHall extends EditRecord
{
    protected static string $resource = HallResource::class;
    protected ?string $heading='';
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function beforeSave(): void
    {
        $res=Hall::find($this->data['id']);
        if ($res->account)
        $res->account->update(['name'=>$this->data['name']]);
    }
}
