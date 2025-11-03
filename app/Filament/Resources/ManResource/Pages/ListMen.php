<?php

namespace App\Filament\Resources\ManResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Enums\AccRef;
use App\Filament\Resources\ManResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hand;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListMen extends ListRecords
{
    use AccTrait;
    protected static string $resource = ManResource::class;

    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('اضافة مشغل'),

        ];
    }

    public function getTabs(): array
    {
        return [
            'visible' => Tab::make('مرئي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('visible', 1)),
            'hidden' => Tab::make('مخفي')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('visible', 0)),
            'all' => Tab::make('الجميع') ,
        ];
    }
}
