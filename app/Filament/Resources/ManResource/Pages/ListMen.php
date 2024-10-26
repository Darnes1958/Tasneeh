<?php

namespace App\Filament\Resources\ManResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\ManResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Hand;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMen extends ListRecords
{
    use AccTrait;
    protected static string $resource = ManResource::class;

    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('اضافة مشغل'),
            Actions\Action::make('acc')
                ->label('add acc')
                ->visible(fn(): bool=>Auth::id()==1)
                ->action(function (){
                    $places = Man::all();
                    foreach ($places as $place){
                        if (!$place->account){
                            $this->AddAcc(AccRef::mans,$place);
                        }
                    }
                    $hands=Hand::all();
                    foreach ($hands as $hand){
                        if (!$hand->kyde){

                        }
                    }
                }),
        ];
    }
}
