<?php

namespace App\Livewire\widgets;

use App\Livewire\Traits\PublicTrait;
use App\Models\KydeData;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class KydeDataWidget extends BaseWidget
{
    use PublicTrait;
    public function mount($kyde_id)
    {
        $this->kyde_id=$kyde_id;
    }
    public $kyde_id;
    protected static ?string $heading='';
    public function table(Table $table): Table
    {
        return $table
            ->query( function() {
                return KydeData::query()->where('kyde_id',$this->kyde_id);
            }
            )
            ->columns([
                TextColumn::make('Account.id')
                    ->searchable()
                    ->sortable()
                    ->label('الحساب'),
                TextColumn::make('Account.name')
                    ->searchable()
                    ->sortable()
                    ->label('الاسم'),
                $this->getKydedataFormComponent('full_name'),
                $this->getMdenFormComponent(),
                $this->getDaenFormComponent(),
            ]);
    }
}
