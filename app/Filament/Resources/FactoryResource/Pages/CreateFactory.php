<?php

namespace App\Filament\Resources\FactoryResource\Pages;

use App\Filament\Resources\FactoryResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Factory;
use App\Models\Place_stock;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFactory extends CreateRecord
{
    use AccTrait;
    protected static string $resource = FactoryResource::class;
    protected ?string $heading='';
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()

            ->extraAttributes(['type' => 'button', 'wire:click' => 'create'])
            ;
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function beforeCreate(): void
    {
        $cuurent=$this->data['Tran'];

        foreach ($cuurent as $item) {

            if (Place_stock::where('item_id', $item['item_id'])
                    ->where('place_id', $this->data['place_id'])
                    ->first()->stock < $item['quant']) {
                Notification::make()->warning()->title('يوجد صنف او اصناف رصيدها لا يسمح')
                    ->body('يجب مراجعة الكميات')
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }
    }

    protected function afterCreate(): void{

        $fac=Factory::find(Factory::max('id'));
        self::inputKyde($fac);
        if ($fac->Hand){
            foreach ($fac->Hand as $hand){
                self::inputKyde($hand);
            }
        }
    }
}
