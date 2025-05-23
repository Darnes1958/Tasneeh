<?php

namespace App\Filament\Resources;

use App\Enums\PlaceType;
use App\Filament\Resources\HallResource\Pages;
use App\Filament\Resources\HallResource\RelationManagers;
use App\Models\Account;
use App\Models\Hall;
use App\Models\Hall_stock;
use App\Models\Sell;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HallResource extends Resource
{
    protected static ?string $model = Hall::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='ادخال مخازن وصالات منتجات';
    protected static ?string $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=7;

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('الاسم'),
                Forms\Components\Select::make('hall_type')
                    ->label('النوع')
                    ->required()
                    ->options(PlaceType::class)
                    ->default(0)
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم'),
                Tables\Columns\TextColumn::make('hall_type')
                    ->badge()
                    ->label('البيان')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('del')
                 ->icon('heroicon-o-trash')
                 ->color('danger')
                 ->iconButton()
                 ->hidden(fn(Model $record): bool => Hall_stock::where('hall_id',$record->id)
                    ->where('stock','>',0)->exists() || Sell::where('hall_id',$record->id)->exists())
                 ->requiresConfirmation()
                ->action(function (Model $record){

                    $record->delete();
                }),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListHalls::route('/'),
            'create' => Pages\CreateHall::route('/create'),
            'edit' => Pages\EditHall::route('/{record}/edit'),
        ];
    }
}
