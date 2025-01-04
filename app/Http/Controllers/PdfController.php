<?php

namespace App\Http\Controllers;

use App\Livewire\Traits\PublicTrait;
use App\Models\Buy;
use App\Models\Buy_tran;
use App\Models\Masrofat;
use App\Models\OurCompany;
use App\Models\Receipt;
use App\Models\Recsupp;
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
    public function PdfDaily(Request $request){


        $cus=OurCompany::where('Company',Auth::user()->company)->first();
        if ($request->repDate1 && !$request->repDate2)
            $buy=Buy::where('order_date','>=',$request->repDate1)->get();
        if ($request->repDate2 && !$request->repDate1)
            $buy=Buy::where('order_date','=<',$request->repDate1)->get();
        if ($request->repDate1 && $request->repDate2)
            $buy=Buy::whereBetween('order_date',[$request->repDate1,$request->repDate2])->get();


        if ($request->repDate1 && !$request->repDate2)
            $sell=Sell::where('order_date','>=',$request->repDate1)->get();
        if ($request->repDate2 && !$request->repDate1)
            $sell=Sell::where('order_date','<=',$request->repDate1)->get();
        if ($request->repDate1 && $request->repDate2)
            $sell=Sell::whereBetween('order_date',[$request->repDate1,$request->repDate2])->get();

        if ($request->repDate1 && !$request->repDate2)
            $supp=Recsupp::where('receipt_date','>=',$request->repDate1)->get();
        if ($request->repDate2 && !$request->repDate1)
            $supp=Recsupp::where('receipt_date','<=',$request->repDate1)->get();
        if ($request->repDate1 && $request->repDate2)
            $supp=Recsupp::whereBetween('receipt_date',[$request->repDate1,$request->repDate2])->get();

        if ($request->repDate1 && !$request->repDate2)
            $cust=Receipt::where('receipt_date','>=',$request->repDate1)->get();
        if ($request->repDate2 && !$request->repDate1)
            $cust=Receipt::where('receipt_date','<=',$request->repDate1)->get();
        if ($request->repDate1 && $request->repDate2)
            $cust=Receipt::whereBetween('receipt_date',[$request->repDate1,$request->repDate2])->get();

        $masr=Masrofat::whereBetween('masr_date',[$request->repDate1,$request->repDate2])->get();

        \Spatie\LaravelPdf\Facades\Pdf::view('PrnView.pdf-daily',
            ['BuyTable'=>$buy,'SellTable'=>$sell,'SuppTable'=>$supp,'CustTable'=>$cust,
                'cus'=>$cus,'masr'=>$masr,'RepDate1'=>$request->repDate1,'RepDate2'=>$request->repDate2])
            ->save(Auth::user()->company.'/invoice-2023-04-10.pdf');

        return Response::download(public_path().'/'.Auth::user()->company.'/invoice-2023-04-10.pdf');


    }
}
