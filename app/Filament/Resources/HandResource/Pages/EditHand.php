<?php

namespace App\Filament\Resources\HandResource\Pages;

use App\Filament\Resources\HandResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use App\Models\Hand;
use App\Models\Kazena;
use App\Models\Man;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHand extends EditRecord
{
    use AccTrait;
    protected static string $resource = HandResource::class;
    protected ?string $heading='تعديل بيانات دفع لمشغل';




}
