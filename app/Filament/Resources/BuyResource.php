<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Spatie\LaravelPdf\Facades\Pdf;
use App\Filament\Resources\BuyResource\Pages\ListBuys;
use App\Filament\Resources\BuyResource\Pages\CreateBuy;
use App\Filament\Resources\BuyResource\Pages\EditBuy;



use App\Filament\Resources\BuyResource\RelationManagers;

use App\Livewire\Traits\AccTrait;

use App\Models\Buy;

use App\Models\Item;

use App\Models\OurCompany;
use App\Models\Place;
use App\Models\Place_stock;


use App\Models\Setting;
use App\Models\Supplier;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Spatie\Browsershot\Browsershot;

class BuyResource extends Resource
{
    use AccTrait;
    protected static ?string $model = Buy::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel='فاتورة مشتريات';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([

                        Select::make('supplier_id')
                            ->default(Supplier::min('id'))
                            ->preload()
                            ->searchable()
                            ->prefix('المورد')
                            ->hiddenLabel()
                            ->relationship('Supplier','name')
                            ->live()
                            ->required()
                            ->columnSpan('full')
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
                            ->createOptionUsing(function (array $data): int {
                                $thekey=Supplier::create($data)->getKey();
                                $hall=Supplier::find($thekey);

                                return $thekey;
                            }),
                        DatePicker::make('order_date')
                            ->id('order_date')
                            ->default(now())
                            ->autofocus()
                            ->label('التاريخ')
                            ->columnSpan(2)
                            ->required(),
                        TextInput::make('tot')
                            ->label('إجمالي الفاتورة')
                            ->columnSpan(2)
                            ->default(0)
                            ->readOnly(),
                        TextInput::make('cost')
                            ->label('تكاليف اضافية')
                            ->columnSpan(2)
                            ->readOnly()
                            ->default('0'),

                        Select::make('place_id')
                            ->searchable()
                            ->preload()
                            ->default(fn()=>Place::min('id'))
                            ->disabled(function ($operation){
                                return $operation=='edit';
                            })
                            ->label('مكان التخزين')
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
                                        Hidden::make('place_type')
                                            ->default(0)

                                    ])
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $thekey=Place::create($data)->getKey();
                                $place=Place::find($thekey);

                                return $thekey;
                            }),
                        TextInput::make('ksm')
                            ->label('الخصم')
                            ->columnSpan(2)
                            ->default('0'),

                        TextInput::make('notes')
                            ->live()
                            ->prefix('ملاحظات')
                            ->hiddenLabel()
                            ->columnSpan('full'),

                        Hidden::make('user_id')
                         ->default(Auth::id()),
                    ])
                    ->columns(6)
                    ->columnSpan(5),
                Section::make()
                 ->schema([
                     Repeater::make('Buy_tran')
                         ->hiddenLabel()
                         ->required()
                         ->relationship()
                         ->table([
                             Repeater\TableColumn::make('رقم الصنف')
                                 ->width('50%'),
                             Repeater\TableColumn::make('الكمية')
                                 ->width('15%'),
                             Repeater\TableColumn::make('الاجمالي')
                                 ->width('20%'),
                             Repeater\TableColumn::make('السعر')
                                 ->width('15%'),
                         ])
                         ->schema([
                             Select::make('item_id')
                                 ->required()
                                 ->preload()
                                 ->searchable()
                                 ->relationship('Item','name')
                             //    ->options(Item::all()->pluck('name','id'))
                                 ->disableOptionWhen(function ($value, $state, Get $get) {
                                     return collect($get('../*.item_id'))
                                         ->reject(fn($id) => $id == $state)
                                         ->filter()
                                         ->contains($value);
                                 })
                                 ->createOptionForm([
                                     Section::make('ادخال صنف')
                                         ->schema([
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
                                             Select::make('item_type_id')
                                                 ->label('التصنيف')
                                                 ->relationship('Item_type','name')
                                                 ->required()
                                                 ->searchable()
                                                 ->preload()
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
                                                     Section::make('تعديل تصنيف للأصناف')
                                                         ->schema([
                                                             TextInput::make('name')
                                                                 ->required()
                                                                 ->unique(ignoreRecord: true)
                                                                 ->label('الاسم'),
                                                         ])
                                                 ]),
                                             Select::make('unit_id')
                                                 ->label('الوحدة')
                                                ->relationship('Unit','name')
                                                 ->required()
                                                 ->searchable()
                                                 ->preload()
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
                                                 ->editOptionForm([
                                                     Section::make('تعديل وحدات ')
                                                         ->description('ادخال وحدة (صندوق,دزينه,كيس .... الخ)')
                                                         ->schema([
                                                             TextInput::make('name')
                                                                 ->required()
                                                                 ->unique(ignoreRecord: true)
                                                                 ->label('الاسم'),
                                                         ])
                                                 ]),
                                             TextInput::make('count')
                                                 ->label('العدد')
                                                 ->required(),
                                             Hidden::make('price_cost')
                                                 ->default(0)
                                             ,
                                             Hidden::make('user_id')
                                                 ->default(Auth::id()),
                                         ])
                                         ->columns(4)
                                 ])
                                 ->editOptionForm([
                                     Section::make('تعديل صنف')
                                         ->schema([
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
                                             Select::make('item_type_id')
                                                 ->label('التصنيف')
                                                 ->relationship('Item_type','name')
                                                 ->required()
                                                 ->searchable()
                                                 ->preload()
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
                                                     Section::make('تعديل تصنيف للأصناف')
                                                         ->schema([
                                                             TextInput::make('name')
                                                                 ->required()
                                                                 ->unique(ignoreRecord: true)
                                                                 ->label('الاسم'),
                                                         ])
                                                 ]),
                                             Select::make('unit_id')
                                                 ->label('الوحدة')
                                                 ->relationship('Unit','name')
                                                 ->required()
                                                 ->searchable()
                                                 ->preload()
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
                                                 ->editOptionForm([
                                                     Section::make('تعديل وحدات ')
                                                         ->description('ادخال وحدة (صندوق,دزينه,كيس .... الخ)')
                                                         ->schema([
                                                             TextInput::make('name')
                                                                 ->required()
                                                                 ->unique(ignoreRecord: true)
                                                                 ->label('الاسم'),
                                                         ])
                                                 ]),
                                             TextInput::make('count')
                                                 ->label('العدد')
                                                 ->required(),
                                             Hidden::make('price_cost')
                                                 ->default(0)
                                             ,
                                             Hidden::make('user_id')
                                                 ->default(Auth::id()),
                                         ])
                                         ->columns(4)
                                 ]),

                             TextInput::make('quant')
                                 ->live(onBlur: true)
                                 ->extraInputAttributes(['tabindex' => 1])
                                 ->afterStateUpdated(function (Get $get,Set $set,$state){
                                     if ($get('sub_sub')!=null){
                                         $set('price_input',round($get('sub_sub')/$state,3));
                                         $set('price_cost',round($get('sub_sub')/$state,3));
                                     }
                                 })
                                 ->columnSpan(1)
                                 ->required(),
                             TextInput::make('sub_sub')
                                 ->live(onBlur: true)
                                 ->extraInputAttributes(['tabindex' => 2])
                                 ->afterStateUpdated(function ($state,Set $set,Get $get){
                                     $set('price_input',round($state/$get('quant'),3));
                                     $set('price_cost',round($state/$get('quant'),3));
                                 })
                                 ->columnSpan(1)
                                 ->required(),

                             TextInput::make('price_input')
                                 ->readOnly()
                                 ->columnSpan(1)
                                 ->required() ,
                             Hidden::make('price_cost'),
                         ])
                         ->live()
                         ->afterStateUpdated(function ($state,Set $set,Get $get){
                             $total=0;
                             foreach ($state as $item){
                                 if ($item['sub_sub'] && $item['quant']) {
                                     $total +=$item['sub_sub'];

                                 }

                             }
                             $set('tot',$total);
                         })
                         ->columnSpan('full')
                         ->defaultItems(0)
                         ->addActionLabel('اضافة صنف')
                         ->addable(function ($state){
                             $flag=true;
                             foreach ($state as $item) {
                                 if (!$item['item_id'] || !$item['sub_sub'] || !$item['quant']) {$flag=false; break;}
                             }
                             return $flag;
                         })
                         ->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get,$operation): array {
                             $data['user_id'] = auth()->id();
                             if ($get('cost')!=0) {
                                 $ratio=($data['quant']*$data['price_input'])/$get('tot')*100;
                                 $data['price_cost']=(($ratio/100*$get('cost'))/$data['quant'])+$data['price_input'];
                             }

                                 $item=Item::find($data['item_id']);
                                 $p=( ($item->price_buy*$item->stock) + ($data['quant']*$data['price_input']) )
                                     / ($item->stock+$data['quant']);
                                 $pc=( ($item->price_cost*$item->stock) + ($data['quant']*$data['price_cost']) )
                                     / ($item->stock+$data['quant']);
                                 $item->price_cost=$pc;
                                 $item->price_buy=$p;
                                 if ($operation=='create')
                                    $item->stock += $data['quant'];
                                 $item->save();
                             if ($operation=='create') {
                                 $place=Place_stock::where('item_id',$data['item_id'])
                                     ->where('place_id',$get('place_id'))->first();
                                 if ($place) {
                                     $place->stock+= $data['quant'];
                                     $place->save();
                                 } else {
                                     Place_stock::create([
                                         'item_id'=>$data['item_id'],
                                         'place_id'=>$get('place_id'),
                                         'stock'=>$data['quant'],
                                     ]);
                                 }
                             }
                             return $data;
                         })
                 ])
                 ->columnSpan(7),
                Section::make()
                    ->heading('تكاليف اضافية')
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                         Repeater::make('costs')
                             ->hiddenLabel()
                             ->relationship()
                             ->table([
                                 Repeater\TableColumn::make('نوع التكلفة')
                                     ->width('50%'),
                                 Repeater\TableColumn::make('المبلغ')
                                     ->width('30%'),

                             ])
                             ->schema([
                                 Select::make('costtype_id')
                                     ->required()
                                     ->preload()
                                     ->searchable()
                                     ->relationship('Costtype','name')
                                     ->disableOptionWhen(function ($value, $state, Get $get) {
                                         return collect($get('../*.costtype_id'))
                                             ->reject(fn($id) => $id == $state)
                                             ->filter()
                                             ->contains($value);
                                     })

                                     ->createOptionForm([
                                         Section::make('ادخال نوع تكلفة')
                                             ->schema([

                                                 TextInput::make('name')
                                                     ->label('البيان')
                                                     ->autocomplete(false)
                                                     ->required()
                                                     ->live()
                                                     ->unique()
                                                     ->validationMessages([
                                                         'unique' => ' :attribute مخزون مسبقا ',
                                                     ])
                                                     ->columnSpan(2),
                                             ])
                                             ->columns(3)
                                     ])
                                     ->editOptionForm([
                                         Section::make('تعديل نوع تكلفة')
                                             ->schema([
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

                                     ]),
                                 TextInput::make('val')
                                     ->extraInputAttributes(['tabindex' => 1])
                                     ->columnSpan(1)
                                     ->required(),

                             ])
                             ->live(onBlur: true)
                             ->afterStateUpdated(function ($state,Set $set,Get $get){
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
                    ->columnSpan(5),
            ])->columns(12);
    }

    protected static ?string $pluralLabel='فواتير المشتريات';
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
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي الفاتورة'),
                TextColumn::make('ksm')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('الخصم'),
                TextColumn::make('total')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Summarizer::make()->label('')
                        ->numeric(
                            decimalPlaces: 2,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        )
                        ->using(fn ($query): string => $query->sum(DB::Raw('tot-ksm')))
                    )
                    ->label('الصافي'),
                TextColumn::make('pay')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('المدفوع'),
                TextColumn::make('baky')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Summarizer::make()->label('')
                        ->numeric(
                            decimalPlaces: 2,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        )
                        ->using(fn ($query): string => $query->sum(DB::Raw('tot-pay-ksm')))
                    )
                    ->label('المطلوب'),
                TextColumn::make('cost')
                    ->summarize(Sum::make()->label('')->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ))
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->label('تكلفة اضافية'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->defaultKeySort(false)
            ->defaultSort('id','desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                ->iconSize(IconSize::Small)
                ->iconButton(),
                Action::make('del')
                 ->icon('heroicon-o-trash')
                 ->modalHeading('الغاء الفاتورة')
                 ->iconSize(IconSize::Small)
                 ->requiresConfirmation()
                 ->color('danger')
                 ->iconButton()
                 ->action(function (Model $record){
                     $minus=false;
                     foreach ($record->Buy_tran as $item){
                         if ($item->quant>Place_stock::where('item_id',$item->item_id)
                                 ->where('place_id',$record->place_id)->first()->stock){
                             Notification::make()->warning()->title('يوجد صنف او اصناف لا يمكن الغاءها لانها ستصبح بالسالب')
                                 ->body('يجب مراجعة الكميات')
                                 ->persistent()
                                 ->send();
                             break;
                             $minus=true;
                         }
                     }
                     if ($minus) return;

                     foreach ($record->Buy_tran as $tran) {
                         $place=Place_stock::where('item_id',$tran->item_id)
                             ->where('place_id',$record->place_id)->first();
                         $place->stock-=$tran->quant;
                         $place->save();
                         $item=Item::find($tran->item_id);
                         $item->stock-=$tran->quant;
                         $item->save();
                     }

                      $record->delete();
                 }),
                Action::make('buytran')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-o-list-bullet')
                    ->color('success')
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
                    ->modalContent(fn (Buy $record): View => view(
                        'view-buy-tran-widget',
                        ['buy_id' => $record->id],
                    )),
                Action::make('the_cost')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-o-document-currency-dollar')
                    ->color('info')
                    ->visible(fn ($record): bool => $record->cost>0)
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (Action $action) => $action->label('عودة'))
                    ->modalContent(fn (Buy $record): View => view(
                        'view-cost-widget',
                        ['buy_id' => $record->id],
                    )),
                Action::make('print')
                    ->iconButton()
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Model $record) {

                        $RepDate=date('Y-m-d');
                        $cus=OurCompany::where('Company',Auth::user()->company)->first();

                        Pdf::view('PrnView.pdf-buy-order',
                            ['res'=>$record,
                                'cus'=>$cus,'RepDate'=>$RepDate,
                            ])
                            ->footerView('PrnView.footer')
                           ->headerView('PrnView.header',['cus'=>$cus,])
                            ->margins(40, 10, 20, 10)
                            ->withBrowsershot(function (Browsershot $shot) {
                                $shot->setOption('gnoreDefaultArgs', ['--disable-extensions'])
                                    ->ignoreHttpsErrors()
                                    ->noSandbox()
                                    ->setChromePath(Setting::first()->exePath);
                            })
                            ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
                        $file= public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

                        $headers = [
                            'Content-Type' => 'application/pdf',
                        ];
                        return Response::download($file, 'filename.pdf', $headers);
                    })


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
            'index' => ListBuys::route('/'),
            'create' => CreateBuy::route('/create'),
            'edit' => EditBuy::route('/{record}/edit'),
        ];
    }
}
