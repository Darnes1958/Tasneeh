<?php

namespace App\Filament\Resources;

use App\Enums\PayWho;
use App\Filament\Resources\HandResource\Pages;
use App\Filament\Resources\HandResource\RelationManagers;
use App\Models\Factory;
use App\Models\Hall_stock;
use App\Models\Hand;
use App\Models\Man;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class HandResource extends Resource
{
    protected static ?string $model = Hand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel='مدفوعات المشغلين';

    protected static ?string $navigationGroup = 'ايصالات قبض ودفع';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                 ->schema([
                     Wizard::make([
                         Wizard\Step::make('man')
                             ->label('المشغل')
                             ->schema([
                                 Select::make('man_id')
                                     ->columnSpan(4)
                                     ->required()
                                     ->afterStateUpdated(function ($livewire){
                                         $livewire->dispatch('man-submitted');
                                     })
                                     ->live()
                                     ->hiddenLabel()
                                     ->preload()
                                     ->searchable()
                                     ->options(Man::all()->pluck('name','id'))
                                     ->createOptionForm([
                                         Section::make('ادخال مشغل')
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
                                                     ->columnSpan(2),
                                             ])
                                             ->columns(4)
                                     ])
                                     ->createOptionUsing(function (array $data): int {
                                         return Man::create($data)->getKey();
                                     }),
                             ]),
                         Wizard\Step::make('factory')
                             ->label('المنتج')
                             ->schema([
                                 Select::make('factory_id')
                                     ->columnSpan(4)
                                     ->relationship('Factory','id',
                                         modifyQueryUsing: fn (Builder $query,Forms\Get $get) =>
                                         $query->whereIn('id',Hand::
                                         where('id', $get('man_id'))->pluck('factory_id')))
                                     ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->id} {$record->Product->name} {$record->process_date} {$record->cost}")
                                     ->searchable()
                                     ->afterStateUpdated(function ($state,Forms\Set $set) {
                                         if ($state) {$set('pay_who',0);}
                                     })
                                     ->preload()
                                     ->afterStateUpdated(function ($livewire){
                                         $livewire->dispatch('factory-submitted');
                                     })
                                     ->live()
                                     ->label('المنتج'),
                             ]),
                         Wizard\Step::make('detail')
                             ->label('البيان')
                             ->schema([
                                 Select::make('pay_who')
                                     ->columnSpan(4)
                                     ->label('البيان')
                                     ->options(PayWho::class)
                                     ->disableOptionWhen(function ( $value,Get $get){
                                         if ($get('factory_id')){
                                             return $value!=0;
                                         } else return $value=='0';
                                     }),
                                 DatePicker::make('val_date')
                                     ->columnSpan(2)
                                     ->default(now())
                                     ->label('التاريخ')

                                     ->required(),
                                 TextInput::make('val')
                                     ->columnSpan(2)
                                     ->label('المبلغ')

                                     ->required(),
                                 Textarea::make('notes')
                                     ->columnSpan('full')
                                     ->label('ملاحظات')
                                 ,
                                 Hidden::make('user_id')
                                     ->default(Auth::id())

                             ]),

                     ])
                         ->extraAlpineAttributes([
                             '@man-submitted.window' => "step='factory'",
                             '@factory-submitted.window' => "step='detail'",

                         ])
                         ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            تخزين
                        </x-filament::button>
                    BLADE)))
                         ->columnSpan(2),

                    ])

                 ->columnSpan(2)

            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                null
            )
            ->columns([
                TextColumn::make('Man.name')
                    ->description(function ($record){
                        if ($record->Factory)
                         return $record->Factory->Product->name; else return '';
                    })
                    ->searchable()
                    ->sortable()
                    ->label('الاسم'),
                TextColumn::make('val_date')
                    ->searchable()
                    ->sortable()
                    ->label('التاريخ'),
                TextColumn::make('mden')
                    ->state(function ($record){
                        if ($record->pay_who->value==1 || $record->pay_who->value==2){
                            return $record->val;
                        } else return 0;
                    })
                    ->summarize(Tables\Columns\Summarizers\Summarizer::make()
                        ->label('')
                        ->using(fn (Builder $query): string => $query->whereIn('pay_who',[1,2])->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('مدين'),

                TextColumn::make('daen')
                    ->state(function ($record){
                        if ($record->pay_who->value==0 || $record->pay_who->value==3){
                            return $record->val;
                        } else return 0;
                    })
                ->summarize(Tables\Columns\Summarizers\Summarizer::make()
                    ->label('')
                    ->using(fn (Builder $query): string => $query->whereIn('pay_who',[0,3])->sum('val')))
                    ->searchable()
                    ->sortable()
                    ->label('دائن'),
                TextColumn::make('pay_who')
                    ->sortable()
                    ->label('البيان'),
                TextColumn::make('notes')
                    ->sortable()
                    ->label('ملاحظات'),
            ])
            ->filters([
                SelectFilter::make('man_id')
                    ->options(Man::all()->pluck('name', 'id'))
                    ->searchable()
                    ->label('')
                    ->placeholder('مشغل معين'),
                SelectFilter::make('pay_who')
                    ->options(PayWho::class)
                    ->searchable()
                    ->label('')
                    ->placeholder('بيان المبلغ'),
                Filter::make('anyfilter')
                    ->form([
                        DatePicker::make('date1')
                            ->prefix('من تاريخ')
                            ->hiddenLabel(),
                        DatePicker::make('date2')
                            ->prefix('إلي تاريخ')
                            ->hiddenLabel(),

                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when($data['date1'],
                                fn (Builder $query, $date): Builder => $query->where('val_date','>=',$data['date1']))
                            ->when($data['date2'],
                                fn (Builder $query, $date): Builder => $query->where('val_date','<=',$data['date2']),

                            );
                    })
                    ->columnSpan(2)
                    ->columns(2),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormWidth(MaxWidth::SevenExtraLarge)
            ->filtersFormColumns(6)
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()
                 ->hidden(fn($record) => $record->pay_who->value==0),
                Tables\Actions\DeleteAction::make()
                 ->hidden(fn($record) => $record->pay_who->value==0),
            ])
           ;
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
            'index' => Pages\ListHands::route('/'),
            'create' => Pages\CreateHand::route('/create'),
            'edit' => Pages\EditHand::route('/{record}/edit'),
        ];
    }
}
