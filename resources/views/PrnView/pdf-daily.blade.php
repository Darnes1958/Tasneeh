@extends('PrnView.PrnMaster3')

@section('mainrep')
  <div  >

    <div style="text-align: center">
     @if($RepDate1 && !$RepDate2)
      <label style="font-size: 14pt;margin-right: 12px;" > الحركة اليومية   من تاريخ : </label>
      <label style="font-size: 10pt;">{{$RepDate1}}</label>
     @endif
     @if($RepDate2 && !$RepDate1)
         <label style="font-size: 14pt;margin-right: 12px;" > الحركة اليومية   حتي تاريخ : </label>
         <label style="font-size: 10pt;">{{$RepDate2}}</label>
     @endif
     @if($RepDate1 && $RepDate2)
             <label style="font-size: 14pt;margin-right: 12px;" > الحركة اليومية   من تاريخ : </label>
             <label style="font-size: 10pt;">{{$RepDate1}}</label>
             <label style="font-size: 14pt;margin-right: 12px;" > حتي تاريخ : </label>
             <label style="font-size: 10pt;">{{$RepDate2}}</label>
     @endif

    </div>
<br>
    <label style="font-size: 14pt;margin-right: 12px;" >مشتريات </label>
    <table  width="100%"   >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          <th >اسم المورد</th>
          <th style="width: 12%;">رقم الفاتورة</th>
          <th style="width: 12%;">التاريخ</th>
          <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">المدفوع</th>
          <th style="width: 12%;">الباقي</th>
      </tr>
      </thead>
      <tbody>
      @php $sumtot=0;$sumcash=0;$sumnot_cash=0; @endphp
      @foreach($BuyTable as $key=>$item)
        <tr class="font-size-12">
            <td> {{$item->Supplier->name}}  </td>
            <td>{{$item->id}}</td>
            <td>{{$item->order_date}}</td>
            <td> {{number_format($item->tot, 2, '.', ',')}} </td>
            <td> {{number_format($item->pay, 2, '.', ',')}} </td>
            <td> {{number_format($item->baky, 2, '.', ',')}} </td>
        </tr>
        @php $sumtot+=$item->tot;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td></td>
          <td></td>
          <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          <td> {{number_format($sumcash, 2, '.', ',')}} </td>
          <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
      </tr>


      </tbody>
    </table>

      <br>

    <label style="font-size: 14pt;margin-right: 12px;" >مبيعات  </label>
    <table  width="100%"   >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          <th >اسم الزبون</th>
          <th style="width: 12%;">رقم الفاتورة</th>
          <th style="width: 12%;">التاريخ</th>
          <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">المدفوع</th>
          <th style="width: 12%;">الباقي</th>
      </tr>
      </thead>
      <tbody >
      @php ;$sumtot=0;$sumcash=0;$sumnot_cash=0;

      @endphp
      @foreach($SellTable as $key=>$item)
        <tr class="font-size-12">
            <td> {{$item->Customer->name}}  </td>
            <td>{{$item->id}}</td>
            <td>{{$item->order_date}}</td>
            <td> {{number_format($item->tot, 2, '.', ',')}} </td>
            <td> {{number_format($item->pay, 2, '.', ',')}} </td>
            <td> {{number_format($item->baky, 2, '.', ',')}} </td>
        </tr>
        @php $sumtot+=$item->total;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td></td>
          <td></td>
          <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          <td> {{number_format($sumcash, 2, '.', ',')}} </td>
          <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
      </tr>

      </tbody>
    </table>

      <br>
      @if($SuppTable->count()>0)
    <label style="font-size: 14pt;margin-right: 12px;" >الموردين</label>
      <table   >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 11%;text-align: center;">التاريخ</th>
              <th style="width: 10%;text-align: center;">البيان</th>
              <th >المورد</th>
              <th style="width: 30%;">الحساب المصرفي / الخزينة</th>
              <th style="width: 10%;">المبلغ</th>
          </tr>
      </thead>
      <tbody >
      @php $sumval=0; @endphp
      @foreach($SuppTable as $key=>$item)

        <tr class="font-size-12">
            <td style="text-align: center;">{{$item->receipt_date}}  </td>
            <td style="text-align: center;">{{$item->rec_who->name}}  </td>
            <td > {{$item->Supplier->name}}  </td>


            @if( $item->Acc)
                <td> {{$item->Acc->name}}  </td>
            @else
                <td> {{$item->Kazena->name}}  </td>
            @endif


            <td> {{number_format($item->val, 2, '.', ',')}} </td>

        </tr>
        @php $sumval+=$item->val; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td>   </td>
          <td>   </td>
          <td>   </td>

          <td> {{number_format($sumval, 2, '.', ',')}} </td>

      </tr>

      </tbody>
    </table>
      @endif
<br>

      @if($CustTable->count()>0)
      <label style="font-size: 14pt;margin-right: 12px;" >الزبائن</label>
      <table    >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 11%;text-align: center;">التاريخ</th>
              <th style="width: 10%;text-align: center;">البيان</th>
              <th >الزبون</th>
              <th style="width: 30%;">الحساب المصرفي / الخزينة</th>

              <th style="width: 10%;">المبلغ</th>
          </tr>
          </thead>
          <tbody >
          @php $sumval=0; @endphp
          @foreach($CustTable as $key=>$item)
              <tr class="font-size-12">
                  <td style="text-align: center;">{{$item->receipt_date}}  </td>
                  <td style="text-align: center;">{{$item->rec_who->name}}  </td>
                  <td > {{$item->Customer->name}}  </td>
                  @if( $item->Acc)
                      <td> {{$item->Acc->name}}  </td>
                  @else
                      <td> {{$item->Kazena->name}}  </td>
                  @endif
                  <td> {{number_format($item->val, 2, '.', ',')}} </td>
              </tr>

              @php $sumval+=$item->val; @endphp
          @endforeach
          <tr class="font-size-12 " style="font-weight: bold">
              <td style="font-weight:normal;">الإجمــــــــالي  </td>
              <td>   </td>
              <td>   </td>
              <td>   </td>

              <td> {{number_format($sumval, 2, '.', ',')}} </td>
          </tr>
          </tbody>
      </table>
      @endif
  </div>



@endsection
