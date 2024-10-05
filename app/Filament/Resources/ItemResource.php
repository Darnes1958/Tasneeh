<?php

namespace App\Filament\Resources;

use App\Enums\TwoUnit;
use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Buy_tran;
use App\Models\Item;
use App\Models\Price_buy;
use App\Models\Price_sell;
use App\Models\Sell_tran;
use App\Models\Setting;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $pluralModelLabel='أصناف';
  protected static ?string $navigationGroup='مخازن و أصناف';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مشتريات');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                 ->hidden(fn(string $operation)=>$operation=='create')
                 ->disabled()
                 ->label('الرقم الألي'),
                TextInput::make('name')
                 ->label('اسم الصنف')
                 ->required()
                  ->live()
                ->unique(ignoreRecord: true)
                  ->validationMessages([
                    'unique' => ' :attribute مخزون مسبقا ',
                  ])
                ->columnSpan(2),



                Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('Unit','name')
                    ->required()
                    ->columnSpan(2)
                    ->createOptionForm([
                        Section::make('ادخال وحدات')
                            ->description('ادخال وحدة  (صندوق,دزينه,كيس .... الخ)')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique()
                                    ->label('الاسم'),
                            ])
                    ])
                    ->editOptionForm([
                        Section::make('تعديل وحدات ')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique()
                                    ->label('الاسم'),
                            ])->columns(2)
                    ]),


              TextInput::make('count')
                    ->label('العدد')
                    ->required()
                    ,
              TextInput::make('price_buy')
                ->label('سعر الشراء')
                ->disabled(fn(string $operation)=>$operation=='edit')
                ->required()
                ->id('price_buy'),


                Select::make('item_type_id')
                    ->label('التصنيف')
                    ->relationship('Item_type','name')
                    ->required()
                    ->columnSpan(2)
                    ->createOptionForm([
                        Section::make('ادخال تصنيف للأصناف')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique()
                                    ->label('الاسم'),
                            ])
                    ])
                    ->editOptionForm([
                        Section::make('تعديل تصنيف')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique()
                                    ->label('الاسم'),
                            ])->columns(2)
                    ]),

            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                 ->label('الرقم الألي')
                 ->sortable()
                 ->searchable(),

                TextColumn::make('name')
                    ->label('اسم الصنف')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Unit.name')
                    ->label('الوحدة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('count')
                    ->label('العدد')
                    ->sortable()
                    ->formatStateUsing(function (string $state) {
                        if ($state==1) return '';
                        return $state;
                    })
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('الرصيد'),
                TextColumn::make('price_buy')
                ->label('سعر الشراء'),
                TextColumn::make('price_cost')
                    ->label('سعر التكلفة'),
            ])
            ->filters([
                //
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => !$record->Buy_tran()->exists()
            )
            ->actions([
              Tables\Actions\EditAction::make()->iconButton(),
              Tables\Actions\DeleteAction::make()
                  ->hidden(fn ($record):bool =>
                  $record->Buy_tran()->exists()
                  || !Auth::user()->can('تعديل مشتريات')
                  )
                  ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
