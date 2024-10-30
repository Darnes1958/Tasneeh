<?php

namespace App\Filament\Resources\SalaryResource\Pages;

use App\Enums\AccRef;
use App\Filament\Resources\SalaryResource;
use App\Livewire\Traits\AccTrait;
use App\Models\Salary;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalary extends EditRecord
{
    use AccTrait;
    protected static string $resource = SalaryResource::class;
    protected ?string $heading='';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function beforeSave(): void
    {
        $res=Salary::find($this->data['id']);
        if ($res->account)
            $res->account->update(['name'=>$this->data['name']]);
        else $this->AddAcc(AccRef::salaries,$res);
    }
}
