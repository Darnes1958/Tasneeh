<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\RecsuppResource\Pages\ListRecsupps;
use App\Filament\Resources\RecsuppResource\Pages\CreateRecsupp;
use App\Filament\Resources\RecsuppResource\Pages\EditRecsupp;
use App\Enums\AccRef;
use App\Enums\PayType;
use App\Enums\RecWho;
use App\Filament\Resources\RecsuppResource\Pages;
use App\Filament\Resources\RecsuppResource\RelationManagers;
use App\Livewire\Traits\AccTrait;
use App\Models\Acc;
use App\Models\Buy;
use App\Models\Kazena;
use App\Models\Recsupp;

use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\text;

class RecsuppResource extends Resource
{
    use AccTrait;
    protected static ?string $model = Recsupp::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

  protected static ?string $navigationLabel = 'ايصالات موردين';
  protected static string | \UnitEnum | null $navigationGroup = 'ايصالات قبض ودفع';
  protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال ايصالات موردين');
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
        TextInput::make('id')
         ->label('الرقم الألي')
         ->disabled()
         ->hidden(fn(string $operation)=>$operation=='create') ,
        Select::make('supplier_id')
          ->label('المورد')
          ->relationship('Supplier','name')
          ->searchable()
          ->required()
          ->live()
          ->preload()
          ->createOptionForm([
            Section::make('ادخال مورد جديد')
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
            ->createOptionUsing(function (array $data): int {
                $thekey=Supplier::create($data)->getKey();
                $hall=Supplier::find($thekey);

                return $thekey;
            }),

        Select::make('buy_id')
          ->label('رقم الفاتورة')
          ->options(fn (Get $get): Collection => Buy::query()
            ->where('supplier_id', $get('supplier_id'))
            ->selectRaw('\'الرقم \'+str(id)+\' الإجمالي \'+str(tot)+\' بتاريخ \'+convert(varchar,order_date) as name,id')
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
          ->required()
          ->numeric(),
        Select::make('acc_id')
              ->label('المصرف')
              ->relationship('Acc','name')
              ->searchable()
              ->required()
              ->live()
              ->preload()
              ->visible(fn(Get $get): bool =>($get('pay_type')==1 ))

           ,
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
              ,
        TextInput::make('notes')
          ->columnSpan(3)
          ->label('ملاحظات'),
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
        TextColumn::make('id')
          ->searchable()
          ->label('الرقم الألي'),
        TextColumn::make('receipt_date')
          ->searchable()
          ->label('التاريخ'),
        TextColumn::make('supplier.name')
          ->searchable()
          ->label('اسم المورد'),
        TextColumn::make('pay_type')
                    ->description(function (Recsupp $record){
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

        TextColumn::make('notes')
          ->label('ملاحظات'),
      ])
      ->filters([
        SelectFilter::make('supplier_id')
          ->options(Supplier::all()->pluck('name', 'id'))
          ->searchable()
          ->label('مورد معين'),
        Filter::make('is_order')
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
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
              )
              ->when(
                $data['Date2'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
              );
          })
      ])
      ->recordActions([
        EditAction::make()->iconButton()
            ->visible(fn(Recsupp $record): bool =>
                $record->rec_who->value<7
                || Auth::user()->can('الغاء ايصالات موردين')
            ),
        DeleteAction::make()->iconButton()
            ->visible(fn(Recsupp $record): bool =>
            $record->rec_who->value<7
            || Auth::user()->can('الغاء ايصالات موردين')
        )
          ->modalHeading('حذف الإيصال')
          ->after(function (Recsupp $record) {
              if ($record->rec_who->value==3 || $record->rec_who->value==4 || $record->rec_who->value==5 || $record->rec_who->value==6) {
                $sum=Recsupp::where('buy_id',$record->buy_id)->whereIn('rec_who',[3,6])->sum('val');
                $sub=Recsupp::where('buy_id',$record->buy_id)->whereIn('rec_who',[4,5])->sum('val');
              $buy=Buy::find($record->buy_id);
              $buy->pay=$sub-$sum;
              $buy->save();


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
            'index' => ListRecsupps::route('/'),
            'create' => CreateRecsupp::route('/create'),
            'edit' => EditRecsupp::route('/{record}/edit'),
        ];
    }
}
