@extends('PrnView.PrnMaster3')

@section('mainrep')
  <div  >

    <div style="text-align: center ; margin-bottom: 5px;">
        <label style="font-size: 14pt;margin-right: 12px;" >كشف حساب المشغل : </label>
        <label style="font-size: 10pt;">{{$res->first()->Man->name}}</label>
        <br>
        <label style="font-size: 14pt;margin-right: 12px;" >من تاريخ : </label>
        <label style="font-size: 10pt;">{{$arr['tran_date']}}</label>
    </div>

    <div style="text-align: center ; margin-bottom: 5px;">
        <label style="font-size: 12pt;margin-right: 12px;" >بداية المدة : </label>
        <label style="font-size: 10pt;">{{$arr['balance']}}</label>
        <label style="font-size: 12pt;margin-right: 12px;" >رصيد سابق : </label>
        <label style="font-size: 10pt;">{{$arr['last']}}</label>
        <label style="font-size: 12pt;margin-right: 12px;" >مدين : </label>
        <label style="font-size: 10pt;">{{$arr['mden']}}</label>
        <label style="font-size: 12pt;margin-right: 12px;" >دائن : </label>
        <label style="font-size: 10pt;">{{$arr['daen']}}</label>
        <label style="font-size: 12pt;margin-right: 12px;" >الرصيد </label>
        <label style="font-size: 10pt;">{{$arr['raseed']}}</label>
   <div class="flex justify-center">
       <table style="border-collapse:collapse;width: 96%"  >
           <thead >

           <tr style="background:lightgray ;">
               <th style="width: 36%; font-size: 7pt;">البيان</th>
               <th style="width: 8%;font-size: 7pt;">التاريخ</th>
               <th style="width: 6%;font-size: 7pt;">الرقم الألي</th>
               <th style="width: 6%;font-size: 7pt;">طريقة الدفع</th>
               <th style="width: 6%;font-size: 7pt;">مدين</th>
               <th style="width: 6%;font-size: 7pt;">دائن</th>
               <th style="font-size: 7pt;">ملاحظات</th>
           </tr>
           </thead>
           <tbody style="margin-bottom: 40px; ">

           @foreach($res as $key=>$item)

               <tr style="border:1px solid ;">
                   @if($item->Factory)
                       <td> {{ $item->pay_who->name  }}&nbsp;({{$item->Factory->Product->name}}) </td>
                   @else
                       <td > {{ $item->pay_who->name }} </td>
                   @endif

                   <td style="text-align: center;"> {{ $item->val_date }} </td>
                   <td style="text-align: center;"> {{ $item->id }} </td>
                   <td style="text-align: center;"> {{ $item->pay_type->name }} </td>
                   @if($item->pay_who->value!=0 )
                       <td > {{ $item->val }} </td>
                   @else
                       <td >  </td>
                   @endif
                   @if($item->pay_who->value==0)
                       <td > {{ $item->val }} </td>
                   @else
                       <td >  </td>
                   @endif


                   <td> {{ $item->notes }} </td>
               </tr>

           @endforeach

           <tr  >
               <td>  </td>
               <td>  </td>
               <td>  </td>
               <td style="font-weight: bolder">  الإجمالي</td>
               <td style="color: red"> {{$arr['mden'] }} </td>
               <td style="color: blue"> {{ $arr['daen'] }} </td>
               <td>  </td>
           </tr>


           </tbody>
       </table>
   </div>



  </div>



@endsection
