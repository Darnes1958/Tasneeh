<?php

namespace App\Filament\Resources\AccResource\Pages;

use App\Filament\Resources\AccResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAcc extends EditRecord
{
    use AccTrait;
    protected static string $resource = AccResource::class;

    protected ?string $heading='';


}
