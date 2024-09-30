<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;

use App\Filament\Resources\BuyResource\Pages;
use App\Filament\Resources\BuyResource\RelationManagers;

use App\Models\Buy;
use App\Models\Cost;
use App\Models\Costtype;
use App\Models\Item;
use App\Models\Item_type;
use App\Models\Place_stock;
use App\Models\Sell_tran;

use App\Models\Unit;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BuyResource extends Resource
{
    protected static ?string $model = Buy::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('order_date')
                            ->id('order_date')
                            ->default(now())
                            ->autofocus()
                            ->prefix('التاريخ')
                            ->hiddenLabel()
                            ->columnSpan(2)
                            ->required(),
                        Select::make('supplier_id')
                            ->default(1)
                            ->prefix('المورد')
                            ->hiddenLabel()
                            ->relationship('Supplier','name')
                            ->live()
                            ->required()
                            ->columnSpan(4)
                            ->createOptionForm([
                                Section::make('ادخال مورد جديد')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        TextInput::make('address')
                                            ->label('العنوان'),
                                        TextInput::make('mdar')
                                            ->label('مدار'),
                                        TextInput::make('libyana')
                                            ->label('لبيانا'),
                                        Hidden::make('user_id')
                                            ->default(Auth::id()),
                                    ])
                            ])
                            ->editOptionForm([
                                Section::make('تعديل بيانات مورد')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->label('الاسم'),
                                        TextInput::make('address')
                                            ->label('العنوان'),
                                        TextInput::make('mdar')
                                            ->label('مدار'),
                                        TextInput::make('libyana')
                                            ->label('لبيانا'),
                                        Hidden::make('user_id')
                                            ->default(Auth::id()),

                                    ])->columns(2)
                            ])
                            ->id('supplier_id'),
                        Select::make('price_type_id')
                            ->default(1)
                            ->prefix('طريقة الدفع')
                            ->hiddenLabel()
                            ->columnSpan(2)
                            ->live()
                            ->default(1)
                            ->relationship('Price_type','name')
                            ->required()
                            ->id('price_type_id'),
                        Select::make('place_id')
                            ->default(1)
                            ->prefix('مكان التخزين')
                            ->hiddenLabel()
                            ->relationship('Place','name')
                            ->live()
                            ->required()
                            ->columnSpan(4)
                            ->createOptionForm([
                                Section::make('ادخال مكان تخزين')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Radio::make('place_type')
                                            ->inline()
                                            ->options(PlaceType::class)
                                    ])
                            ])
                            ->editOptionForm([
                                Section::make('تعديل مكان تخزين')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Radio::make('place_type')
                                            ->inline()
                                            ->options(PlaceType::class)
                                    ])->columns(2)
                            ])
                            ->id('place_id'),
                        Section::make()
                         ->schema([
                             TextInput::make('tot')
                                 ->label('إجمالي الفاتورة')
                                 ->columnSpan(2)
                                 ->default(0)
                                 ->readOnly(),
                             TextInput::make('pay')
                                 ->label('المدفوع')
                                 ->minValue(0)
                                 ->columnSpan(2)
                                 ->afterStateUpdated(function ($state,Get $get,Forms\Set $set){
                                     if (!$state) $set('pay', 0);
                                     $set('baky', $get('tot')-$get('pay'));
                                 })
                                 ->live(onBlur: true)
                                 ->default('0')
                                 ->id('pay'),
                             TextInput::make('baky')
                                 ->label('المتبقي')
                                 ->disabled()
                                 ->columnSpan(2)
                                 ->default('0'),
                             TextInput::make('cost')
                                 ->label('تكاليف اضافية')
                                 ->columnSpan(2)
                                 ->readOnly()
                                 ->default('0'),
                         ])->columns(8)->columnSpan('full'),
                        Forms\Components\Textarea::make('notes')
                            ->live()

                            ->extraAttributes(['x-on:change' => 'myfun'])
                            ->label('ملاحظات')
                            ->columnSpan('full'),
                        Hidden::make('user_id')
                         ->default(Auth::id()),

                    ])
                    ->columns(6)
                    ->columnSpan(6),
                Section::make()
                 ->schema([
                     TableRepeater::make('Buy_tran')
                         ->hiddenLabel()
                         ->required()
                         ->relationship()
                         ->headers([
                             Header::make('رقم الصنف')
                                 ->width('50%'),
                             Header::make('الكمية')
                                 ->width('20%'),
                             Header::make('السعر')
                                 ->width('20%'),

                         ])
                         ->schema([
                             Select::make('item_id')
                                 ->required()
                                 ->searchable()
                                 ->options(Item::all()->pluck('name','id'))
                                 ->disableOptionWhen(function ($value, $state, Get $get) {
                                     return collect($get('../*.item_id'))
                                         ->reject(fn($id) => $id == $state)
                                         ->filter()
                                         ->contains($value);
                                 })
                                 ->createOptionForm([
                                     Section::make('ادخال صنف')
                                         ->schema([
                                             TextInput::make('id')
                                                 ->hidden(fn(string $operation)=>$operation=='create')
                                                 ->disabled()
                                                 ->label('الرقم الألي'),
                                             TextInput::make('name')
                                                 ->label('اسم الصنف')
                                                 ->autocomplete(false)
                                                 ->required()
                                                 ->live()
                                                 ->unique(ignoreRecord: true)
                                                 ->validationMessages([
                                                     'unique' => ' :attribute مخزون مسبقا ',
                                                 ])
                                                 ->columnSpan(2),
                                             Select::make('unit_id')
                                                 ->label('الوحدة')
                                                 ->options(Unit::all()->pluck('name','id'))
                                                 ->required()
                                                 ->columnSpan(2)
                                                 ->createOptionForm([
                                                     Section::make('ادخال وحدات ')
                                                         ->description('ادخال وحدة (صندوق,دزينه,كيس .... الخ)')
                                                         ->schema([
                                                             TextInput::make('name')
                                                                 ->required()
                                                                 ->unique()
                                                                 ->label('الاسم'),
                                                         ])
                                                 ])
                                                 ->createOptionUsing(function (array $data): int {
                                                     return Unit::create($data)->getKey();
                                                 }),
                                             TextInput::make('count')
                                                 ->label('العدد')
                                                 ->required(),
                                             TextInput::make('price_buy')
                                                 ->label('سعر الشراء')
                                                 ->required()
                                                 ->id('price_buy'),

                                             Select::make('item_type_id')
                                                 ->label('التصنيف')
                                                 ->options(Item_type::all()->pluck('name','id'))
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
                                                 ->createOptionUsing(function (array $data): int {
                                                     return Item_type::create($data)->getKey();
                                                 }),
                                             Hidden::make('price_cost')
                                                 ->default(0)
                                             ,
                                             Hidden::make('user_id')
                                                 ->default(Auth::id()),
                                         ])
                                         ->columns(4)
                                 ])
                                 ->createOptionUsing(function (array $data): int {
                                     return Item::create($data)->getKey();
                                 }),
                             TextInput::make('quant')
                                 ->extraInputAttributes(['tabindex' => 1])
                                 ->id('quant')
                                 ->columnSpan(1)
                                 ->required(),
                             TextInput::make('price_input')
                                 ->extraInputAttributes(['tabindex' => 2])
                                 ->afterStateUpdated(function ($state,Forms\Set $set){
                                     $set(('price_cost'),$state);
                                 })
                                 ->id('price_input')
                                 ->columnSpan(1)
                                 ->required() ,
                             Hidden::make('price_cost'),
                         ])
                         ->live()
                         ->afterStateUpdated(function ($state,Forms\Set $set,Get $get){
                             $total=0;
                             foreach ($state as $item){
                                 if ($item['price_input'] && $item['quant']) {
                                     $total +=$item['price_input']*$item['quant'];

                                 }

                             }
                             $set('tot',$total);
                             $set('baky',$total-$get('pay'));

                         })
                         ->columnSpan('full')
                         ->defaultItems(0)
                         ->addActionLabel('اضافة صنف')
                         ->addable(function ($state){
                             $flag=true;
                             foreach ($state as $item) {
                                 if (!$item['item_id'] || !$item['price_input'] || !$item['quant']) {$flag=false; break;}
                             }
                             return $flag;
                         })
                         ->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get,$operation): array {
                             $data['user_id'] = auth()->id();
                             if ($get('cost')!=0) {
                                 $ratio=($data['quant']*$data['price_input'])/$get('tot')*100;
                                 $data['price_cost']=(($ratio/100*$get('cost'))/$data['quant'])+$data['price_input'];
                             }
                             if ($operation=='create') {
                                 $item=Item::find($data['item_id']);
                                 $p=( ($item->price_buy*$item->stock) + ($data['quant']*$data['price_input']) )
                                     / ($item->stock+$data['quant']);
                                 $pc=( ($item->price_cost*$item->stock) + ($data['quant']*$data['price_cost']) )
                                     / ($item->stock+$data['quant']);


                                 $item->price_cost=$pc;
                                 $item->price_buy=$p;
                                 $item->stock += $data['quant'];
                                 $item->save();
                                 $place=Place_stock::where('item_id',$data['item_id'])
                                     ->where('place_id',$get('place_id'))->first();
                                 if ($place) {
                                     $place->stock+= $data['quant'];
                                     $place->save();
                                 } else {
                                     Place_stock::insert([
                                         'item_id'=>$data['item_id'],
                                         'place_id'=>$get('place_id'),
                                         'stock'=>$data['quant'],
                                     ]);
                                 }
                             }
                             return $data;
                         })
                 ])
                 ->columnSpan(6),
                Section::make()
                    ->heading('تكاليف اضافية')
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                        TableRepeater::make('Cost')
                            ->hiddenLabel()

                            ->relationship()
                            ->headers([
                                Header::make('نوع التكلفة')
                                    ->width('50%'),
                                Header::make('المبلغ')
                                    ->width('30%'),
                            ])
                            ->schema([
                                Select::make('costtype_id')

                                    ->required()
                                    ->searchable()
                                    ->options(Costtype::all()->pluck('name','id'))
                                    ->disableOptionWhen(function ($value, $state, Get $get) {
                                        return collect($get('../*.costtype_id'))
                                            ->reject(fn($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                    ->createOptionForm([
                                        Section::make('ادخال نوع تكلفة')
                                            ->schema([
                                                TextInput::make('id')
                                                    ->hidden(fn(string $operation)=>$operation=='create')
                                                    ->disabled()
                                                    ->label('الرقم الألي'),
                                                TextInput::make('name')
                                                    ->label('البيان')
                                                    ->autocomplete(false)
                                                    ->required()
                                                    ->live()
                                                    ->unique(ignoreRecord: true)
                                                    ->validationMessages([
                                                        'unique' => ' :attribute مخزون مسبقا ',
                                                    ])
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(3)
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Costtype::create($data)->getKey();
                                    }),
                                TextInput::make('val')

                                    ->extraInputAttributes(['tabindex' => 1])
                                    ->columnSpan(1)
                                    ->required(),

                            ])
                            ->live()
                            ->afterStateUpdated(function ($state,Forms\Set $set,Get $get){
                                $cost=0;
                                foreach ($state as $item){
                                    if ($item['val'] )
                                        $cost +=$item['val'];
                                }
                                $set('cost',$cost);


                            })
                            ->columnSpan('full')
                            ->defaultItems(0)
                            ->addActionLabel('اضافة تكلفة')
                            ->addable(function ($state){
                                $flag=true;
                                foreach ($state as $item) {
                                    if (!$item['costtype_id'] || !$item['val'] ) {$flag=false; break;}
                                }
                                return $flag;
                            })

                    ])
                ->columnSpan(6),
            ])->columns(12)
             ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()

                    ->sortable()
                    ->label('الرقم الالي'),
                TextColumn::make('Supplier.name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المورد'),
                TextColumn::make('order_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('tot')
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي الفاتورة'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->label('الباقي'),
                TextColumn::make('cost')
                    ->label('تكلفة اضافية'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBuys::route('/'),
            'create' => Pages\CreateBuy::route('/create'),
            'edit' => Pages\EditBuy::route('/{record}/edit'),
        ];
    }
}
