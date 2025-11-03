<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\PlaceResource\Pages\ListPlaces;
use App\Filament\Resources\PlaceResource\Pages\CreatePlace;
use App\Filament\Resources\PlaceResource\Pages\EditPlace;
use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Models\Account;
use App\Models\Factory;
use App\Models\Hall_stock;
use App\Models\Place;
use App\Models\Place_stock;
use App\Models\Sell;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='ادخال مخازن اصناف';
    protected static string | \UnitEnum | null $navigationGroup='مخازن و أصناف';
    protected static ?int $navigationSort=6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('الاسم'),
                Hidden::make('place_type')
                    ->default(0)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                 ->label('الاسم')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('del')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(function (Model $record){

                        $record->delete();
                    })
                    ->hidden(fn(Model $record): bool => Place_stock::where('place_id',$record->id)
                            ->where('stock','>',0)->exists() || Factory::where('place_id',$record->id)->exists()),
            ])
            ->toolbarActions([
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
            'index' => ListPlaces::route('/'),
            'create' => CreatePlace::route('/create'),
            'edit' => EditPlace::route('/{record}/edit'),
        ];
    }
}
