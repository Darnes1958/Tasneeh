<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HallTranResource\Pages;
use App\Filament\Resources\HallTranResource\RelationManagers;
use App\Models\Hall_stock;
use App\Models\Hall_tran;
use App\Models\HallTran;
use App\Models\Place_stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class HallTranResource extends Resource
{
    protected static ?string $model = Hall_tran::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='نقل انتاج من مكان الي اخر';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('prod')
                        ->label('المنتج')
                        ->schema([
                            Select::make('product_id')
                                ->label('المنتج')
                                ->relationship('Product', 'name')
                                ->afterStateUpdated(function ($livewire){
                                    $livewire->dispatch('prod-submitted');
                                })
                                ->searchable()
                                ->required()
                                ->preload()
                                ->live(),
                        ]),
                    Wizard\Step::make('hall1')
                        ->label('مــن')
                        ->schema([
                            Select::make('hall_id1')
                                ->label('مــــن')
                                ->relationship('Hall1', 'name',
                                    modifyQueryUsing: fn (Builder $query,Forms\Get $get) =>
                                    $query->whereIn('id',Hall_stock::
                                       where('product_id', $get('product_id'))
                                       ->where('stock','>',0) ->pluck('hall_id')),)
                                ->searchable()
                                ->afterStateUpdated(function ($livewire){
                                    $livewire->dispatch('hall1-submitted');
                                })
                                ->required()
                                ->preload()
                                ->columnSpan(3)
                                ->live(),
                            Placeholder::make('raseed1')
                                ->label('الرصيد')
                                ->inlineLabel()
                                ->content(function (Forms\Get $get): string {
                                    if ($get('hall_id1') && $get('product_id'))
                                        return Hall_stock::where('hall_id', $get('hall_id1'))
                                            ->where('product_id',$get('product_id'))->first()->stock;
                                    return 0;
                                }),
                        ]),
                    Wizard\Step::make('hall2')
                        ->label('إلــي')
                        ->schema([
                            Select::make('hall_id2')
                                ->label('إلـــــي')
                                ->relationship('Hall1', 'name',
                                    modifyQueryUsing: fn (Builder $query,Forms\Get $get) =>
                                    $query->where('id','!=',$get('hall_id1'))
                                )
                                ->searchable()
                                ->afterStateUpdated(function ($livewire){
                                    $livewire->dispatch('hall2-submitted');
                                })
                                ->required()
                                ->preload()
                                ->columnSpan(3)
                                ->live(),
                            Placeholder::make('raseed2')
                                ->label('الرصيد')
                                ->inlineLabel()
                                ->content(function (Forms\Get $get): string {
                                    if ($get('hall_id2') && $get('product_id'))
                                    {
                                       $stock= Hall_stock::where('hall_id', $get('hall_id2'))
                                            ->where('product_id',$get('product_id'))
                                           ->first();
                                        if ($stock)
                                            return $stock->stock;
                                    }

                                    return 0;
                                }),

                        ]),
                    Wizard\Step::make('quantity')
                       ->label('الكمية')
                       ->schema([
                           Forms\Components\DatePicker::make('tran_date')
                           ->label('التاريخ')
                           ->required()
                           ->default(now()),
                           Forms\Components\TextInput::make('quant')
                               ->label('الكمية')
                               ->live(onBlur: true)
                               ->afterStateUpdated(function (Forms\Get $get,$state,Forms\Set $set){
                                   if ($state> Hall_stock::where('hall_id', $get('hall_id1'))
                                       ->where('product_id',$get('product_id'))->first()->stock){
                                       Notification::make()
                                           ->title('الرصيد لايسمح بهذه الكمية')
                                           ->send();
                                       $set('quant',0);

                                   };
                               })
                               ->required(),
                           Forms\Components\Hidden::make('user_id')->default(auth()->id()),

                       ])
                ])
                    ->extraAlpineAttributes([
                        '@prod-submitted.window' => "step='hall1'",
                        '@hall1-submitted.window' => "step='hall2'",
                        '@hall2-submitted.window' => "step='quantity'",
                    ])
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            تخزين
                        </x-filament::button>
                    BLADE)))
                ->columnSpan(2),


            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tran_date')
                    ->label('التاريخ'),
                TextColumn::make('Product.name')
                    ->label('اسم المنتج'),
                TextColumn::make('Hall1.name')
                 ->label('مــــن'),
                TextColumn::make('Hall2.name')
                    ->label('إلــــي'),
                TextColumn::make('quant')
                    ->label('الكمية'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('del')
                 ->icon('heroicon-o-trash')
                 ->iconButton()
                 ->hidden(function (Model $record){
                  return   Hall_stock::where('product_id',$record->product_id)
                         ->where('hall_id',$record->hall_id2)->first()->stock < $record->quant;
                 })
                ->requiresConfirmation()
                ->modalHeading('الغاء النقل')
                ->action(function (Model $record){
                    $h=Hall_stock::where('hall_id', $record->hall_id2)
                        ->where('product_id',$record->product_id)->first();
                    $h->stock -= $record->quant;
                    $h->save();
                    $h=Hall_stock::where('hall_id', $record->hall_id1)
                        ->where('product_id',$record->product_id)->first();
                    $h->stock += $record->quant;
                    $h->save();
                    $record->delete();

                }
                )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => Hall_stock::where('product_id',$record->product_id)
                        ->where('hall_id',$record->hall_id2)->first()->stock >= $record->quant,
            );
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
            'index' => Pages\ListHallTrans::route('/'),
            'create' => Pages\CreateHallTran::route('/create'),
         //   'edit' => Pages\EditHallTran::route('/{record}/edit'),
        ];
    }
}
