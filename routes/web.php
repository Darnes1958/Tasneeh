<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('filament.admin.auth.login'));

});
Route::controller(\App\Http\Controllers\PdfController::class)->group(function (){
    route::get('/pdfbuy/{id}', 'PdfBuy')->name('pdfbuy') ;
    route::get('/pdfsell/{id}', 'PdfSell')->name('pdfsell') ;
    route::get('/pdfdaily/{repDate1?},{repDate2?}', 'PdfDaily')->name('pdfdaily') ;

});
