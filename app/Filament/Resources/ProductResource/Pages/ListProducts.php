<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'visible' => Tab::make('بها رصيد')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock','>', 0)),
            'hidden' => Tab::make('رصيدها صفر')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock', 0)),
            'all' => Tab::make('الجميع') ,
        ];
    }
}
