<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\PlaceResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Place;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPlaces extends ListRecords
{
    use  AccTrait;
    protected static string $resource = PlaceResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('ادخال'),
            Actions\Action::make('acc')
             ->label('add acc')
             ->visible(fn(): bool=>Auth::id()==1)
             ->action(function (){
                 $places = Place::all();
                 foreach ($places as $place){
                     if (!$place->account){
                         $this->AddAcc(AccRef::places,$place);
                     }
                 }
             }),
        ];
    }
}
