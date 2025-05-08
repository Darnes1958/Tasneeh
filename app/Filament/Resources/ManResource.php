<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManResource\Pages;
use App\Filament\Resources\ManResource\RelationManagers;
use App\Models\Man;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مشغلين';
    protected static ?string $navigationGroup='زبائن وموردين ومشغلين';
    protected static ?int $navigationSort=3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )->label(''))
                    ->label('رصيد بداية المدة'),
                Tables\Columns\IconColumn::make('visible')
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('del')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->hidden(fn($record) => $record->Hand)
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
            'index' => Pages\ListMen::route('/'),
            'create' => Pages\CreateMan::route('/create'),
            'edit' => Pages\EditMan::route('/{record}/edit'),
        ];
    }
}
