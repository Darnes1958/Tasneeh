<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\FactoryResource\Pages;
use App\Filament\Resources\FactoryResource\RelationManagers;
use App\Models\Buy;
use App\Models\Factory;
use App\Models\Hall;
use App\Models\Hall_stock;
use App\Models\Item;
use App\Models\Item_type;
use App\Models\Man;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Product;

use Awcodes\TableRepeater\Header;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Awcodes\TableRepeater\Components\TableRepeater;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;


class FactoryResource extends Resource
{
    protected static ?string $model = Factory::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel='تصنيع وانتاج';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                    Select::make('product_id')
                        ->relationship('Product', 'name')
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('اسم المنتج')
                                ->required()
                                ->live()
                                ->unique()
                                ->validationMessages([
                                    'unique' => ' :attribute مخزون مسبقا ',
                                ]),
                            Select::make('category_id')
                                ->label('التصنيف')
                                ->relationship('Category','name')
                                ->required()
                                ->createOptionForm([
                                    Section::make('ادخال تصنيف منتجات')
                                        ->description('ادخال تصنيف  (صالون , دولاب . طاولة .... الخ)')
                                        ->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->unique()
                                                ->label('الاسم'),
                                        ])
                                ])
                                ->editOptionForm([
                                    Section::make('تعديل تصنيف ')
                                        ->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->label('الاسم'),
                                        ])->columns(2)
                                ]),
                            Textarea::make('description')
                                ->label('الوصف (اختياري)'),
                            FileUpload::make('image')
                                ->directory('productImages')
                                ->label('صورة'),
                            Forms\Components\Hidden::make('user_id')
                                ->default(auth()->id()),
                        ])
                        ->editOptionForm([
                            TextInput::make('name')
                                ->label('اسم المنتج')
                                ->required()
                                ->live()
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => ' :attribute مخزون مسبقا ',
                                ]),
                            Select::make('category_id')
                                ->label('التصنيف')
                                ->relationship('Category','name')
                                ->required()
                                ->createOptionForm([
                                    Section::make('ادخال تصنيف منتجات')
                                        ->description('ادخال تصنيف  (صالون , دولاب . طاولة .... الخ)')
                                        ->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->unique()
                                                ->label('الاسم'),
                                        ])
                                ])
                                ->editOptionForm([
                                    Section::make('تعديل تصنيف ')
                                        ->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->label('الاسم'),
                                        ])->columns(2)
                                ]),
                            Textarea::make('description')
                                ->label('الوصف (اختياري)'),
                            FileUpload::make('image')
                                ->directory('productImages')
                                ->label('صورة'),
                            Forms\Components\Hidden::make('user_id')
                                ->default(auth()->id()),
                        ])
                        ->searchable()
                        ->required()
                        ->preload()
                        ->live()
                        ->columnSpan(3)
                        ->label('المنتج'),
                        Select::make('place_id')
                            ->relationship('Place', 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('place_type',0),)
                            ->searchable()
                            ->columnSpan(3)
                            ->required()
                            ->preload()
                            ->default(Place::where('place_type',0)->first()->id)
                            ->label('من مخزن'),
                    DatePicker::make('process_date')
                        ->default(now())
                        ->required()
                        ->columnSpan(2)
                        ->label('تاريخ بداية التصنيع'),

                    TextInput::make('quantity')
                        ->columnSpan(2)
                        ->required()
                        ->label('الكمية'),
                    TextInput::make('price')
                        ->columnSpan(2)
                        ->label('السعر')
                        ->required(),
                    TextInput::make('tot')
                        ->columnSpan(2)
                        ->readOnly()
                        ->default(0)
                        ->label('الاجمالي'),
                    TextInput::make('handwork')
                        ->columnSpan(2)
                        ->default(0)
                        ->label('تكلفة التشغيل')
                        ->readOnly(),
                    TextInput::make('cost')
                        ->columnSpan(2)
                        ->default(0)
                        ->label('اجمالي التكلفة')
                        ->readOnly(),
                    Hidden::make('user_id')
                      ->default(Auth::id()),
                ])
                    ->columns(6)
                    ->columnSpan(6),
                Section::make()
                   ->schema([
                       TableRepeater::make('Tran')
                           ->hiddenLabel()
                           ->required()
                           ->relationship()
                           ->addActionLabel('اضافة صنف')
                           ->headers([
                               Header::make('رقم الصنف')
                                   ->width('40%'),
                               Header::make('الكمية')
                                   ->width('20%'),
                               Header::make('الرصيد')
                                   ->width('20%'),
                               Header::make('السعر')
                                   ->width('20%'),
                           ])
                           ->live()
                           ->afterStateUpdated(function ($state,Forms\Set $set,Get $get){
                               $total=0;
                               foreach ($state as $item){
                                   if ($item['quant'] && $item['price']) {
                                       $total +=round($item['quant'] * $item['price'],3);
                                   }
                               }
                               $set('tot',$total);
                               $set('cost',round($total+$get('handwork'),3));
                           })
                           ->defaultItems(0)
                           ->addable(function ($state){
                               $flag=true;
                               foreach ($state as $item) {
                                   if (!$item['item_id'] || !$item['quant'] ) {$flag=false; break;}
                               }
                               return $flag;
                           })
                           ->schema([
                               Select::make('item_id')
                                   ->required()
                                   ->searchable()
                                   ->options(function (Get $get){
                                       return Item::
                                       whereIn('id',Place_stock::where('place_id',$get('../../place_id'))->pluck('item_id'))->pluck('name','id');
                                   }
                                       )
                                   ->live()
                                   ->afterStateUpdated(function ($state,Forms\Set $set,Get $get){
                                       $set('price',Item::find($state)->price_cost);
                                       $set('stock',Place_stock::where('place_id',$get('../../place_id'))
                                           ->where('item_id',$state)->first()->stock);
                                   })
                                   ->disableOptionWhen(function ($value, $state, Get $get) {
                                       return collect($get('../*.item_id'))
                                           ->reject(fn($id) => $id == $state)
                                           ->filter()
                                           ->contains($value);
                                   }),
                               TextInput::make('quant')
                                   ->live(onBlur: true)
                                   ->extraInputAttributes(['tabindex' => 1])

                                   ->required(),
                               TextInput::make('stock')
                                   ->dehydrated(false),

                               TextInput::make('price')
                                   ->readOnly()
                                   ->required(),
                           ])
                           ->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get,$operation): array {
                               $item=Item::find($data['item_id']);
                               $item->stock -= $data['quant'];
                               $item->save();
                               $place=Place_stock::where('item_id',$data['item_id'])
                                   ->where('place_id',$get('place_id'))->first();
                               $place->stock-= $data['quant'];
                               $place->save();
                               return $data;
                           })
                   ])
                  ->columnSpan(6),
                Section::make()
                  ->schema([
                      TableRepeater::make('Hand')
                          ->hiddenLabel()
                          ->relationship()
                          ->addActionLabel('اضافة مشغل')
                          ->headers([
                              Header::make('الاسم')
                                  ->width('50%'),
                              Header::make('بتاريخ')
                                  ->width('30%'),
                              Header::make('المبلغ')
                                  ->width('20%'),


                          ])
                          ->live()
                          ->afterStateUpdated(function ($state,Forms\Set $set,Get $get){
                              $total=0;
                              foreach ($state as $item){
                                  if ($item['man_id'] && $item['val']) {
                                      $total +=$item['val'] ;
                                  }
                              }
                              $set('handwork',$total);
                              $set('cost',round($total+$get('tot'),3));
                          })
                          ->defaultItems(0)
                          ->addable(function ($state){
                              $flag=true;
                              foreach ($state as $item) {
                                  if (!$item['man_id'] || !$item['val'] ) {$flag=false; break;}
                              }
                              return $flag;
                          })
                          ->schema([
                              Select::make('man_id')
                                  ->required()
                                  ->preload()
                                  ->searchable()
                                  ->relationship('Man','name')
                                  ->disableOptionWhen(function ($value, $state, Get $get) {
                                      return collect($get('../*.man_id'))
                                          ->reject(fn($id) => $id == $state)
                                          ->filter()
                                          ->contains($value);
                                  })
                                  ->createOptionForm([
                                      Section::make('ادخال مشغل')
                                          ->schema([
                                              TextInput::make('name')
                                                  ->label('الاسم')
                                                  ->autocomplete(false)
                                                  ->required()
                                                  ->live()
                                                  ->unique()
                                                  ->validationMessages([
                                                      'unique' => ' :attribute مخزون مسبقا ',
                                                  ])
                                                  ->columnSpan(2),
                                          ])
                                          ->columns(4)
                                  ])
                                  ->createOptionForm([
                                      Section::make('ادخال مشغل')
                                          ->schema([
                                              TextInput::make('name')
                                                  ->label('الاسم')
                                                  ->autocomplete(false)
                                                  ->required()
                                                  ->live()
                                                  ->unique(ignoreRecord: true)
                                                  ->validationMessages([
                                                      'unique' => ' :attribute مخزون مسبقا ',
                                                  ])
                                                  ->columnSpan(2),
                                          ])
                                          ->columns(4)
                                  ]),
                              DatePicker::make('val_date')
                                ->default(now())
                                ->columnSpan(1)
                                ->required(),
                              TextInput::make('val')
                                  ->live(onBlur: true)
                                  ->extraInputAttributes(['tabindex' => 1])
                                  ->columnSpan(1)
                                  ->required(),

                              Hidden::make('user_id')
                              ->default(Auth::id())
                          ])
                  ])
                 ->columnSpan(6)
            ])
            ->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                 ->searchable()
                 ->sortable()
                 ->action(
                     Action::make('ready')
                         ->fillForm(fn(Factory $record): array => [
                             'ready_date' => now(),
                         ])
                         ->form([
                             DatePicker::make('ready_date')
                                 ->label('تاريخ انتهاء العمل')
                                 ->hidden(fn(Factory $record): bool =>  $record->status->value=='ready')
                                 ->required(),
                             Select::make('hall_id')
                                 ->label('مكان التخزين')
                                 ->hidden(fn(Factory $record): bool =>  $record->status->value=='ready')
                                 ->options(Hall::all()->pluck('name','id'))
                                 ->searchable()
                                 ->preload()
                                 ->createOptionForm([
                                     Section::make('ادخال صالة عرض')
                                         ->schema([
                                             TextInput::make('name')
                                                 ->label('الاسم')
                                                 ->autocomplete(false)
                                                 ->required()
                                                 ->live()
                                                 ->unique(ignoreRecord: true)
                                                 ->validationMessages([
                                                     'unique' => ' :attribute مخزون مسبقا ',
                                                 ])
                                                 ->columnSpan(2),
                                             Select::make('hall_type')
                                              ->label('النوع')
                                              ->default(0)
                                              ->required()
                                              ->options(PlaceType::class)
                                         ])
                                         ->columns(4)
                                 ])
                                 ->createOptionUsing(function (array $data): int {
                                     return Hall::create($data)->getKey();
                                 })
                                 ->required()
                         ])
                    //     ->visible(fn(Model $record): bool =>$record->status->value=='manufacturing')
                         ->modalCancelActionLabel('عودة')
                         ->modalSubmitActionLabel('تحزين')
                         ->modalWidth(MaxWidth::Medium)
                         ->modalHeading('تغيير الحالة')
                         ->action(function (Factory $record,array $data){
                             if ($record->status->value==='ready'){
                                 $hall=Hall_stock::where('product_id',$record->product_id)
                                     ->where('hall_id',$record->hall_id)->first();
                                 if ($hall->stock<$record->quantity) {
                                     Notification::make('')
                                         ->title('الرصيد لا يسمح')
                                         ->send();
                                     return;
                                 }
                                 $hall->stock -= $record->quantity;
                                 $hall->save();

                                 $record->ready_date=null;
                                 $record->status='manufacturing';
                                 $record->hall_id=null;
                                 $record->save();

                                 $prod=Product::find($record->product_id);
                                 $prod->stock -=$record->quantity;
                                 $prod->save();
                             } else {
                                 $hall=Hall_stock::where('product_id',$record->product_id)
                                     ->where('hall_id',$data['hall_id'])->first();
                                 if ($hall) {$hall->stock += $record->quantity; $hall->save();}
                                 else
                                     Hall_stock::create([
                                         'product_id'=>$record->product_id,
                                         'hall_id'=>$data['hall_id'],
                                         'stock'=>$record->quantity,
                                     ]);

                                 $record->ready_date=$data['ready_date'];
                                 $record->status='ready';
                                 $record->hall_id=$data['hall_id'];
                                 $record->save();

                                 $prod=Product::find($record->product_id);
                                 $p=( ($prod->cost*$prod->stock) + ($record->cost) )
                                     / ($prod->stock+$record->quantity);
                                 $prod->cost=$p;
                                 $prod->stock +=$record->quantity;
                                 $prod->price=$record->price;

                                 $prod->save();
                             }

                         })
                 )
                 ->label('الحالة'),
                TextColumn::make('Product.name')
                    ->description(function (Model $record) {
                        return $record->Product->description;
                    })
                    ->searchable()
                    ->sortable()
                    ->label('اسم المنتج'),
                Tables\Columns\ImageColumn::make('Product.image')
                 ->circular()
                 ->label(''),
                TextColumn::make('process_date')
                    ->searchable()
                    ->sortable()
                    ->label('تاريخ بدء التصنيع'),
                TextColumn::make('ready_date')
                    ->searchable()
                    ->sortable()
                    ->label('تاريخ الانتاج'),
                TextColumn::make('quantity')
                    ->searchable()
                    ->sortable()
                    ->label('العدد'),
                TextColumn::make('tot')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي المواد'),

                TextColumn::make('handwork')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->searchable()
                    ->sortable()
                    ->label('عمل اليد'),
                TextColumn::make('cost')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي التكلفة'),
                TextColumn::make('price')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->searchable()
                    ->sortable()
                    ->label('سعر المنتج'),
                TextColumn::make('price_tot')
                    ->summarize(Tables\Columns\Summarizers\Summarizer::make()
                        ->using(function (Table $table) {
                            return $table->getRecords()->sum('price_tot');
                        })
                    )
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي السعر'),
            ])
            ->filters([
               //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->iconButton(),
                Action::make('del')
                    ->icon('heroicon-o-trash')
                    ->modalHeading('الغاء التصنيع')
                    ->visible(function (Model $record) {return $record->status->value=='manufacturing';})
                    ->iconSize(IconSize::Small)
                    ->requiresConfirmation()
                    ->color('danger')
                    ->iconButton()
                    ->action(function (Model $record){
                        foreach ($record->Tran as $tran) {
                            $place=Place_stock::where('item_id',$tran->item_id)
                                ->where('place_id',$record->place_id)->first();
                            $place->stock+=$tran->quant;
                            $place->save();
                            $item=Item::find($tran->item_id);
                            $item->stock+=$tran->quant;
                            $item->save();
                        }
                        $record->delete();
                    }),
                Action::make('the_tran')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-m-list-bullet')
                    ->color('success')
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
                    ->modalContent(fn (Factory $record): View => view(
                        'view-tran-widget',
                        ['factory_id' => $record->id],
                    )),
                Action::make('the_hand')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('info')
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
                    ->modalContent(fn (Factory $record): View => view(
                        'view-hand-widget',
                        ['factory_id' => $record->id],
                    )),
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
            'index' => Pages\ListFactories::route('/'),
            'create' => Pages\CreateFactory::route('/create'),
            'edit' => Pages\EditFactory::route('/{record}/edit'),
        ];
    }
}
