<?php

namespace App\Filament\Resources\MasrofatResource\Pages;

use App\Filament\Resources\MasrofatResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Masrofat;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditMasrofat extends EditRecord
{
    use AccTrait;
    protected static string $resource = MasrofatResource::class;
    protected ?string $heading='';

   protected function afterSave(): void {
       $masrofat=Masrofat::find($this->data['id']);
       if ($masrofat->kyde) foreach ($masrofat->kyde as $kyde) {$kyde->delete();}
       self::inputKyde($masrofat);
   }
}
