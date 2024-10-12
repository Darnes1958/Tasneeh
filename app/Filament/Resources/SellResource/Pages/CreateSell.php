<?php

namespace App\Filament\Resources\SellResource\Pages;

use App\Filament\Resources\SellResource;
use App\Models\Hall_stock;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSell extends CreateRecord
{
    protected static string $resource = SellResource::class;
    protected ?string $heading='';
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function beforeCreate(): void
    {
        $cuurent=$this->data['Sell_tran'];
            foreach ($cuurent as  $tran) {
                if (Hall_stock::where('product_id', $tran['product_id'])
                        ->where('hall_id', $this->data['hall_id'])
                        ->first()->stock < $tran['q']) {
                    Notification::make()->warning()->title('يوجد صنف او اصناف أرصدتهالا تسمح')
                        ->body('يجب مراجعة الكميات')
                        ->persistent()
                        ->send();
                    $this->halt();
                }
            }
    }
}
