<?php

namespace App\Filament\Resources\BuyResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\BuyResource;

use App\Livewire\Traits\AccTrait;
use App\Models\Buy;
use App\Models\Place;
use App\Models\Supplier;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class CreateBuy extends CreateRecord
{
    use AccTrait;
    protected static string $resource = BuyResource::class;
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
    protected static bool $canCreateAnother = false;

    protected function afterCreate(): void
    {
        $buy=Buy::find(Buy::max('id'));
        $supp=Supplier::find($buy->supplier_id);
        $place=Place::find($buy->place_id);

        $this->AddKyde(AccRef::buys->value,$supp->account->id,$buy,$buy->tot,$buy->order_date,'فاتورة مشتريات');
        $this->AddKyde($place->account->id,AccRef::buys->value,$buy,$buy->tot,$buy->order_date,'من المشتريات الي المخازن');

        if ($buy->cost) self::inputKyde($buy->cost);

    }



}
