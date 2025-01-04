<?php

namespace App\Filament\Pages;

use App\Enums\AccLevel;
use App\Livewire\Traits\PublicTrait;
use App\Models\Account;
use App\Models\Accountsum;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class Mezan extends Page implements HasForms,HasTable
{
    use InteractsWithForms,InteractsWithTable;
    use PublicTrait;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.mezan';
    protected static ?string $navigationLabel='ميزان المراجعة';
    protected static ?string $navigationGroup='محاسبة';


    protected ?string $heading='';
    public $acc_level=1;
    public function form(Form $form): Form
    {
        return $form
            ->schema([
               Radio::make('acc_level')
                ->label('المستوي')
                ->inline()
                ->inlineLabel(false)
                ->options(AccLevel::class)

                ->live()
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(function (){
                if ($this->acc_level==1 ) return Accountsum::where('acc_level',$this->acc_level);
                else return Accountsum::where('acc_level',$this->acc_level)
                    ->orwhere(function ($q){
                        $q->where('acc_level','<',$this->acc_level)->where('is_active',1);
                    });
            })
            ->columns([
                TextColumn::make('id')
                ->label('رقم الحساب'),
                TextColumn::make('name')
                ->label('الاسم'),
                TextColumn::make('full_name')
                    ->hidden(fn(): bool=>$this->acc_level==1)
                    ->label('الاسم بالكامل'),
                ColumnGroup::make(
                    function (){return new HtmlString('<span class="text-primary-400">بالمجاميع</span>');},[
                    $this->getMdenFormComponent(),
                    $this->getDaenFormComponent(),

                ])->alignment(Alignment::Center),
                ColumnGroup::make(
                    function (){return new HtmlString('<span class="text-primary-400">بالأرصدة</span>');}, [
                    $this->getMden2FormComponent(),
                    $this->getDaen2FormComponent(),
                ])->alignment(Alignment::Center),


            ]);
    }

}
