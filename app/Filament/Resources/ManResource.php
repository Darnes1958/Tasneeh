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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManResource extends Resource
{
    protected static ?string $model = Man::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مشغلين';
    protected static ?string $navigationGroup='زبائن وموردين';


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
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->label('الاسم')
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
                        if ($record->account) $record->account->delete();
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
