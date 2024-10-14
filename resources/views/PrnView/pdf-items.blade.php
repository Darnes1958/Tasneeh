@extends('PrnView.PrnMaster2')

@section('mainrep')

<div  >

  <div >




  </div>


  <table style="width: 60% ;float: right;">
      <caption style="font-size: 12pt; margin: 8px;text-align: right">كشف بالأصناف </caption>


    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >


      <th style="width: 12%"> رصيد سابق</th>
      <th style="width: 12%">الرصيد</th>
      <th >الاسم</th>
      <th style="width: 12%">رقم الصنف</th>
    </tr>
    </thead>
    <tbody style="margin-bottom: 40px; ">

    @foreach($res as $key => $item)
      <tr >


        <td> {{ $item->balance }} </td>
        <td style="text-align: center;"> {{ $item->stock }} </td>
        <td> {{ $item->name }} </td>

        <td style="text-align: center;"> {{ $item->id }} </td>
      </tr>
      <div id="footer" style="height: 50px; width: 96%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
        <label class="page"></label>
        <label> صفحة رقم </label>
      </div>

    @endforeach

    </tbody>
  </table>
</div>
</div>

@endsection

