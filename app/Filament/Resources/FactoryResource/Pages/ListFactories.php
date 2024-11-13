<?php

namespace App\Filament\Resources\FactoryResource\Pages;

use App\Filament\Resources\FactoryResource;
use App\Models\OurCompany;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Spatie\LaravelPdf\Enums\Unit;
use function Symfony\Component\Translation\t;

class ListFactories extends ListRecords
{
    protected static string $resource = FactoryResource::class;
    protected ?string $heading=' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('ادخال تصنيع'),
            Actions\Action::make('prinitem')
                ->label('طباعة')
                ->icon('heroicon-s-printer')
                ->color('success')
                ->action(function (){
                    $filters=$this->table->getFilters();
                    $any=$filters['created_at']->getState();
                    $title='  كشف بالمنتجات  ';
                    if ($any['Date1']) $title=$title.' من تاريخ '.$any['Date1'];
                    if ($any['Date2']) $title=$title.'  حتي تاريخ '.$any['Date2'];

                    $RepDate=date('Y-m-d');
                    $cus=OurCompany::where('Company',Auth::user()->company)->first();

                    \Spatie\LaravelPdf\Facades\Pdf::view('PrnView.pdf-factories',
                        ['res'=>$this->getTableQueryForExport()->get(),
                            'cus'=>$cus,'RepDate'=>$RepDate,'title'=>$title
                        ])
                        ->footerView('PrnView.footer')
                        ->margins(10, 40, 40, 40, Unit::Pixel)
                        ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
                    $file= public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

                    $headers = [
                        'Content-Type' => 'application/pdf',
                    ];
                    return Response::download($file, 'filename.pdf', $headers);
                }),
        ];
    }
}
