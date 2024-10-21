<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\OurCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Spatie\LaravelPdf\Enums\Unit;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    public function getTitle():  string|Htmlable
    {
        return  new HtmlString('<div class="leading-3 h-4 py-0 text-base text-primary-400 py-0">أصناف</div>');
    }
    public  function convertToArabic($html, int $line_length = 100, bool $hindo = false, $forcertl = false): string
    {
        $Arabic = new \ArPHP\I18N\Arabic();
        $p = $Arabic->arIdentify($html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $Arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), $line_length, $hindo, $forcertl);
            $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        return $html;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('إضافة صنف جديد'),
            Actions\Action::make('prinitem')
             ->label('طباعة')
             ->icon('heroicon-s-printer')
             ->color('success')
             ->action(function (){
                 $RepDate=date('Y-m-d');
                 $cus=OurCompany::where('Company',Auth::user()->company)->first();



                 $reportHtml=$this->convertToArabic($reportHtml);

                 return response()->streamDownload(function () use ($reportHtml) {
                     echo Pdf::loadHtml($reportHtml)->stream();
                 },  'any.pdf');

                 $reportHtml = view('PrnView.pdf-items',
                     ['res'=>$this->getTableQueryForExport()->get(),
                         'cus'=>$cus,'RepDate'=>$RepDate,
                     ])->render();




                 \Spatie\LaravelPdf\Facades\Pdf::view('PrnView.pdf-items',
                     ['res'=>$this->getTableQueryForExport()->get(),
                         'cus'=>$cus,'RepDate'=>$RepDate,
                     ])

                     ->footerView('PrnView.footer')
                     ->margins(10, 10, 40, 10, Unit::Pixel)
                     ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');
                 $file= public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf';

                 $headers = [
                     'Content-Type' => 'application/pdf',
                 ];

                 return Response::download($file, 'filename.pdf', $headers);



             })
        ];
    }
}
