<?php

namespace App\Filament\Resources\KazenaResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\KazenaResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Kazena;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListKazenas extends ListRecords
{
    use AccTrait;
    protected static string $resource = KazenaResource::class;
    protected ?string $heading='خزائن';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('إضافة'),
            Actions\Action::make('acc')
                ->label('add acc')
                ->visible(fn(): bool=>Auth::id()==1)
                ->action(function (){
                    $places = Kazena::all();
                    foreach ($places as $place){
                        if (!$place->account){
                            $this->AddAcc(AccRef::kazena,$place);
                        }
                    }
                }),
        ];
    }
}
