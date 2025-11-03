<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Sell;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;


    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='زبائن';
    protected static string | \UnitEnum | null $navigationGroup='زبائن وموردين ومشغلين';
    protected static ?int $navigationSort=1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('الاسم'),
                TextInput::make('address')
                    ->label('العنوان'),
                TextInput::make('mdar')
                    ->label('مدار'),
                TextInput::make('libyana')
                    ->label('لبيانا'),
                TextInput::make('balance')
                    ->label('رصيد بداية المدة')
                    ->default(0)
                    ->numeric()
                    ->required(),
                Hidden::make('user_id')
                    ->default(Auth::id()),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('الرقم الألي'),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('الاسم'),
                TextColumn::make('address')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('blue')
                    ->label('العنوان'),
                TextColumn::make('mdar')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->iconColor('green')
                    ->label('مدار'),
                TextColumn::make('libyana')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->label('لبيانا')
                    ->iconColor('Fuchsia'),
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
            ])
            ->striped()
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                ,
                Action::make('del')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalHeading('حذف زبون')
                    ->modalDescription('هل انت متأكد من الغاء هذا الزبون ؟')
                    ->hidden(fn(Customer $record)=>
                        Sell::where('customer_id',$record->id)->exists()
                        || Receipt::where('customer_id',$record->id)->exists()
                    )
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
