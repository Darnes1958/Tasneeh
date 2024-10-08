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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard;

class HallTranResource extends Resource
{
    protected static ?string $model = Hall_tran::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Prod')
                        ->label('المنتج')
                        ->schema([
                            Select::make('product_id')
                                ->label('المنتج')
                                ->relationship('Product', 'name')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->live(),
                        ]),
                    Wizard\Step::make('Hall1')
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
                    Wizard\Step::make('Hall2')
                        ->label('إلــي')
                        ->schema([
                            Select::make('hall_id2')
                                ->label('إلـــــي')
                                ->relationship('Hall1', 'name',
                                    modifyQueryUsing: fn (Builder $query,Forms\Get $get) =>
                                    $query->where('id','!=',$get('hall_id1'))
                                )
                                ->searchable()
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
                    Wizard\Step::make('Quantity')
                       ->label('الكمية')
                       ->schema([
                           Forms\Components\DatePicker::make('tran_date')
                           ->label('التاريخ')
                           ->required()
                           ->default(now()),
                           Forms\Components\TextInput::make('quant')
                               ->label('الكمية')
                               ->live(onBlur: true)
                               ->maxValue(function (Forms\Get $get){
                                   return Hall_stock::where('hall_id', $get('hall_id1'))
                                       ->where('product_id',$get('product_id'))->first()->stock;
                               })

                               ->required()

                       ])
                ])
                ->columnSpan(2),


            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListHallTrans::route('/'),
            'create' => Pages\CreateHallTran::route('/create'),
            'edit' => Pages\EditHallTran::route('/{record}/edit'),
        ];
    }
}
