<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccResource\Pages;
use App\Filament\Resources\AccResource\RelationManagers;
use App\Models\Acc;
use App\Models\Kazena;
use App\Models\Receipt;
use App\Models\Recsupp;
use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='حسابات مصرفية';
    protected static ?string $navigationGroup='مصارف وخزائن';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال مصارف');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('del')
                    ->hidden(fn(Kazena $record)=>
                        Receipt::where('acc_id',$record->id)->count()>0
                        || Recsupp::where('acc_id',$record->id)->count()>0
                        || !Auth::user()->can('الغاء مصارف'))

                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->action(function (Model $record){
                        if ($record->kyde) foreach ($record->kyde as $rec) $rec->delete();
                        if ($record->account) $record->account->delete();
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
            'index' => Pages\ListAccs::route('/'),
            'create' => Pages\CreateAcc::route('/create'),
            'edit' => Pages\EditAcc::route('/{record}/edit'),
        ];
    }
}
