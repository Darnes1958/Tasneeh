<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Livewire\Traits\AccTrait;
use App\Models\Buy;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\Recsupp;
use App\Models\Sell;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SupplierResource extends Resource
{
    use AccTrait;
    protected static ?string $model = Supplier::class;
  protected static ?string $navigationLabel='موردين';
    protected static ?string $navigationGroup='زبائن وموردين ومشغلين';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort=2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ادخال موردين');
    }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
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
      ->pluralModelLabel('الموردين')
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
              ->summarize(Tables\Columns\Summarizers\Sum::make()->numeric(
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
      ->actions([
        Tables\Actions\EditAction::make()
          ->iconButton()
          ,
        Tables\Actions\Action::make('del')
          ->icon('heroicon-o-trash')
            ->color('danger')
          ->iconButton()
          ->requiresConfirmation()
          ->modalHeading('حذف مورد')
          ->modalDescription('هل انت متأكد من الغاء هذا المورد ؟')
          ->hidden(fn(Supplier $record)=>
            Buy::where('supplier_id',$record->id)->exists()
            || Recsupp::where('supplier_id',$record->id)->exists()
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
