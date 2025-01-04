<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KydeResource\Pages;
use App\Filament\Resources\KydeResource\RelationManagers;
use App\Models\Buy;
use App\Models\Kyde;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Doctrine\DBAL\Schema\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

class KydeResource extends Resource
{
    protected static ?string $model = Kyde::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='قيود يومية';
    protected static ?string $navigationGroup='محاسبة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                 ->schema([
                     DatePicker::make('kyde_date')
                         ->label('التاريح')
                         ->default(now())
                         ->columnSpan(2)
                         ->required(),
                     Textarea::make('notes')
                         ->rows(4)
                         ->maxLength(255)
                         ->required()
                         ->columnSpan(2)
                         ->label('البيان'),
                     TextInput::make('totMden')
                      ->disabled()
                      ->default(0)
                      ->label('اجمالي المدين'),
                     TextInput::make('totDaen')
                         ->disabled()
                         ->default(0)
                         ->label('اجمالي الدائن'),
                 ])
                 ->columnSpan(4)
                 ->columns(2),
                Section::make()
                 ->schema([
                     TableRepeater::make('KydeData')
                         ->hiddenLabel()
                         ->required()
                         ->relationship()
                         ->headers([
                             Header::make('رقم الحساب')
                                 ->label(function () {
                                     return new HtmlString('<span class="text-primary-600">رقم الحساب</span>');
                                 })
                                 ->width('50%'),
                             Header::make('مدين')
                                 ->label(function () {
                                     return new HtmlString('<span class="text-primary-600">مدين</span>');
                                 })
                                 ->width('25%'),
                             Header::make('daen')
                                 ->label(function () {
                                     return new HtmlString('<span class="text-primary-600">دائن</span>');
                                 })
                                 ->width('25%'),
                         ])
                         ->schema([
                            Select::make('account_id')
                                ->required()
                                ->preload()
                                ->live()
                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                    return collect($get('../*.account_id'))
                                        ->reject(fn($id) => $id == $state)
                                        ->filter()
                                        ->contains($value);
                                })
                                ->searchable()
                                ->relationship('Account','name',
                                    modifyQueryUsing: fn (Builder $query) => $query->where('is_active',1),),
                            TextInput::make('mden')
                             ->numeric()

                             ->afterStateUpdated(function (Set $set,$state,Get $get){
                                 if ($state==null) {$set('mden',0);return;}
                                 if (filled($state)) $set('daen',0);
                                 $set('../../totMden',collect($get('../../KydeData'))->sum('mden'));
                                 $set('../../totDaen',collect($get('../../KydeData'))->sum('daen'));

                             })
                                ->required()
                             ->live(onBlur: true)
                             ,
                            TextInput::make('daen')
                                ->numeric()
                                ->afterStateUpdated(function (Set $set,$state,Get $get){
                                    if ($state==null) {$set('daen',0);return;}
                                    if (filled($state)) $set('mden',0);
                                    $set('../../totMden',collect($get('../../KydeData'))->sum('mden'));
                                    $set('../../totDaen',collect($get('../../KydeData'))->sum('daen'));

                                })

                                ->required()
                                ->live(onBlur: true)
                                ,
                        ])
                         ->live(onBlur: true)
                         ->grid(2)
                         ->addActionLabel('اضافة')
                         ->addable(function ($state){
                             $flag=true;
                             foreach ($state as $item) {
                                 if (!$item['account_id'] || (!$item['mden'] && !$item['daen']) )
                                 {$flag=false; break;}
                             }
                             return $flag;
                         })
                 ])
                 ->columnSpan(8)

            ])
            ->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->label('الرقم الالي'),
                TextColumn::make('kyde_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('notes')
                    ->searchable()
                    ->sortable()
                    ->label('البيان'),
                TextColumn::make('tot_mden')


                    ->label('مدين'),
                TextColumn::make('tot_daen')

                    ->label('دائن'),


            ])
            ->filters([
                Filter::make('anyfilter')
                    ->form([
                        DatePicker::make('date1')
                            ->prefix('من تاريخ')
                            ->hiddenLabel(),
                        DatePicker::make('date2')
                            ->prefix('إلي تاريخ')
                            ->hiddenLabel(),

                    ])
                    ->query(function ( $query, array $data) {
                        return $query
                            ->when($data['date1'],
                                fn ( $query, $date) => $query->where('kyde_date','>=',$data['date1']))
                            ->when($data['date2'],
                                fn ( $query, $date) => $query->where('kyde_date','<=',$data['date2']),

                            );
                    })
                    ,
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn($record): bool => $record->kydeable_id!=null),
                Tables\Actions\DeleteAction::make()->hidden(fn($record): bool => $record->kydeable_id!=null),
                Action::make('kydeview')
                    ->iconButton()
                    ->iconSize(IconSize::Small)
                    ->icon('heroicon-o-list-bullet')
                    ->color('success')
                    ->modalHeading(false)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('عودة'))
                    ->modalContent(fn (Kyde $record): View => view(
                        'view-kyde-data-widget',
                        ['kyde_id' => $record->id],
                    )),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListKydes::route('/'),
            'create' => Pages\CreateKyde::route('/create'),
            'edit' => Pages\EditKyde::route('/{record}/edit'),
        ];
    }
}
