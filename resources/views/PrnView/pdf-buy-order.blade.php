@extends('PrnView.PrnMaster3')

@section('mainrep')

<div  >

    <div class="flex justify-center">
        <br>
        <br><br>
        <br>
        <label class="text-xl font-extrabold">فاتورة مشتريات رقم  </label>
        <label class="text-xl font-extrabold text-blue-700 mr-4"> {{$res->id}} </label>
        <br>
        <br>

    </div>
  <div style="margin-right: 40px;">
      <label >المـــورد : </label>
      <label class="text-blue-700"> {{$res->Supplier->name}} </label>
      <br>
      <br>
  </div>

  <table style="width: 80% ;float: right;margin-right: 40px;">

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
    @foreach($res->Buy_tran as $key => $item)
      <tr >
          <td> {{ $item->Item->name }} </td>
          <td style="text-align: center"> {{ number_format($item->quant,2, '.', ',') }} </td>
          <td> {{ number_format($item->price_input,2, '.', ',') }} </td>
          <td> {{ number_format($item->sub_sub,3, '.', ',') }} </td>
      </tr>
      @php $sumbuy+=$item->sub_sub;@endphp
    @endforeach
    <tr class="font-size-12 " style="font-weight: bold">
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>
        <td>   </td>
        <td>   </td>
        <td> {{number_format($sumbuy, 3, '.', ',')}}  </td>
    </tr>
    <tr class="font-size-12 " style="font-weight: bold">
        <td>   </td>
        <td>   </td>
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الخصم  </td>
        <td> {{number_format($res->ksm, 3, '.', ',')}}  </td>
    </tr>
    <tr class="font-size-12 " style="font-weight: bold">
        <td>   </td>
        <td>   </td>
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">المطلوب  </td>
        <td> {{number_format($res->baky, 3, '.', ',')}}  </td>
    </tr>

    </tbody>
  </table>





</div>


@endsection

