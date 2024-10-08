<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryResource\Pages;
use App\Filament\Resources\FactoryResource\RelationManagers;
use App\Models\Buy;
use App\Models\Factory;
use App\Models\Item;
use App\Models\Item_type;
use App\Models\Man;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Unit;
use Awcodes\TableRepeater\Header;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                    Select::make('product_id')
                        ->relationship('Product', 'name')
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
                       TableRepeater::make('TranWidget')
                           ->hiddenLabel()
                           ->required()
                           ->relationship()
                           ->addActionLabel('اضافة صنف')
                           ->headers([
                               Header::make('رقم الصنف')
                                   ->width('50%'),
                               Header::make('الكمية')
                                   ->width('25%'),
                               Header::make('السعر')
                                   ->width('25%'),
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
                                   ->columnSpan(1)
                                   ->required(),
                               TextInput::make('price')
                                   ->readOnly()
                                   ->columnSpan(1)
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
                          ->required()
                          ->relationship()
                          ->addActionLabel('اضافة مشغل')
                          ->headers([
                              Header::make('الاسم')
                                  ->width('75%'),
                              Header::make('المبلغ')
                                  ->width('25%'),
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
                                  ->searchable()
                                  ->options(Man::all()->pluck('name','id'))
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
                TextColumn::make('id')
                 ->searchable()
                 ->sortable()
                 ->label('الرقم الألي'),
                TextColumn::make('Product.name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم المنتج'),
                TextColumn::make('process_date')
                    ->searchable()
                    ->sortable()
                    ->label('تاريخ بدء التصنيع'),
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
                    ->visible(function (Model $record){return $record->status==='manufacturing';})
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
