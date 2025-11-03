<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ReceiptResource\Pages\ListReceipts;
use App\Filament\Resources\ReceiptResource\Pages\CreateReceipt;
use App\Filament\Resources\ReceiptResource\Pages\EditReceipt;
use App\Enums\PayType;
use App\Enums\RecWho;
use App\Filament\Resources\ReceiptResource\Pages;

use App\Models\Acc;
use App\Models\Customer;
use App\Models\Kazena;
use App\Models\Receipt;
use App\Models\Sell;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;

use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'ايصالات زبائن';
    protected static string | \UnitEnum | null $navigationGroup = 'ايصالات قبض ودفع';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال ايصالات زبائن');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               Radio::make('rec_who')
                ->inline()
                ->inlineLabel(false)
                ->label('نوع الايصال')
                ->default(1)
                ->live()
                ->columnSpan(2)
                ->options(RecWho::class),

               Select::make('customer_id')
                ->label('الزبون')
                ->relationship('Customer','name')
                ->searchable()
                ->required()
                ->live()
                ->preload()
                ->createOptionForm([
                       Section::make('ادخال زبون جديد')
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
                           ])
                   ])
                ->editOptionForm([
                       Section::make('تعديل بيانات زبون')
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
                   ]),
              Select::make('sell_id')
                ->label('رقم الفاتورة')
                ->options(fn (Get $get): Collection => Sell::query()
                  ->where('customer_id', $get('customer_id'))
                  ->selectRaw('\' رقم \'+str(id)+\' الاجمالي \'+str(tot)+\' بتاريخ \'+convert(varchar,order_date)+\' الباقي \'+str(tot-pay) as name,id')
                  ->pluck('name', 'id'))
                ->searchable()
                ->requiredIf('rec_who',[3,4])
                ->visible(fn(Get $get): bool =>($get('rec_who')->value==3 || $get('rec_who')->value ==4))
                ->preload(),

                Select::make('pay_type')
                    ->label('طريقة الدفع')
                    ->options(PayType::class)
                    ->live()
                    ->default(0)
                    ->required(),
                DatePicker::make('receipt_date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required(),
                TextInput::make('val')
                   ->label('المبلغ')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state,Get $get,Set $set){
                        if ($get('pay_type')->value==1 && $get('rate') && $state) {
                            $set('differ',$get('rate')*$state/100);
                        }
                    })
                   ->required()
                   ->numeric(),
                Select::make('acc_id')
                    ->label('المصرف')
                    ->relationship('Acc','name')
                    ->searchable()
                    ->required()
                    ->live()
                    ->preload()
                    ->visible(fn(Get $get): bool =>($get('pay_type')->value==1 ))
                    ->createOptionForm([
                        Section::make('ادخال حساب مصرفي جديد')
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم المصرف')
                                    ->required()
                                    ->autofocus()
                                    ->columnSpan(2)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => ' :attribute مخزون مسبقا ',
                                    ])        ,
                                TextInput::make('acc')
                                    ->label('رقم الحساب')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => ' :attribute مخزون مسبقا ',
                                    ])  ,
                                TextInput::make('raseed')
                                    ->label('رصيد بداية المدة')
                                    ->numeric()
                                    ->required()                          ,
                            ])
                    ])
                    ->editOptionForm([
                        Section::make('تعديل بيانات مصرف')
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم المصرف')
                                    ->required()
                                    ->autofocus()
                                    ->columnSpan(2)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => ' :attribute مخزون مسبقا ',
                                    ])        ,
                                TextInput::make('acc')
                                    ->label('رقم الحساب')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => ' :attribute مخزون مسبقا ',
                                    ])  ,
                                TextInput::make('raseed')
                                    ->label('رصيد بداية المدة')
                                    ->numeric()
                                    ->required()

                            ])->columns(2)
                    ]),
                Select::make('kazena_id')
                    ->label('الخزينة')
                    ->relationship('Kazena','name')
                    ->searchable()
                    ->required()
                    ->live()
                    ->preload()

                    ->default(function (){
                        $res=Kazena::where('user_id',Auth::id())->first();
                        if ($res) return $res->id;
                        else return null;
                    })
                    ->visible(fn(Get $get): bool =>($get('pay_type')->value==0 ))
                    ->createOptionForm([
                        Section::make('ادخال حساب خزينة جديد')
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم الخزينة')
                                    ->required()
                                    ->autofocus()
                                    ->columnSpan(2)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => ' :attribute مخزون مسبقا ',
                                    ])        ,
                                Select::make('user_id')
                                    ->label('المستخدم')
                                    ->searchable()
                                    ->preload()
                                    ->options(User::
                                    where('company',Auth::user()->company)
                                        ->where('id','!=',1)
                                        ->pluck('name','id')),
                                TextInput::make('balance')
                                    ->label('رصيد بداية المدة')
                                    ->numeric()
                                    ->required()                          ,
                            ])
                    ])
                    ->editOptionForm([
                        Section::make('تعديل بيانات خزينة')
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم الخزينة')
                                    ->required()
                                    ->autofocus()
                                    ->columnSpan(2)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => ' :attribute مخزون مسبقا ',
                                    ])        ,
                                Select::make('user_id')
                                    ->label('المستخدم')
                                    ->searchable()
                                    ->preload()
                                    ->options(User::
                                    where('company',Auth::user()->company)
                                        ->where('id','!=',1)
                                        ->pluck('name','id')),
                                TextInput::make('raseed')
                                    ->label('رصيد بداية المدة')
                                    ->numeric()
                                    ->required()

                            ])->columns(2)
                    ]),
                Fieldset::make('فرق عملة')
                  ->visible(fn(Get $get)=>$get('pay_type')->value==1)
                  ->columnSpan(2)
                  ->schema([
                      TextInput::make('rate')
                          ->hiddenLabel()
                          ->live(onBlur: true)
                          ->prefix('النسبة')
                          ->prefixIcon('heroicon-m-chart-pie')
                          ->prefixIconColor('danger')
                          ->afterStateUpdated(function ($state,Get $get,Set $set){
                              if ($get('val') && $state) {
                                  $set('differ',$get('val')*$state/100);
                              }
                          })
                          ->numeric()
                          ->minValue(0)
                          ->maxValue(100),
                      TextInput::make('differ')
                          ->hiddenLabel()
                          ->prefix('فرق عملة')
                          ->prefixIcon('heroicon-m-document-plus')
                          ->prefixIconColor('success')
                          ->readOnly(),
                  ]),

                TextInput::make('notes')
                 ->columnSpan(3)
                 ->label('ملاحظات'),
                Hidden::make('sell_id'),

                Hidden::make('imp_exp')
                ->default(0),
                Hidden::make('user_id')
                    ->default(Auth::id())
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id','desc')
            ->columns([
                TextColumn::make('index')
                    ->label('ت')
                    ->rowIndex(),
                TextColumn::make('id')
                  ->searchable()
                 ->label('الرقم الألي'),
                TextColumn::make('receipt_date')
                  ->searchable()
                    ->label('التاريخ'),
                TextColumn::make('customer.name')
                  ->searchable()
                    ->label('اسم الزبون'),
                TextColumn::make('pay_type')
                    ->description(function (Receipt $record){
                        $name=null;
                        if ($record->acc_id) {$name=Acc::find($record->acc_id)->name;}
                        if ($record->kazena_id) {$name=Kazena::find($record->kazena_id)->name;}
                        return $name;
                    })
                    ->label('طريقة الدفع'),
                TextColumn::make('rec_who')
                    ->label('البيان')
                    ->badge(),
                TextColumn::make('val')
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
                    ->label('المبلغ'),
                TextColumn::make('differ')
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
                    ->label('فرق عملة'),
                TextColumn::make('tot')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Summarizer::make()
                        ->numeric(
                            decimalPlaces: 2,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        )
                        ->using(function (Table $table) {
                            return $table->getRecords()->sum('tot');
                        })
                    )
                    ->searchable()
                    ->label('الاجمالي'),
                TextColumn::make('notes')
                    ->label('ملاحظات'),
            ])
            ->filters([
              SelectFilter::make('customer_id')
                ->options(Customer::all()->pluck('name', 'id'))
                ->searchable()
                ->label('زبون معين'),
              Filter::make('is_sell')
                ->label('ايصالات فاتورة')
                ->query(fn (Builder $query): Builder => $query->whereIn('rec_who', [3,4])),
              Filter::make('is_imp')
                ->label('ايصالات قبض')
                ->query(fn (Builder $query): Builder => $query->where('rec_who', 1)),
              Filter::make('is_exp')
                ->label('ايصالات دقع')
                ->query(fn (Builder $query): Builder => $query->where('rec_who', 2)),
              Filter::make('created_at')
                ->schema([
                  DatePicker::make('Date1')
                   ->label('من تاريخ'),
                  DatePicker::make('Date2')
                   ->label('إلي تاريخ'),
                ])
                  ->indicateUsing(function (array $data): ?string {
                      if (! $data['Date1'] && ! $data['Date2']) { return null;   }
                      if ( $data['Date1'] && !$data['Date2'])
                       return 'ادخلت بتاريخ  ' . Carbon::parse($data['Date1'])->toFormattedDateString();
                      if ( !$data['Date1'] && $data['Date2'])
                          return 'حتي تاريخ  ' . Carbon::parse($data['Date2'])->toFormattedDateString();
                      if ( $data['Date1'] && $data['Date2'])
                          return 'ادخلت في الفترة من  ' . Carbon::parse($data['Date1'])->toFormattedDateString()
                              .' إلي '. Carbon::parse($data['Date1'])->toFormattedDateString();

                  })
                ->query(function (Builder $query, array $data): Builder {
                  return $query
                    ->when(
                      $data['Date1'],
                      fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', '>=', $date),
                    )
                    ->when(
                      $data['Date2'],
                      fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', '<=', $date),
                    );
                })
            ])
            ->recordActions([
              EditAction::make()->iconButton()
                  ->color('blue')
                  ->visible(fn(Receipt $record): bool =>
                      $record->rec_who->value<7
                      || !Auth::user()->can('االغاء ايصالات زبائن')),
              DeleteAction::make()->iconButton()
                  ->visible(fn(Receipt $record): bool =>
                      $record->rec_who->value<7
                       || !Auth::user()->can('االغاء ايصالات زبائن'))
                ->modalHeading('حذف الإيصال')
                ->after(function (Receipt $record) {

                  if ($record->rec_who->value==3 || $record->rec_who->value==4 ) {

                    $sum=Receipt::where('sell_id',$record->sell_id)->where('rec_who',3)->sum('val');
                    $sub=Receipt::where('sell_id',$record->sell_id)->where('rec_who',4)->sum('val');

                    $sell=Sell::find($record->sell_id);
                    $sell->pay=$sum-$sub;
                    $sell->save();
                  }

                }),
            ])
            ->toolbarActions([
               //
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'edit' => EditReceipt::route('/{record}/edit'),
        ];
    }
}
