<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\ManResource\Pages\ListMen;
use App\Filament\Resources\ManResource\Pages\CreateMan;
use App\Filament\Resources\ManResource\Pages\EditMan;
use App\Filament\Resources\ManResource\Pages;
use App\Filament\Resources\ManResource\RelationManagers;
use App\Models\Man;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManResource extends Resource
{
    protected static ?string $model = Man::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مشغلين';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين ومشغلين';
    protected static ?int $navigationSort=3;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->autocomplete(false)
                    ->required()
                    ->live()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => ' :attribute مخزون مسبقا ',
                    ]),
                TextInput::make('balance')
                    ->label('رصيد بداية المدة')
                    ->default(0)
                    ->numeric()
                    ->required(),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->pluralModelLabel('المشغلين')
            ->columns(components: [
                TextColumn::make('name')
                ->searchable()
                ->label('الاسم'),
                TextColumn::make('balance')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->summarize(Sum::make()->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )->label(''))
                    ->label('رصيد بداية المدة'),
                IconColumn::make('visible')
                 ->label('الظهور')
                    ->action(function ($record){
                       $record->visible = !$record->visible;
                        $record->save();
                    })
                ->boolean(),
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
                    ->hidden(fn($record) => $record->Hand)
                    ->requiresConfirmation()
                    ->action(function (Model $record){

                        $record->delete();
                    }),
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
            'index' => ListMen::route('/'),
            'create' => CreateMan::route('/create'),
            'edit' => EditMan::route('/{record}/edit'),
        ];
    }
}
