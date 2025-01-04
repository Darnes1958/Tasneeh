<?php

namespace App\Filament\Resources\KydeResource\Pages;

use App\Filament\Resources\KydeResource;
use App\Models\Account;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKyde extends CreateRecord
{
    protected static string $resource = KydeResource::class;
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
        $collect=collect($this->data['KydeData']);
        $notfilled=$collect->where('mden',0)->where('daen',0)->first();
        if ($notfilled) {
            Notification::make()
                ->warning()
                ->title('يجب ادخال مبلغ دائن او مدين للحساب ('.Account::find($notfilled['account_id'])->full_name.' )')
                ->persistent()
                ->send();
            $this->halt();
        }

        $mden=$collect->sum('mden');
        $daen=$collect->sum('daen');
        if ($mden!=$daen) {
            Notification::make()
                ->warning()
                ->title('القيد غير متوازن')
                ->persistent()
                ->send();
            $this->halt();
        }
    }
}
