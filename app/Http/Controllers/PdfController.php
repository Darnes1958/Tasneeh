<?php

namespace App\Http\Controllers;

use App\Livewire\Traits\PublicTrait;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\OurCompany;
use App\Models\Sell;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class PdfController extends Controller
{
    use PublicTrait;
    public function PdfBuy($id){
        return Response::download(self::ret_spatie(Buy::find($id),
            'PrnView.pdf-buy-order',
            ), 'filename.pdf', self::ret_spatie_header());
    }
    public function PdfSell($id){
        return Response::download(self::ret_spatie(Sell::find($id),
            'PrnView.pdf-sell-order',
        ), 'filename.pdf', self::ret_spatie_header());
    }
}
