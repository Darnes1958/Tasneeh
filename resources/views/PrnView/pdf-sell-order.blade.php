@extends('PrnView.PrnMaster3')

@section('mainrep')

<div >
    <div id="pageborder">
    </div>
     <div class="flex mr-20">
        <label class="text-lg font-extrabold">فاتورة مبيعات رقم :   </label>
        <label class="text-lg font-extrabold text-blue-700 mr-4"> {{$res->id}} </label>
        <br>
        <br>
    </div>
    <div class="flex mr-20">
        <label >بتاريـــــــخ :   </label>
        <label class="text-blue-700 mr-4"> {{$res->order_date}} </label>
        <br>

    </div>
    <div class="flex mr-20">
        <label >اسم الزبون :   </label>
        <label class="text-blue-700 mr-4"> {{$res->Customer->name}} </label>
        <br>

    </div>
    <div class="flex mr-20">
        <label >صدرت من :   </label>
        <label class="text-blue-700 mr-4"> {{$res->Hall->name}} </label>
        <br>

    </div>


  <div class="flex justify-center">
      <br>
      <table style="width: 80% ;">
          <caption>' '</caption>
          <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
          <tr  style="background: #9dc1d3;" >
              <th >البيان</th>
              <th style="width: 14%;"> الكمية</th>
              <th style="width: 20%">السعر</th>
              <th style="width: 20%"> الاجمالي</th>
          </tr>
          </thead>
          <tbody >
          @php $sumbuy=0; @endphp
          @foreach($res->Sell_tran as $key => $item)
              <tr >
                  <td> {{ $item->Product->name }} </td>
                  <td style="text-align: center"> {{ number_format($item->q,2, '.', ',') }} </td>
                  <td> {{ number_format($item->p,2, '.', ',') }} </td>
                  <td> {{ number_format($item->sub_tot,3, '.', ',') }} </td>
              </tr>
              @php $sumbuy+=$item->sub_tot;@endphp
          @endforeach
          <tr class="font-size-12 border-2 border-l-white border-b-white border-r-white" >
              <td class=" border-l-white border-b-white border-r-white"></td>
              <td class=" border-l-white border-b-white border-r-white"></td>
              <td class="text-left p-2 ">اجمالي الفاتورة   </td>
              <td class="bg-gray-200 "> {{number_format($sumbuy, 3, '.', ',')}}  </td>
          </tr>
          <tr class="font-size-12 border-white border-2" >
              <td class=" text-left p-2">المدفوع</td>
              <td class="bg-gray-200">   {{number_format($res->pay, 3, '.', ',')}} </td>
              <td class=" text-left p-2">الخصم   </td>
              <td class="bg-gray-200"> {{number_format($res->ksm, 3, '.', ',')}}  </td>
          </tr>
          <tr class="font-size-12 border-white border-2" >
              <td class=" text-left p-2">الباقي</td>
              <td class="bg-gray-200"> {{number_format($res->baky, 3, '.', ',')}}   </td>
              <td class=" text-left p-2">الصافي   </td>
              <td class="bg-gray-200"> {{number_format($res->total, 3, '.', ',')}}  </td>
          </tr>

          </tbody>
      </table>
  </div>

</div>


@endsection

