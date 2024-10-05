<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;
  protected ?string $heading=" ";
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->hidden(fn (Model $record): bool => $record->Buy_tran()->exists()),
        ];
    }
}
