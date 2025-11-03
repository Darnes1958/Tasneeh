<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\AccResource\Pages\ListAccs;
use App\Filament\Resources\AccResource\Pages\CreateAcc;
use App\Filament\Resources\AccResource\Pages\EditAcc;
use App\Filament\Resources\AccResource\Pages;
use App\Filament\Resources\AccResource\RelationManagers;
use App\Models\Acc;
use App\Models\Kazena;
use App\Models\Receipt;
use App\Models\Recsupp;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use http\Client\Curl\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;

class AccResource extends Resource
{
    protected static ?string $model = Acc::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='حسابات مصرفية';
    protected static string | \UnitEnum | null $navigationGroup='مصارف وخزائن';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصارف');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TextInput::make('balance')
                 ->label('رصيد بداية المدة')
                 ->numeric()
                 ->required()
                 ,

            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              TextColumn::make('id')
                ->label('الرقم الألي'),
              TextColumn::make('name')
                    ->label('اسم المصرف'),
              TextColumn::make('balance')
                    ->label('بداية المدة'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('del')
                    ->hidden(fn(Kazena $record)=>
                        Receipt::where('acc_id',$record->id)->count()>0
                        || Recsupp::where('acc_id',$record->id)->count()>0
                        || !Auth::user()->can('الغاء مصارف'))

                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(function (Model $record){

                        $record->delete();
                    }),

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
            'index' => ListAccs::route('/'),
            'create' => CreateAcc::route('/create'),
            'edit' => EditAcc::route('/{record}/edit'),
        ];
    }
}
