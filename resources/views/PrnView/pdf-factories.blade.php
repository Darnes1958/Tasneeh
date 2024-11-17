@extends('PrnView.PrnMaster3')

@section('mainrep')

<div  >


    <div class="flex justify-center">
  <table style="width: 90% ;float: right;">
      <caption style="font-size: 12pt; margin: 8px;text-align: center;font-weight: bold;font-family: Amiri;"> {{$title}} </caption>


    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >
        <th >اسم المنتج</th>
        <th style="width: 8%">الحالة</th>
        <th style="width: 8%">تاريخ التصنيع</th>
        <th style="width: 8%"> تاريخ الانتاج</th>
        <th style="width: 4%">العدد</th>
        <th style="width: 8%"> اجمالي المواد</th>
        <th style="width: 6%"> عمل اليد</th>
        <th style="width: 8%"> اجمالي التكلفة</th>
        <th style="width: 8%"> سعر المنتج</th>
        <th style="width: 8%"> الاجمالي</th>
    </tr>
    </thead>
    <tbody >
    @php $sumtot=0;$sumhandwork=0;$sumprice_tot=0; @endphp

    @foreach($res as $key => $item)
      <tr >
          <td > {{ $item->Product->name }} </td>
          <td style="text-align: center"> {{ $item->status->name }} </td>
          <td style="font-size: 12px;text-align: center"> {{ $item->process_date }} </td>
          <td style="font-size: 12px;text-align: center"> {{ $item->ready_date }} </td>
          <td style="text-align: center"> {{ $item->quantity }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->tot,2, '.', ',') }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->handwork,2, '.', ',') }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->cost,2, '.', ',') }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->price,2, '.', ',') }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->price_tot,2, '.', ',') }} </td>
      </tr>

      @php $sumtot+=$item->tot;$sumhandwork+=$item->handwork;$sumprice_tot+=$item->price_tot;@endphp

    @endforeach
    <tr class="font-size-12 " style="font-weight: bold">
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>
        <td>   </td>
        <td>   </td>
        <td>   </td>
        <td>   </td>
        <td style="font-size: 12px;"> {{number_format($sumtot, 2, '.', ',')}}  </td>
        <td style="font-size: 12px;"> {{number_format($sumhandwork, 2, '.', ',')}}  </td>
        <td>   </td>
        <td>   </td>
        <td style="font-size: 12px;">   {{number_format($sumprice_tot, 2, '.', ',')}}</td>

    </tr>
    </tbody>

  </table>
    </div>
</div>
</div>

@endsection

