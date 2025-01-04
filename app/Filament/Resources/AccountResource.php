<?php

namespace App\Filament\Resources;

use App\Enums\AccLevel;
use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use App\Models\KydeData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Collection;
use Svg\Tag\Text;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationLabel='حسابات';
    protected static ?string $navigationGroup='محاسبة';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                 ->schema([
                     Radio::make('acc_level')
                         ->label('مستوي الحساب')
                         ->options(AccLevel::class)
                         ->disabled(fn($operation): bool=>$operation=='edit')
                         ->live()
                         ->inline()
                         ->afterStateHydrated(function (Radio $component, string $state,Set $set) {
                             $last=Account::orderby('created_at','desc')->first();
                             if ($last)
                                 $set('acc_level',$last->acc_level->value);
                         })
                         ->afterStateUpdated(function (Set $set,$state,Get $get){
                             $set('grand_id', null);
                             $set('father_id', null);
                             $set('son_id', null);

                             $set('id', null);
                             $set('theGrand', null);
                             $set('theFather', null);
                             $set('theSon', null);

                         })
                         ->inlineLabel(false)
                         ->default(1),
                     Select::make('theGrand')
                         ->label('الحساب الرئيسي')
                         ->dehydrated(false)
                         ->options(Account::where('acc_level',1)
                             ->whereNotIn('id',KydeData::select('account_id')->get())
                             ->pluck('name', 'id'))
                         ->preload()
                         ->searchable()
                         ->required()
                         ->live()
                         ->visible(fn(Get $get,$operation): bool=> ($get('acc_level')>1 && $operation=='create'))
                         ->afterStateUpdated(function ($state,Set $set,Get $get): void {
                            $set('grand_id',$state);
                             if ($get('acc_level')==2) {
                                 $set('num',Account::where('grand_id',$state)
                                         ->where('acc_level',2)->max('num') + 1);
                                 $set('id',strval($state).'-'.strval($get('num')));
                             }
                             if ($get('acc_level')==3) {$set('theFather',null);}

                         }),
                     Select::make('theFather')
                         ->label('الحساب الفرعي')
                         ->dehydrated(false)
                         ->options(function (Get $get){
                             $theGrand=Account::query()->where('acc_level',2)
                                 ->where('grand_id', $get('grand_id'))
                                 ->whereNotIn('id',KydeData::select('account_id')->get())
                                 ->pluck('name', 'id');
                             if (! $theGrand) return Account::query()->where('acc_level',2)
                                 ->pluck('name', 'id');
                             return $theGrand;
                         })
                         ->preload()
                         ->searchable()
                         ->required()
                         ->live()
                         ->visible(fn(Get $get,$operation): bool=> ($get('acc_level')>2 && $operation=='create'))
                         ->disabled(fn(Get $get): bool=> !$get('theGrand'))
                         ->afterStateUpdated(function ($state,Set $set,Get $get): void {
                             $set('father_id',$state);
                             if ($get('acc_level')==3) {
                                 $set('num',Account::where('father_id',$state)
                                         ->where('acc_level',3)->max('num') + 1);
                                 $set('id',strval($state).'-'.strval($get('num')));
                             }
                             if ($get('acc_level')==4) {$set('theSon',null);}

                         }),
                     Select::make('theSon')
                         ->label('الحساب التحليلي')
                         ->dehydrated(false)
                         ->options(function (Get $get){
                             $theFather=Account::query()->where('acc_level',3)
                                 ->where('father_id', $get('father_id'))
                                 ->whereNotIn('id',KydeData::select('account_id')->get())
                                 ->pluck('name', 'id');
                             if (! $theFather) return Account::query()->where('acc_level',3)
                                 ->pluck('name', 'id');
                             return $theFather;
                         })
                         ->preload()
                         ->searchable()
                         ->required()
                         ->live()
                         ->visible(fn(Get $get,$operation): bool=> ($get('acc_level')>3 && $operation=='create'))
                         ->disabled(fn(Get $get): bool=> !$get('theFather'))
                         ->afterStateUpdated(function ($state,Set $set,Get $get): void {
                             $set('son_id',$state);
                             if ($get('acc_level')==4) {
                                 $set('num',Account::where('son_id',$state)
                                         ->where('acc_level',4)->max('num') + 1);
                                 $set('id',strval($state).'-'.strval($get('num')));
                             }

                         }),

                     TextInput::make('name')
                         ->label('اسم الحساب')
                         ->required(),
                     TextInput::make('id')
                         ->label('رقم الحساب')
                         ->default(function (Get $get){
                             if ($get('acc_level')==1) return strval(Account::where('acc_level',1)->max('num')+1);
                         })
                         ->readOnly(),
                     Hidden::make('num')
                         ->default(function (Get $get){
                             if ($get('acc_level')==1) return Account::where('acc_level',1)->max('num')+1;
                         }),
                     Hidden::make('grand_id')
                         ,
                     Hidden::make('father_id')
                         ,
                     Hidden::make('son_id'),
                     Hidden::make('is_active')
                         ->default(1) ,

                 ])
                ->columnSpan(1),

            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('id')
                 ->searchable()
                 ->sortable()
                 ->label('الحساب'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('الاسم'),
                TextColumn::make('full_name')
                    ->label('الاسم الكامل'),
                TextColumn::make('acc_level')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('المستوي'),
            ])
            ->filters([
                //
            ])
            ->actions([
               Tables\Actions\EditAction::make()
                ->iconButton(),

            Tables\Actions\Action::make('del')
                 ->icon('heroicon-o-trash')
                 ->visible(function (Model $record){

                     if (KydeData::where('account_id',$record->id)->exists()) {return false;}
                     if ($record->has('Grands')) {
                         foreach ($record->Grands as $grand) {
                             if (KydeData::where('account_id',$grand->id)->exists()) {return false;}
                         }
                     }
                     if ($record->has('Fathers')) {
                         foreach ($record->Fathers as $father) {
                             if (KydeData::where('account_id',$father->id)->exists()) {return false;}
                         }
                     }
                     if ($record->has('Sons')) {
                         foreach ($record->Sons as $son) {
                             if (KydeData::where('account_id',$son->id)->exists()) {return false;}
                         }
                     }

                     return true;
                 })
               ->iconButton()
              ->color('danger')
               ->requiresConfirmation()
               ->modalHeading('الغاء الحساب')
               ->action(function (Model $record) {

                   if ($record->acc_level->value==3)
                           Account::where('son_id',$record->id)->delete();

                   if ($record->acc_level->value==2) {
                           Account::where('father_id',$record->id)->where('acc_level',4)->delete();
                           Account::where('father_id',$record->id)->where('acc_level',3)->delete();
                       }
                   if ($record->acc_level->value==1) {

                           Account::where('grand_id',$record->id)->where('acc_level',4)->delete();

                           Account::where('grand_id',$record->id)->where('acc_level',3)->delete();

                           Account::where('grand_id',$record->id)->where('acc_level',2)->delete();
                       }


                   $record->delete();
               }),
            ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
