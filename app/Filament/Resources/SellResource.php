<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\SellResource\Pages;
use App\Filament\Resources\SellResource\RelationManagers;
use App\Models\Buy;
use App\Models\Hall_stock;
use App\Models\Item;
use App\Models\Item_type;
use App\Models\Place_stock;
use App\Models\Product;
use App\Models\Sell;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SellResource extends Resource
{
    protected static ?string $model = Sell::class;

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
                        Select::make('Customer_id')
                            ->default(1)
                            ->prefix('الزبون')
                            ->hiddenLabel()
                            ->relationship('Customer','name')
                            ->live()
                            ->required()
                            ->columnSpan(4)
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
                            ,

                        Select::make('hall_id')

                            ->disabled(function ($operation){
                                return $operation=='edit';
                            })
                            ->prefix('نقطة اللبيع')
                            ->hiddenLabel()
                            ->relationship('Hall','name')
                            ->live()
                            ->required()
                            ->columnSpan(4)
                            ->createOptionForm([
                                Section::make('ادخال نقطة بيع')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->unique()
                                            ->label('الاسم'),
                                        Radio::make('hall_type')
                                            ->inline()
                                            ->options(PlaceType::class)
                                    ])
                            ])
                            ,


                        TextInput::make('notes')
                            ->live()
                            ->prefix('ملاحظات')
                            ->hiddenLabel()
                            ->columnSpan('full'),
                        TextInput::make('tot')
                            ->label('إجمالي الفاتورة')
                            ->columnSpan(2)
                            ->default(0)
                            ->readOnly(),
                        Hidden::make('user_id')
                            ->default(Auth::id()),
                    ])
                    ->columns(6)
                    ->columnSpan(6),
                Section::make()
                    ->schema([
                        TableRepeater::make('Sell_tran')
                            ->hiddenLabel()
                            ->required()
                            ->relationship()
                            ->headers([
                                Header::make('المنتج')
                                    ->width('50%'),
                                Header::make('الكمية')
                                    ->width('20%'),
                                Header::make('السعر')
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
                                        info($get('../*.product_id'));
                                        info($state);
                                        info($value);
                                        return collect($get('../*.product_id'))
                                            ->reject(fn($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                   ->afterStateUpdated(function ($state,  Forms\Set $set) {
                                       $prod=Product::find($state);
                                       $set('p',$prod->price);
                                       $set('c',$prod->cost);
                                   }),
                                TextInput::make('q')
                                    ->live(onBlur: true)
                                    ->extraInputAttributes(['tabindex' => 1])
                                    ->columnSpan(1)
                                    ->required(),
                                TextInput::make('p')
                                    ->live(onBlur: true)
                                    ->extraInputAttributes(['tabindex' => 2])
                                    ->columnSpan(1)
                                    ->required() ,
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
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data,Get $get,$operation): array {
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
                    ->columnSpan(6),
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
                    ->searchable()
                    ->sortable()
                    ->label('اجمالي الفاتورة'),
                TextColumn::make('pay')
                    ->label('المدفوع'),
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
