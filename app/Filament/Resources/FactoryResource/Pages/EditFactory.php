<?php

namespace App\Filament\Resources\FactoryResource\Pages;

use App\Filament\Resources\FactoryResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Factory;
use App\Models\Item;
use App\Models\Place_stock;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFactory extends EditRecord
{
    use AccTrait;
    protected static string $resource = FactoryResource::class;

    protected ?string $heading='';
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()

            ->extraAttributes(['type' => 'button', 'wire:click' => 'save'])
            ;
    }
    protected function beforeSave(): void {
        $last=$this->getRecord()->tran;
        $cuurent=$this->data['Tran'];
        foreach ($last as $item){
            $last_quant=$item->quant;
            $current_quant=0;
            foreach ($cuurent as $key => $tran) {
                if ($item->item_id==$tran['item_id']) $current_quant=$tran['quant'];
            }
            if ((Place_stock::where('item_id',$item->item_id)
                        ->where('place_id',$this->data['place_id'])
                        ->first()->stock+$last_quant-$current_quant) <0) {
                Notification::make()->warning()->title('يوجد صنف او اصناف لا يمكن تعديلها لانها ستصبح بالسالب')
                    ->body('يجب مراجعة الكميات')
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }
        foreach ($last as $item){
            $res=Item::find($item->item_id);
            $res->stock+=$item->quant;
            $res->save();
            $res=Place_stock::where('place_id',$this->data['place_id'])
                ->where('item_id',$item->item_id)->first();
            $res->stock+=$item->quant;
            $res->save();
        }

        foreach ($cuurent as $key => $tran) {
            $item=Item::find($tran['item_id']);
            $item->stock -= $tran['quant'];
            $item->save();
            $place=Place_stock::where('item_id',$tran['item_id'])
                ->where('place_id',$this->data['place_id'])->first();
            $place->stock-= $tran['quant'];
            $place->save();
        }
        $fac=Factory::find($this->data['id']);
        if ($fac->Hand) foreach ($fac->Hand as $hand) {$hand->kyde->delete();}

    }

    protected function afterSave(): void{
        $fac=Factory::find($this->data['id']);
        self::inputKydewithDelete($fac);
        if ($fac->Hand){
            foreach ($fac->Hand as $hand){
                self::inputKyde($hand);
            }
        }
    }

}
