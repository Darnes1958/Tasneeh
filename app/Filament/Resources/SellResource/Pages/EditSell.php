<?php

namespace App\Filament\Resources\SellResource\Pages;

use App\Filament\Resources\SellResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Buy;
use App\Models\Hall_stock;
use App\Models\Item;
use App\Models\Place_stock;
use App\Models\Product;
use App\Models\Sell;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSell extends EditRecord
{
    use AccTrait;
    protected static string $resource = SellResource::class;


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
        $last=$this->getRecord()->sell_tran;
        $cuurent=$this->data['Sell_tran'];
        foreach ($last as $item){
            $last_quant=$item->q;
            $current_quant=0;
            foreach ($cuurent as $key => $tran) {
                if ($item->product_id==$tran['product_id']) $current_quant=$tran['q'];
            }
            if ((Hall_stock::where('product_id',$item->product_id)
                        ->where('hall_id',$this->data['hall_id'])
                        ->first()->stock+$last_quant-$current_quant) <0) {
                Notification::make()->warning()->title('يوجد صنف او اصناف لا يمكن تعديلها لانها ستصبح بالسالب')
                    ->body('يجب مراجعة الكميات')
                    ->persistent()
                    ->send();
                $this->halt();
            }
        }
        foreach ($last as $item){
            if ($last->where('product_id',$item->product_id)->first()) {
            $res=Product::find($item->product_id);
            $res->stock+=$item->q;
            $res->save();
            $res=Hall_stock::where('hall_id',$this->data['hall_id'])
                ->where('product_id',$item->product_id)->first();
            $res->stock+=$item->q;
            $res->save();
            }
        }

        foreach ($cuurent as $key => $tran) {
            $item=Product::find($tran['product_id']);
            $item->price=$tran['p'];
            $item->stock -= $tran['q'];
            $item->save();
            $place=Hall_stock::where('product_id',$tran['product_id'])
                ->where('hall_id',$this->data['hall_id'])->first();
            $place->stock -= $tran['q'];
            $place->save();

        }

    }

        protected function afterSave(): void{
            $sell=Sell::find($this->data['id']);

            if ($sell->kyde)
                foreach ($sell->kyde as $rec) $rec->delete();

            self::inputKyde($sell);

        }

}
