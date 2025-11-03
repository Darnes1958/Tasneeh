<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\ProductResource;
use App\Models\Factory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->hidden(fn(Model $record): bool => Factory::where('product_id',$record->id)->exists()),
        ];
    }
}
