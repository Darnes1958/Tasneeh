@extends('PrnView.PrnMaster3')

@section('mainrep')

<div  >



    <div class="flex justify-center pb-40">
        <br>
        <table style="width: 96% ;">

      <caption style="font-size: 12pt; margin: 8px;text-align: right;font-weight: bold;font-family: Amiri;">كشف بالأصنـــــــــاف </caption>


    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >
        <th style="width: 14%">رقم الصنف</th>
        <th >الاسم</th>
        <th style="width: 14%"> سعر الشراء</th>
        <th style="width: 14%"> رصيد سابق</th>
        <th style="width: 14%"> الاجمالي</th>
        <th style="width: 14%">الرصيد الكلي</th>
      <th style="width: 14%"> الاجمالي</th>

    </tr>
    </thead>
    <tbody >
    @php $sumbuy=0;$sumbalance=0 @endphp

    @foreach($res as $key => $item)
      <tr style="font-family: Amiri;font-size: 12px">
          <td style="text-align: center;"> {{ $item->id }} </td>
          <td> {{ $item->name }} </td>
          <td> {{ number_format($item->price_buy,3, '.', ',') }} </td>
          <td> {{ number_format($item->balance,2, '.', ',') }} </td>
          <td> {{ number_format($item->price_buy*$item->balance,3, '.', ',') }} </td>
          <td> {{ number_format($item->stock,2, '.', ',') }} </td>
          <td> {{ number_format($item->buy_tot,3, '.', ',') }} </td>
      </tr>

      @php $sumbuy+=$item->buy_tot;$sumbalance+=$item->price_buy*$item->balance@endphp

    @endforeach
    <tr  style="font-weight: bold;font-size: 12px;">

        <td>   </td>
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>
        <td>   </td>

        <td>   </td>
        <td> {{number_format($sumbalance, 3, '.', ',')}}  </td>
        <td>   </td>
        <td> {{number_format($sumbuy, 3, '.', ',')}}  </td>
    </tr>
    </tbody>

  </table>
    </div>

</div>
</div>

@endsection

