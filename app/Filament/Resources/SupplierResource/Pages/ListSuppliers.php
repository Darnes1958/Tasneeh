<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use Filament\Actions\CreateAction;
use App\Enums\AccRef;
use App\Filament\Resources\SupplierResource;
use App\Livewire\Traits\AccTrait;

use App\Models\Supplier;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ListSuppliers extends ListRecords
{
    use AccTrait;
    protected static string $resource = SupplierResource::class;

  public function getTitle():  string|Htmlable
  {
    return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">موردين</div>');
  }
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()
        ->label('إضافة مورد جديد'),


    ];
  }
}
