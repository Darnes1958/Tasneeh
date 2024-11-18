<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\ItemResource;
use App\Livewire\Traits\AccTrait;
use App\Livewire\Traits\PublicTrait;
use App\Models\Hand;
use App\Models\Item;
use App\Models\Man;
use App\Models\OurCompany;
use App\Models\Place;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Unit;

class ListItems extends ListRecords
{
    use AccTrait,PublicTrait;
    protected static string $resource = ItemResource::class;

    public function getTitle():  string|Htmlable
    {
        return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">أصناف</div>');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('إضافة صنف جديد'),
            Actions\Action::make('prinitem')
             ->label('طباعة')
             ->icon('heroicon-s-printer')
             ->color('success')
             ->action(function (){
                 return Response::download(self::ret_spatie($this->getTableQueryForExport()->get(),
                     'PrnView.pdf-items'), 'filename.pdf', self::ret_spatie_header());
             }),
            Actions\Action::make('acc')
                ->label('add acc')
                ->visible(fn(): bool=>Auth::id()==1)
                ->action(function (){
                    $items=Item::all();
                    foreach ($items as $item){
                        if ($item->balance>0 && $item->price_buy>0)
                        {
                            $place=Place::find($item->place_id);
                            $this->AddKyde($place->account->id,AccRef::makzoone->value,$item,$item->price_buy*$item->balance,now(),'مخزون بداية المدة');
                        }
                    }

                }),
        ];
    }
}
