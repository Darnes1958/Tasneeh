<?php

namespace App\Filament\Resources\HallResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\HallResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hall;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListHalls extends ListRecords
{
    use AccTrait;
    protected static string $resource = HallResource::class;

    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ادخال'),
            Actions\Action::make('acc')
                ->label('add acc')
                ->visible(fn(): bool=>Auth::id()==1)
                ->action(function (){
                    $places = Hall::all();
                    foreach ($places as $place){
                        if (!$place->account){
                            $this->AddAcc(AccRef::halls,$place);
                        }
                    }
                }),

        ];
    }
}
