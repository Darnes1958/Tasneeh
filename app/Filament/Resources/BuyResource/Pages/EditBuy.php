<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Filament\Resources\BuyResource;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Place_stock;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBuy extends EditRecord
{
    protected static string $resource = BuyResource::class;
    protected ?string $heading='تعديل فاتورة شراء';
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function beforeSave(): void {
        $last=$this->getRecord()->buy_tran;
        $cuurent=$this->data['Buy_tran'];
        foreach ($last as $item){
            $last_quant=$item->quant;
            $current_quant=0;
            foreach ($cuurent as $key => $tran) {
                if ($item->item_id==$tran['item_id']) $current_quant=$tran['quant'];
            }
               if ((Place_stock::where('item_id',$item->item_id)
                           ->where('place_id',$this->data['place_id'])
                           ->first()->stock-$last_quant+$current_quant) <0) {
                 Notification::make()->warning()->title('يوجد صنف او اصناف لا يمكن تعديلها لانها ستصبح بالسالب')
                   ->body('يجب مراجعة الكميات')
                 ->persistent()
                  ->send();
               $this->halt();
              }
        }
        foreach ($last as $item){
            Item::find($item->item_id)
                ->update(['stock'=>$item->stock-$item->quant]);
            Place_stock::where('place_id',$this->data['place_id'])
                ->where('item_id',$item->item_id)
                ->update(['stock'=>$item->stock-$item->quant]);

        }
        foreach ($cuurent as $key => $tran) {
            if ($this->data['cost']!=0) {
                $ratio=($tran['quant']*$tran['price_input'])/$this->data['tot']*100;
                $tran['price_cost']=(($ratio/100*$this->data['cost'])/$tran['quant'])+$tran['price_input'];
            }
            $item=Item::find($tran['item_id']);

            $item->stock += $tran['quant'];
            $item->save();
            $place=Place_stock::where('item_id',$tran['item_id'])
                ->where('place_id',$this->data['place_id'])->first();
            if ($place) {
                $place->stock+= $tran['quant'];
                $place->save();
            } else {
                Place_stock::insert([
                    'item_id'=>$tran['item_id'],
                    'place_id'=> $this->data['place_id'],
                    'stock'=>$tran['quant'],
                ]);
            }

        }



    }

}
