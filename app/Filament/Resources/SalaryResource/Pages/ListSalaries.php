<?php

namespace App\Filament\Resources\SalaryResource\Pages;

use App\Enums\AccRef;
use App\Enums\PayType;
use App\Filament\Resources\SalaryResource;
use App\Livewire\Traits\AccTrait;
use App\Livewire\Traits\PublicTrait;
use App\Models\Acc;
use App\Models\Kazena;
use App\Models\Main;
use App\Models\Salary;
use App\Models\Salarytran;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ListSalaries extends ListRecords
{
  use AccTrait;
  use PublicTrait;
    protected static string $resource = SalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make()
            ->label('إضافة مرتب جديد'),
            Actions\Action::make('إدارج_مرتبات')
                ->color('success')
                ->modalSubmitActionLabel('إدراج')
                ->form([
                    DatePicker::make('month')
                    ->label('عن شهر')
                    ->required()
                      ->native(false)
                    ->displayFormat('Y/m')
                    ->format('Y/m')
                      ->closeOnDateSelection(),
                  DatePicker::make('tran_date')
                    ->required()
                    ->default(now())
                    ->label('التاريخ'),

                ])
                ->action(function (array $data) {
                  if (! Salarytran::where('month',$data['month'])->exists())
                  {
                      $main=Salary::where('status',1)->get();

                      foreach ($main as $item) {
                          $tran=new Salarytran;
                          $tran->salary_id=$item->id;
                          $tran->tran_date=$data['tran_date'];
                          $tran->tran_type='مرتب';
                          $tran->val=$item->sal;
                          $tran->notes='مرتب شهر '.$data['month'];
                          $tran->month=$data['month'];
                          $tran->save();


                      }
                      $this->TarseedTrans();
                      Notification::make()
                          ->title('تم إدراج المرتب بنجاح ')
                          ->icon('heroicon-o-check')
                          ->duration(5000)
                          ->iconColor('success')
                          ->send();

                  }
                  else
                      Notification::make()
                          ->title('سبق إدراج هذا المرتب')
                          ->body('يرجي مراجعة المرتبات المدخلة سابقا')
                          ->icon('heroicon-o-x-mark')
                          ->color('danger')
                          ->duration(10000)
                          ->iconColor('danger')
                          ->send();
                }),
            Actions\Action::make('سحب')
             ->color('success')
              ->icon('heroicon-o-minus-circle')
             ->form([
              Radio::make('pay_type')
               ->options(PayType::class)
               ->live()
               ->default(1)
               ->label('طريقة الدفع'),

              Select::make('salary_id')
              ->label('الاسم')
              ->options(Salary::all()->pluck('name','id'))
              ->searchable()
              ->preload()
               ->required(),
               Select::make('acc_id')
                 ->label('المصرف')
                 ->options(Acc::all()->pluck('name','id'))
                 ->searchable()
                 ->required()
                 ->live()
                 ->preload()
                 ->visible(fn(Get $get): bool =>($get('pay_type')==1 )),
               Select::make('kazena_id')
                 ->label('الخزينة')
                 ->options(Kazena::all()->pluck('name','id'))
                 ->searchable()
                 ->required()
                 ->live()
                 ->preload()
                 ->default(function (){
                   $res=Kazena::where('user_id',Auth::id())->first();
                   if ($res) return $res->id;
                   else return null;
                 })
                 ->visible(fn(Get $get): bool =>($get('pay_type')==0 )),

              DatePicker::make('tran_date')
                ->required()
                ->default(now())
                ->label('التاريخ'),
              TextInput::make('val')
                ->label('المبلغ')
                ->required(),
               TextInput::make('notes')
                 ->label('ملاحظات'),

             ])
             ->action(function (array $data) {
                  $tran=new Salarytran;
                  $tran->salary_id=$data['salary_id'];
                  $tran->tran_date=$data['tran_date'];
                  $tran->tran_type='سحب';
                 $tran->pay_type=$data['pay_type'];
                  if ($data['pay_type']==1) $tran->acc_id=$data['acc_id'];
                  else $tran->kazena_id=$data['kazena_id'];
                  $tran->val=$data['val'];
                  $tran->notes=$data['notes'];
                  $tran->month='0';
                  $tran->save();


                $this->TarseedTrans();
                Notification::make()
                  ->title('تم عملية السحب بنجاح ')
                  ->icon('heroicon-o-check')
                  ->duration(5000)
                  ->iconColor('success')
                  ->send();

            }),
          Actions\Action::make('اضافة')
            ->color('success')
            ->icon('heroicon-o-plus-circle')
            ->form([
              Select::make('salary_id')
                ->label('الاسم')
                ->options(Salary::all()->pluck('name','id'))
                ->searchable()
                ->preload()
                ->required(),
                DatePicker::make('tran_date')
                    ->required()
                    ->default(now())
                    ->label('التاريخ'),
              TextInput::make('val')
                ->label('المبلغ')
                ->required(),
              TextInput::make('notes')
                ->label('ملاحظات'),
            ])
            ->action(function (array $data) {
              $tran=new Salarytran;
              $tran->salary_id=$data['salary_id'];
                $tran->tran_date=$data['tran_date'];
              $tran->tran_type='اضافة';
              $tran->val=$data['val'];
              $tran->notes=$data['notes'];
              $tran->month='0';
              $tran->save();


              $this->TarseedTrans();
              Notification::make()
                ->title('تم عملية الاضاقة بنجاح ')
                ->icon('heroicon-o-check')
                ->duration(5000)
                ->iconColor('success')
                ->send();

            }),
          Actions\Action::make('خصم')
            ->color('danger')
            ->form([


              Select::make('salary_id')
                ->label('الاسم')
                ->options(Salary::all()->pluck('name','id'))
                ->searchable()
                ->preload()
                ->required(),


              TextInput::make('val')
                ->label('المبلغ')
                ->required(),
              TextInput::make('notes')
                ->label('ملاحظات'),
            ])
            ->action(function (array $data) {

              $tran=new Salarytran;
              $tran->salary_id=$data['salary_id'];
              $tran->tran_date=now();
              $tran->tran_type='خصم';
              $tran->val=$data['val'];
              $tran->notes=$data['notes'];
              $tran->month='0';
              $tran->save();


              $this->TarseedTrans();
              Notification::make()
                ->title('تم عملية الخصم بنجاح ')
                ->icon('heroicon-o-check')
                ->duration(5000)
                ->iconColor('success')
                ->send();
            }),
          Actions\Action::make('ايقاف')

            ->color('danger')
            ->form([
              Select::make('id')
                ->label('الاسم')
                ->options(Salary::all()->pluck('name','id'))
                ->searchable()
                ->preload()
                ->required()
                ->live()
              ->afterStateUpdated(function (Set $set,$state){
                $set('status',Salary::find($state)->status);

              }),
              Toggle::make('status')
                ->onColor('success')
                ->offColor('danger')
                ->label('الحالة')
                ->visible(function (Get $get){
                  return $get('id') !=null;
                }),
            ])
            ->action(function (array $data) {
              Salary::find($data['id'])->update(['status'=>$data['status']]);
              Notification::make()
                ->title('تم عملية الإيقاف بنجاح ')
                ->icon('heroicon-o-check')
                ->duration(5000)
                ->iconColor('success')
                ->send();
            }),

            Actions\Action::make('إلغاء_مرتب')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Select::make('month')
                    ->label('عن شهر')
                    ->options(Salarytran::where('month','!=','0')->distinct()->pluck('month', 'month'))
                ])
                ->action(function (array $data) {

                        Salarytran::where('month',$data['month'])->delete();
                        $this->TarseedTrans();

                        Notification::make()
                            ->title('تم الغاء المرتب بنجاح ')
                            ->icon('heroicon-o-check')
                            ->duration(5000)
                            ->iconColor('success')

                            ->send();


                })
        ];
    }
}
