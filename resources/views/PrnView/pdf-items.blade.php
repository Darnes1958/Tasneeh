@extends('PrnView.PrnMaster3')

@section('mainrep')

<div  >

  <div >




  </div>


  <table style="width: 70% ;float: right;">
      <caption style="font-size: 12pt; margin: 8px;text-align: right;font-weight: bold;font-family: Amiri;">كشف بالأصنـــــــــاف </caption>


    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >
        <th style="width: 14%">رقم الصنف</th>
        <th >الاسم</th>
        <th style="width: 14%"> رصيد سابق</th>
        <th style="width: 14%">الرصيد الكلي</th>
      <th style="width: 14%"> الاجمالي</th>

    </tr>
    </thead>
    <tbody >
    @php $sumbuy=0; @endphp

    @foreach($res as $key => $item)
      <tr >
          <td style="text-align: center;"> {{ $item->id }} </td>
          <td> {{ $item->name }} </td>
          <td> {{ number_format($item->balance,2, '.', ',') }} </td>
          <td> {{ number_format($item->stock,2, '.', ',') }} </td>
          <td> {{ number_format($item->buy_tot,3, '.', ',') }} </td>
      </tr>

      @php $sumbuy+=$item->buy_tot;@endphp

    @endforeach
    <tr class="font-size-12 " style="font-weight: bold">

        <td>   </td>
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>
        <td>   </td>

        <td>   </td>
        <td> {{number_format($sumbuy, 3, '.', ',')}}  </td>
    </tr>
    </tbody>

  </table>

</div>
</div>

@endsection

