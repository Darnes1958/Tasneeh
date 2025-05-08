<?php

namespace App\Filament\Resources;

use App\Enums\AccRef;
use App\Enums\PlaceType;
use App\Filament\Resources\SellResource\Pages;
use App\Filament\Resources\SellResource\RelationManagers;
use App\Livewire\Traits\AccTrait;
use App\Models\Buy;
use App\Models\Customer;
use App\Models\Hall_stock;
use App\Models\Item;
use App\Models\Item_type;
use App\Models\OurCompany;
use App\Models\Place_stock;
use App\Models\Product;
use App\Models\Sell;
use App\Models\Setting;
use App\Models\Unit;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Spatie\Browsershot\Browsershot;

class SellResource extends Resource
{
    use AccTrait;
    protected static ?string $model = Sell::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مبيعات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('Customer_id')
                            ->searchable()
                            ->preload()
                            ->default(Customer::min('id'))
                            ->prefix('الزبون')
                            ->hiddenLabel()
                            ->relationship('Customer','name')
                            ->live()
                            ->required()
                            ->columnSpan('full')
                            ->createOptionForm([
                                Section::make('ادخال زبون جديد')
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
                                $thekey=Customer::create($data)->getKey();
                                $hall=Customer::find($thekey);

                                return $thekey;
                            }),
                        Select::make('hall_id')
                            ->disabled(function ($operation){
                                return $operation=='edit';
                            })
                            ->prefix('نقطة اللبيع')
                            ->hiddenLabel()
                            ->relationship('Hall','name')
                            ->live()
                            ->required()
                            ->columnSpan('full')
                            ->createOptionForm([
                                Section::make('ادخال نقطة بيع')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Radio::make('hall_type')
                                            ->label('النوع')
                                            ->inline()
                                            ->inlineLabel(false)
                                            ->options(PlaceType::class)
                                    ])
                            ])
                            ->editOptionForm([
                                Section::make('ادخال نقطة بيع')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->label('الاسم'),
                                        Radio::make('hall_type')
                                            ->label('النوع')
                                            ->inline()
                                            ->inlineLabel(false)
                                            ->options(PlaceType::class)
                                    ])
                            ]),
                        DatePicker::make('order_date')
                            ->id('order_date')
                            ->default(now())
                            ->label('التاريخ')
                            ->columnSpan(2)
                            ->required(),

                        TextInput::make('tot')
                            ->label('إجمالي الفاتورة')
                            ->columnSpan(2)
                            ->default(0)
                            ->readOnly(),
                        TextInput::make('ksm')
                            ->label('الخصم')
                            ->columnSpan(2)
                            ->default(0),

                        Forms\Components\Textarea::make('notes')
                            ->live()
                            ->label('ملاحظات')
                            ->columnSpan('full'),

                        Hidden::make('user_id')
                            ->default(Auth::id()),
                    ])
                    ->columns(6)
                    ->columnSpan(4),
                Section::make()
                    ->schema([
                        TableRepeater::make('Sell_tran')
                            ->hiddenLabel()
                            ->required()
                            ->relationship()
                            ->headers([
                                Header::make('المنتج')
                                    ->width('40%'),
                                Header::make('الكمية')
                                    ->width('20%'),
                                Header::make('السعر')
                                    ->width('20%'),
                                Header::make('الرصيد')
                                    ->width('20%'),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->required()
                                    ->searchable()
                                    ->options(function (Get $get){
                                        return
                                        Product::query()
                                            ->where('stock','>',0)
                                            ->whereIn('id',Hall_stock::
                                                where('hall_id',$get('../../hall_id'))
                                                ->where('stock','>',0)
                                                ->pluck('product_id')  )
                                            ->pluck('name','id');
                                    })
                                    ->disableOptionWhen(function ($value, $state, Get $get) {

                                        return collect($get('../*.product_id'))
                                            ->reject(fn($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                   ->afterStateUpdated(function ($state,  Forms\Set $set,Get $get) {
                                       $prod=Product::find($state);
                                       $set('p',$prod->price);
                                       $set('c',$prod->cost);
                                       $set('stock',Hall_stock::where('product_id',$state)
                                           ->where('hall_id',$get('../../hall_id'))
                                           ->first()->stock);
                                   }),
                                TextInput::make('q')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state,Forms\Set $set,Get $get,$operation){
                                        if ($state){
                                            if (Hall_stock::where('product_id',$get('product_id'))
                                            ->where('hall_id',$get('../../hall_id'))->first()->stock<$state){
                                                Notification::make()
                                                    ->title('رصيد الصنفلا يكفي')
                                                    ->send();
                                                $set('q',0);

                                            }
                                        }
                                    })
                                    ->extraInputAttributes(['tabindex' => 1])
                                    ->columnSpan(1)
                                    ->required(),

                                TextInput::make('p')
                                    ->live(onBlur: true)
                                    ->extraInputAttributes(['tabindex' => 2])
                                    ->columnSpan(1)
                                    ->required() ,
                                TextInput::make('stock')
                                    ->dehydrated(false) ,
                                Hidden::make('c'),
                                Hidden::make('profit'),
                                Hidden::make('user_id')->default(Auth::id()),
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state,Forms\Set $set,Get $get){
                                $total=0;
                                foreach ($state as $item){
                                    if ($item['p'] && $item['q']) {
                                        $total +=$item['p'] * $item['q'];

                                    }

                                }
                                $set('tot',$total);
                            })
                            ->columnSpan('full')
                            ->defaultItems(0)
                            ->addActionLabel('اضافة منتج')
                            ->addable(function ($state,Get $get){
                                $flag=true;
                                if (!$get('hall_id') )  return false;
                                foreach ($state as $item) {
                                    if (!$item['product_id'] || !$item['p'] || !$item['q']) {$flag=false; break;}
                                }
                                return $flag;
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get): array {
                                $data['user_id'] = auth()->id();
                                $data['profit']=($data['p'] * $data['q']) - ($data['c'] * $data['q']);
                                $prod=Product::find($data['product_id']);
                                $prod->stock -= $data['q'];
                                $prod->save();
                                $place=Hall_stock::where('product_id',$data['product_id'])
                                    ->where('hall_id',$get('hall_id'))->first();
                                $place->stock-= $data['q'];
                                $place->save();
                                return $data;
                            })
                    ])
                    ->columnSpan(8),
            ])->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->label('الرقم الالي'),
                TextColumn::make('Customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الزبون'),
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
                TextColumn::make('pay')
                    ->summarize(Sum::make()->label('')

                        ->numeric(
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
                    ->summarize(Tables\Columns\Summarizers\Summarizer::make()
                        ->label('')
                        ->numeric(
                            decimalPlaces: 2,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        )
                        ->using(function (Table $table) {
                            return $table->getRecords()->sum('baky');
                        })
                    )
                    ->label('المتبقي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
                //
            ])

            ->actions([

                Tables\Actions\EditAction::make()
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
                        foreach ($record->Sell_tran as $tran) {
                            $place=Hall_stock::where('product_id',$tran->product_id)
                                ->where('hall_id',$record->hall_id)->first();
                            $place->stock+=$tran->q;
                            $place->save();
                            $item=Product::find($tran->product_id);
                            $item->stock+=$tran->q;
                            $item->save();
                        }


                        $record->delete();
                    }),
                Action::make('selltran')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-o-list-bullet')
                    ->color('success')
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
                    ->modalContent(fn (Sell $record): View => view(
                        'view-sell-tran-widget',
                        ['sell_id' => $record->id],
                    )),
                Action::make('print')
                    ->iconButton()
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(function (Model $record) {
                        $RepDate=date('Y-m-d');
                        $cus=OurCompany::where('Company',Auth::user()->company)->first();

                        \Spatie\LaravelPdf\Facades\Pdf::view('PrnView.pdf-sell-order',
                            ['res'=>$record,
                                'cus'=>$cus,'RepDate'=>$RepDate,
                            ])
                            ->footerView('PrnView.footer')
                            ->margins(10, 10, 20, 10)
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
            'index' => Pages\ListSells::route('/'),
            'create' => Pages\CreateSell::route('/create'),
            'edit' => Pages\EditSell::route('/{record}/edit'),
        ];
    }
}
