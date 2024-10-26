<?php

namespace App\Filament\Resources;


use App\Enums\AccRef;
use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Livewire\Traits\AccTrait;
use App\Models\Buy_tran;

use App\Models\Item;
use App\Models\OurCompany;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Price_buy;

use App\Models\Sell_tran;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemResource extends Resource
{
    use AccTrait;
    protected static ?string $model = Item::class;

    protected static ?string $pluralModelLabel='أصناف';
    protected static ?int $navigationSort=1;
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
                    ->required(),

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
                TextInput::make('price_buy')
                ->required()
                ->label('سعر الشراء'),
                Hidden::make('price_cost'),
                Fieldset::make()
                 ->columnSpan(2)
                 ->schema([
                     TextInput::make('balance')
                     ->label('رصيد سابق')
                     ->default(0)
                     ->required()
                     ->live()
                     ->disabled(fn(string $operation)=>$operation=='edit'),
                     Select::make('place_id')
                      ->label('مكان التخزين')
                      ->relationship('Place','name')
                      ->required(fn ($get) => ! blank($get('balance')) && $get('balance')>0)
                      ->disabled(fn ($get,string $operation) => $operation=='edit' || blank($get('balance')) || $get('balance')==0 )
                      ->preload()
                      ->createOptionForm([
                          Section::make()
                           ->schema([
                               TextInput::make('name')
                                   ->label('الاسم')
                           ])
                      ])
                      ->editOptionForm([
                          Section::make()
                              ->schema([
                                  TextInput::make('name')
                                      ->label('الاسم')
                              ])
                      ])
                 ])

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


                TextColumn::make('stock')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('الرصيد'),
                TextColumn::make('balance')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('رصيد سابق')
                    ->action(
                        Tables\Actions\Action::make('updateBalance')
                         ->fillForm(fn(Model $record): array=>[
                             'balance' => $record['balance'],
                             'price_buy'=> $record['price_buy'],
                             'price_cost'=> $record['price_cost'],
                             'place_id' => $record['place_id'],
                         ])
                         ->form([
                             TextInput::make('balance')
                                 ->label('رصيد سابق')
                                 ->default(0)
                                 ->required()
                                 ->live(),
                             TextInput::make('price_buy')
                                 ->label('سعر الشراء')
                                 ->default(0)
                                 ->required()
                                 ->live(),
                             Hidden::make('price_cost'),
                             Select::make('place_id')
                                 ->visible(fn(Item $record): bool => blank($record['place_id'] ))
                                 ->label('مكان التخزين')
                                 ->relationship('Place','name')
                                 ->required(fn ($get) => ! blank($get('balance')) && $get('balance')>0)
                                 ->disabled(fn ($get) =>  blank($get('balance')) || $get('balance')==0 )
                                 ->preload()
                         ])
                            ->modalCancelActionLabel('عودة')
                            ->modalSubmitActionLabel('تحزين')
                            ->modalHeading('تعديل الرصيد السابق')
                            ->action(function (array $data,Model $record) {
                                $oldBalance=$record['balance'];
                                $oldPlace=$record['place_id'];

                                if ($record->place_id) {
                                    $place = Place_stock::where('place_id',$record['place_id'])
                                        ->where('item_id',$record['id'])
                                        ->first();
                                    if ($place) {
                                        if ($place->stock-$record->balance+$data['balance']<0)
                                        {
                                            Notification::make()
                                             ->title('لا تجوز هذه الكمية سيكون الرصيد اقل من صفر')
                                             ->success()
                                             ->send();
                                             return;
                                        }
                                        $thePlace=$record['place_id'];
                                        $place->stock =$place->stock-$oldBalance+$data['balance'];
                                        $place->save();
                                        $record->update(['balance' => $data['balance'],
                                            'price_buy'=> $data['price_buy'],
                                            'price_cost'=> $data['price_buy'],
                                            'stock'=>Place_stock::where('item_id',$record['id'])->sum('stock'),]);
                                        $record->save();

                                    } else
                                    {
                                        $thePlace=$data['place_id'];
                                        Place_stock::create([
                                            'stock' => $data['balance'],
                                            'place_id' => $data['place_id'],
                                            'item_id' => $record['id'],
                                        ]);
                                        $record->update(['balance' => $data['balance'],
                                            'place_id' => $data['place_id'],
                                            'price_buy'=> $data['price_buy'],
                                            'price_cost'=> $data['price_buy'],
                                            'stock'=>Place_stock::where('item_id',$record['id'])->sum('stock'),
                                            ]);

                                    }
                                    $Item=Item::find($record['id']);
                                    if ($Item->kyde)
                                        foreach ($Item->kyde as $rec) $rec->delete();

                                    $place=Place::find($thePlace);
                                    if ($record['balance']!=0)
                                    self::AddKyde2($place->account->id,AccRef::makzoone->value,$Item,$record['price_buy']*$record['balance'],now(),'مخزون بداية المدة');
                                }
                            })
                    ),
                TextColumn::make('price_buy')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                ->label('سعر الشراء'),
                TextColumn::make('price_cost')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('سعر التكلفة'),
                TextColumn::make('buy_tot')
                    ->summarize(Summarizer::make()
                        ->numeric(
                            decimalPlaces: 2,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        )
                        ->using(fn (\Illuminate\Database\Query\Builder $query): string => $query->sum(DB::Raw('stock*price_buy')))
                    )
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('الاجمالي'),
            ])
            ->filters([
                Filter::make('is_zero')
                 ->label('رصيدها صفر')
                 ->query(fn(Builder $query): Builder=>$query->where('stock',0)),
                Filter::make('not_zero')
                    ->label('رصيدها لا يساوي صفر')
                    ->query(fn(Builder $query): Builder=>$query->where('stock','!=',0)),
                Filter::make('has_balance')
                    ->label('لديها رصيد سابق')
                    ->query(fn(Builder $query): Builder=>$query->where('balance','!=',0)),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => !$record->Buy_tran()->exists()
            )
            ->actions([
              Tables\Actions\EditAction::make()->iconButton(),
              Tables\Actions\DeleteAction::make()

                  ->hidden(fn ($record):bool =>
                  $record->Buy_tran()->exists() || $record->balance>0
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
