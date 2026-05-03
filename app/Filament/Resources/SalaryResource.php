<?php

namespace App\Filament\Resources;

use App\Models\Visibility;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\SalaryResource\Pages\ListSalaries;
use App\Filament\Resources\SalaryResource\Pages\CreateSalary;
use App\Filament\Resources\SalaryResource\Pages\EditSalary;
use App\Filament\Resources\SalaryResource\Pages;
use App\Filament\Resources\SalaryResource\RelationManagers;
use App\Models\Salary;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SalaryResource extends Resource
{

    protected static ?string $pluralModelLabel='ادراج مرتبات';
    protected static string | \UnitEnum | null $navigationGroup='مرتبات';
    protected static ?int $navigationSort=1;

    protected static ?string $model = Salary::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('مرتبات');
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                 ->required()
                 ->label('الاسم'),
                TextInput::make('sal')
                    ->required()
                    ->numeric()
                    ->label('المرتب'),
                Select::make('hall_id')
                    ->label('مكان العمل')
                    ->relationship('Hall', 'name')
                    ->searchable()
                    ->placeholder('قم باختيار مكان العمل .. او اتركه كما هو اذا كان العمل بالادارة')
                    ->live()
                    ->preload(),
                Forms\Components\Checkbox::make('visible')
                 ->default(1)
                 ->visible(fn($operation)=>$operation=='edit')
                 ->label('مرئي'),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(false)

            ->modifyQueryUsing(fn($query)=> Visibility::where('company',Auth::user()->company)->first()->salary==0 ? $query->where('visible',1) : $query->where('id','!=',null))
            ->columns([
              TextColumn::make('name')
                ->label('الاسم')
                ->sortable()
                ->searchable(),
              TextColumn::make('sal')
                  ->label('المرتب')
                  ->sortable()
                  ->searchable(),
              IconColumn::make('status')
                    ->label('الحالة')
                    ->sortable()
                    ->boolean(),
              TextColumn::make('raseed')
                  ->label('الرصيد')
                  ->searchable(),
              IconColumn::make('visible')
                  ->label('مرئي')
                  ->action(function (Model $record){

                      $record->visible=!$record->visible;
                      $record->save();
                  })
                  ->boolean(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

            ])
            ->toolbarActions([
                Action::make('show')
                    ->label(fn()=>Visibility::where('company',Auth::user()->company)->first()->salary==0 ? 'إظهار المخفي' : 'إخفاء')
                    ->color(fn()=>Visibility::where('company',Auth::user()->company)->first()->salary==0 ? 'success' : 'danger')
                    ->action(function (){
                        $res=Visibility::where('company',Auth::user()->company)->first();
                        $res->salary=!Visibility::where('company',Auth::user()->company)->first()->salary;
                        $res->save();
                        // Visibility::where('company',Auth::user()->company)->first()->modify([
                        //     'salary'=>Visibility::where('company',Auth::user()->company)->first()->salary
                        // ]) ;
                    }),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListSalaries::route('/'),
            'create' => CreateSalary::route('/create'),
            'edit' => EditSalary::route('/{record}/edit'),


        ];
    }
}
