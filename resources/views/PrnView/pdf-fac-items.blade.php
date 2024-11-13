@extends('PrnView.PrnMaster3')

@section('mainrep')

<div  >

  <div style="margin-right: 40px;">

    <label >كشف بمحتويات المنتج : </label>
      <br>
    <label class="text-blue-700"> {{$res->Product->name}} </label>
      <br>
      <br>
      <label >السعر : </label>
      <label class="text-blue-700"> {{$res->price}} </label>
      <br>
      <br>

  </div>

  <table style="width: 70% ;float: right;margin-right: 40px;">
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
          <td> {{ number_format($item->quant,2, '.', ',') }} </td>
          <td> {{ number_format($item->price,2, '.', ',') }} </td>
          <td> {{ number_format($item->sub_tot,3, '.', ',') }} </td>
      </tr>
      @php $sumbuy+=$item->sub_tot;@endphp
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

    <br>

  <table style="width: 70% ;float: right;margin-right: 40px;">
      <caption style="font-size: 12pt; margin: 8px;text-align: right;font-weight: bold;font-family: Amiri;">عمل اليد </caption>
        <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
        <tr  style="background: #9dc1d3;" >
            <th >الاسم</th>
            <th style="width: 14%"> المبلغ</th>
        </tr>
        </thead>
        <tbody >
        @php $sumbuy=0; @endphp
        @foreach($res->Hand as $key => $item)
            <tr >
                <td> {{ $item->Man->name }} </td>
                <td> {{ number_format($item->val,2, '.', ',') }} </td>
            </tr>
            @php $sumbuy+=$item->val;@endphp
        @endforeach
        <tr class="font-size-12 " style="font-weight: bold">

            <td style="font-family: DejaVu Sans, sans-serif;font-weight:bold;">الإجمــــــــالي  </td>

            <td> {{number_format($sumbuy, 3, '.', ',')}}  </td>
        </tr>
        </tbody>
    </table>

</div>


@endsection

