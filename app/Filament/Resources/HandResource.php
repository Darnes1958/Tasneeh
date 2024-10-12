<?php

namespace App\Filament\Resources;

use App\Enums\PayType;
use App\Enums\PayWho;
use App\Filament\Resources\HandResource\Pages;
use App\Filament\Resources\HandResource\RelationManagers;
use App\Models\Acc;
use App\Models\Factory;
use App\Models\Hall_stock;
use App\Models\Hand;
use App\Models\Kazena;
use App\Models\Man;
use App\Models\Receipt;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;

class HandResource extends Resource
{
    protected static ?string $model = Hand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مدفوعات المشغلين';

    protected static ?string $navigationGroup = 'ايصالات قبض ودفع';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                 ->schema([
                     Radio::make('pay_type')
                         ->columnSpan('full')
                         ->options(PayType::class)
                         ->dehydrated(false)
                         ->inline()
                         ->inlineLabel(false)
                         ->live()
                         ->default(0)
                         ->label('طريقة الدفع'),
                     Select::make('man_id')
                         ->label('المشغل')
                         ->columnSpan(2)
                         ->required()
                         ->live()

                         ->preload()
                         ->searchable()
                         ->options(Man::all()->pluck('name','id'))
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
                         ])
                         ->createOptionUsing(function (array $data): int {
                             return Man::create($data)->getKey();
                         }),
                     Select::make('pay_who')
                         ->required()
                         ->columnSpan(2)
                         ->label('البيان')
                         ->options(PayWho::class)
                         ->disableOptionWhen(function ( $value,Get $get){
                             if ($get('factory_id')){
                                 return $value!=0;
                             } else return $value=='0';
                         }),
                     Select::make('acc_id')
                         ->label('المصرف')
                         ->columnSpan(2)
                         ->relationship('Acc','name')
                         ->searchable()
                         ->required()
                         ->live()
                         ->preload()
                         ->visible(fn(Get $get): bool =>($get('pay_type')==1 ))
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
                         ->columnSpan(2)
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
                         ->visible(fn(Get $get): bool =>($get('pay_type')==0 ))
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
                                     Forms\Components\Select::make('user_id')
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
                                     Forms\Components\Select::make('user_id')
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
                     DatePicker::make('val_date')

                         ->default(now())
                         ->label('التاريخ')
                         ->required(),
                     TextInput::make('val')

                         ->label('المبلغ')
                         ->required(),
                     Textarea::make('notes')
                         ->columnSpan('full')
                         ->label('ملاحظات')
                     ,
                     Hidden::make('user_id')
                         ->default(Auth::id())
                 ])
                 ->columns(4)

            ])
            ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                null
            )
            ->columns([
                TextColumn::make('Man.name')
                    ->searchable()
                    ->sortable()
                    ->label('الاسم'),
                TextColumn::make('val_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('pay_type')
                    ->state(function(Model $record) {
                        if ($record->kazena_id) return 'نقدا';
                        if ($record->acc_id) return 'مصرفي';
                    } )
                    ->color(function(Model $record) {
                        if ($record->kazena_id) return 'success';
                        if ($record->acc_id) return 'primary';
                    })
                    ->badge()
                    ->weight(FontWeight::ExtraBold)
                    ->description(function ($record){
                        $name=null;
                        if ($record->acc_id) {$name=Acc::find($record->acc_id)->name;}
                        if ($record->kazena_id) {$name=Kazena::find($record->kazena_id)->name;}
                        return $name;
                    })
                    ->label('طريقة الدفع'),
                TextColumn::make('mden')
                    ->state(function ($record){
                        if ($record->pay_who->value==1 || $record->pay_who->value==2){
                            return $record->val;
                        } else return 0;
                    })
                    ->summarize(Tables\Columns\Summarizers\Summarizer::make()
                        ->label('')
                        ->using(fn (Builder $query): string => $query->whereIn('pay_who',[1,2])->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('مدين'),

                TextColumn::make('daen')
                    ->state(function ($record){
                        if ($record->pay_who->value==0 || $record->pay_who->value==3){
                            return $record->val;
                        } else return 0;
                    })
                ->summarize(Tables\Columns\Summarizers\Summarizer::make()
                    ->label('')
                    ->using(fn (Builder $query): string => $query->whereIn('pay_who',[0,3])->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('دائن'),
                TextColumn::make('pay_who')
                    ->sortable()
                    ->description(function ($record){
                        if ($record->Factory)
                            return $record->Factory->Product->name; else return '';
                    })
                    ->label('البيان'),
                TextColumn::make('notes')
                    ->sortable()
                    ->label('ملاحظات'),
            ])
            ->filters([
                SelectFilter::make('man_id')
                    ->options(Man::all()->pluck('name', 'id'))
                    ->searchable()
                    ->label('')
                    ->placeholder('مشغل معين'),
                SelectFilter::make('pay_who')
                    ->options(PayWho::class)
                    ->searchable()
                    ->label('')
                    ->placeholder('بيان المبلغ'),
                Filter::make('anyfilter')
                    ->form([
                        DatePicker::make('date1')
                            ->prefix('من تاريخ')
                            ->hiddenLabel(),
                        DatePicker::make('date2')
                            ->prefix('إلي تاريخ')
                            ->hiddenLabel(),

                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when($data['date1'],
                                fn (Builder $query, $date): Builder => $query->where('val_date','>=',$data['date1']))
                            ->when($data['date2'],
                                fn (Builder $query, $date): Builder => $query->where('val_date','<=',$data['date2']),

                            );
                    })
                    ->columnSpan(2)
                    ->columns(2),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormWidth(MaxWidth::SevenExtraLarge)
            ->filtersFormColumns(6)
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()
                 ->hidden(fn($record) => $record->pay_who->value==0),
                Tables\Actions\DeleteAction::make()
                 ->hidden(fn($record) => $record->pay_who->value==0),
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
            'index' => Pages\ListHands::route('/'),
            'create' => Pages\CreateHand::route('/create'),
            'edit' => Pages\EditHand::route('/{record}/edit'),
        ];
    }
}
