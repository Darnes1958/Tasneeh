@extends('PrnView.PrnMaster3')

@section('mainrep')

<div  >

  <div class="flex justify-center">

    <label >كشف بمحتويات المنتج </label>

      <br>

  </div>
    <div class="flex justify-center">
        <label class="text-blue-700"> {{$res->Product->name}} </label>

        <br>
        <br>

    </div>

  <div class="flex justify-center">
  <table style="width: 70% ;float: right;">
      <caption style="font-size: 12pt; margin: 8px;text-align: right;font-weight: bold;font-family: Amiri;">الاصناف</caption>
    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
        <tr  style="background: #9dc1d3;" >
        <th style="width: 14%">رقم الصنف</th>
        <th >الاسم</th>
        <th style="width: 14%"> الكمية</th>
        <th style="width: 14%">سعر الشراء</th>
        <th style="width: 14%"> الاجمالي</th>
    </tr>
    </thead>
    <tbody >
    @php $sumbuy=0; @endphp
    @foreach($res->Tran as $key => $item)
      <tr >
          <td style="text-align: center;"> {{ $item->item_id }} </td>
          <td> {{ $item->Item->name }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->quant,2, '.', ',') }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->price,2, '.', ',') }} </td>
          <td style="font-size: 12px;"> {{ number_format($item->sub_tot,3, '.', ',') }} </td>
      </tr>
      @php $sumbuy+=$item->sub_tot;@endphp
    @endforeach
    <tr class="font-size-12 " style="font-weight: bold">
        <td>   </td>
        <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>
        <td>   </td>

        <td>   </td>
        <td style="font-size: 12px;"> {{number_format($sumbuy, 3, '.', ',')}}  </td>
    </tr>
    </tbody>
  </table>
  </div>

    <br>
  <div class="flex justify-center">
  <table style="width: 70% ;float: right;">
      <caption style="font-size: 12pt; margin: 8px;text-align: right;font-weight: bold;font-family: Amiri;">عمل اليد </caption>
        <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
        <tr  style="background: #9dc1d3;" >
            <th >الاسم</th>
            <th style="width: 14%"> المبلغ</th>
        </tr>
        </thead>
        <tbody >
        @php $sumcost=0; @endphp
        @foreach($res->Hand as $key => $item)
            <tr >
                <td> {{ $item->Man->name }} </td>
                <td style="font-size: 12px;"> {{ number_format($item->val,2, '.', ',') }} </td>
            </tr>
            @php $sumcost+=$item->val;@endphp
        @endforeach
        <tr class="font-size-12 " style="font-weight: bold">

            <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>

            <td style="font-size: 12px;"> {{number_format($sumcost, 3, '.', ',')}}  </td>
        </tr>
        </tbody>
    </table>
  </div>

    <br>
    <br>
    <div class="flex justify-center">
    <table style="width: 70% ;float: right;">
        <tbody >
            <tr class="font-size-12 " style="font-weight: bold;   ">

                <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;background: #9dc1d3; border: 1pt solid  gray;

          height: 30px;text-align: center">احمالي التكلفة  </td>

                <td style="text-align: center;font-size: 12px; border: 1pt solid  gray;"> {{number_format($sumbuy+$sumcost, 3, '.', ',')}}  </td>
            </tr>
            <tr class="font-size-12 " style="font-weight: bold;   ">

                <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;;background: #9dc1d3;border: 1pt solid  gray;

          height: 30px;text-align:center ">سعر البيع  </td>

                <td style="text-align: center;font-size: 12px; border: 1pt solid  gray;"> {{number_format($res->price, 3, '.', ',')}}  </td>
            </tr>
        </tbody>
    </table>
    </div>



</div>


@endsection

