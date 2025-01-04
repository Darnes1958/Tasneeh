@extends('PrnView.PrnMaster3')

@section('mainrep')
  <div  >

    <div style="text-align: center">
         @if($arr['RepDate1'] && !$arr['RepDate2'])
                <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   من تاريخ : </label>
                <label style="font-size: 10pt;">{{$arr['RepDate1']}}</label>
         @endif
         @if($arr['RepDate2'] && !$arr['RepDate1'])
                 <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   حتي تاريخ : </label>
                 <label style="font-size: 10pt;">{{$arr['RepDate2']}}</label>
         @endif
         @if($arr['RepDate1'] && $arr['RepDate2'])
                 <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   من تاريخ : </label>
                 <label style="font-size: 10pt;">{{$arr['RepDate1']}}</label>
                 <label style="font-size: 14pt;margin-right: 12px;" > حتي تاريخ : </label>
                 <label style="font-size: 10pt;">{{$arr['RepDate2']}}</label>
         @endif

    </div>

      <br>

    <label style="font-size: 14pt;margin-right: 12px;" >مشتريات </label>
    <table  width="100%"   >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          <th >نقطة البيع</th>
          <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">المدفوع</th>
          <th style="width: 12%;">الباقي</th>
      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php $sumtot=0;$sumcash=0;$sumnot_cash=0;@endphp
      @foreach($arr['buy'] as $item)
        <tr class="font-size-12">
            <td >{{$item['name']}}  </td>
            <td> {{number_format($item['tot'], 2, '.', ',')}} </td>
            <td> {{number_format($item['pay'], 2, '.', ',')}} </td>
            <td> {{number_format($item['baky'], 2, '.', ',')}} </td>
        </tr>

        @php $sumtot+=$item['tot'];$sumcash+=$item['pay'];$sumnot_cash+=$item['baky']; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          <td> {{number_format($sumcash, 2, '.', ',')}} </td>
          <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
      </tr>

      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


      </tbody>
    </table>

<br>
    <label style="font-size: 14pt;margin-right: 12px;" >مبيعات  </label>
    <table  width="100%"    >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          <th >نقطة البيع</th>
          <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">المدفوع</th>
          <th style="width: 12%;">الباقي</th>
      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php ;$sumtot=0;$sumcash=0;$sumnot_cash=0;

      @endphp
      @foreach($arr['sell'] as $key=>$item)
        <tr class="font-size-12">
            <td >{{$item['name']}}  </td>
            <td> {{number_format($item['tot'], 2, '.', ',')}} </td>
            <td> {{number_format($item['pay'], 2, '.', ',')}} </td>
            <td> {{number_format($item['baky'], 2, '.', ',')}} </td>
        </tr>

        @php $sumtot+=$item['tot'];$sumcash+=$item['pay'];$sumnot_cash+=$item['baky']; @endphp
      @endforeach

      <tr class="font-size-12 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          <td> {{number_format($sumcash, 2, '.', ',')}} </td>
          <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


      </tbody>
    </table>

<br>
    <label style="font-size: 14pt;margin-right: 12px;" >الموردين</label>
      <table style=" width:80%"    >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 20%;">البيان</th>
              <th style="width: 20%;">طريقة الدفع</th>
              <th >الحساب المصرفي / الخزينة</th>
              <th style="width: 14%;">قبض</th>
              <th style="width: 14%;">دفع</th>

          </tr>
      </thead>
      <tbody >
      @php $sumval=0;$sumexp=0; info($arr['supp'])@endphp
      @foreach($arr['supp'] as $key=>$item)

        <tr class="font-size-12">
            <td >{{\App\Enums\RecWho::from($item['rec_who'])->name}}  </td>
            <td> {{\App\Enums\PayType::from($item['pay_type'])->name}}  </td>
            @if($item['accName'])
                <td> {{$item['accName']}}  </td>
            @else
                <td> {{$item['kazName']}}  </td>
            @endif
            <td> {{number_format($item['val'], 2, '.', ',')}} </td>
            <td> {{number_format($item['exp'], 2, '.', ',')}} </td>

        </tr>

        @php $sumval+=$item['val'];$sumexp+=$item['exp']; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td>   </td>
          <td>   </td>
          <td> {{number_format($sumval, 2, '.', ',')}} </td>
          <td> {{number_format($sumexp, 2, '.', ',')}} </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      </tbody>
    </table>
      <br>
      @if($arr['cust'])

      <label style="font-size: 14pt;margin-right: 12px;" >الزبائن</label>
      <table style=" width:80%"    >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 20%;">البيان</th>
              <th style="width: 20%;">طريقة الدفع</th>
              <th >الحساب المصرفي / الخزينة</th>
              <th style="width: 14%;">قبض</th>
              <th style="width: 14%;">دفع</th>
          </tr>
          </thead>
          <tbody >
          @php $sumval=0;$sumexp=0; @endphp
          @foreach($arr['cust'] as $key=>$item)
              <tr class="font-size-12">
                  <td >{{\App\Enums\RecWho::from($item['rec_who'])->name}}  </td>
                  <td> {{\App\Enums\PayType::from($item['pay_type']->name)}}  </td>
                  @if($item['accName'])
                      <td> {{$item['accName']}}  </td>
                  @else
                      <td> {{$item['kazName']}}  </td>
                  @endif
                  <td> {{number_format($item['val'], 2, '.', ',')}} </td>
                  <td> {{number_format($item['exp'], 2, '.', ',')}} </td>
              </tr>

              @php $sumval+=$item['val'];$sumexp+=$item['exp']; @endphp
          @endforeach
          <tr class="font-size-12 " style="font-weight: bold">
              <td style="font-weight:normal;">الإجمــــــــالي  </td>
              <td>   </td>
              <td>   </td>
              <td> {{number_format($sumval, 2, '.', ',')}} </td>
              <td> {{number_format($sumexp, 2, '.', ',')}} </td>
          </tr>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          </tbody>
      </table>
      @endif

      <br>

      @if($arr['masr'])

          <label style="font-size: 14pt;margin-right: 12px;" >المصروفات</label>
          <table style=" width:80%"   >
              <thead style="  margin-top: 8px;">
              <tr style="background:lightgray">
                  <th style="width: 20%;">البيان</th>
                  <th style="width: 20%;">دفعت من</th>
                  <th style="width: 14%;">المبلغ</th>
              </tr>
              </thead>
              <tbody >
              @php $sumval=0; @endphp
              @foreach($arr['masr'] as $key=>$item)
                  <tr class="font-size-12">
                      <td> {{$item['name']}}  </td>
                      <td> {{$item['acc_name']}} </td>
                      <td> {{number_format($item['val'], 2, '.', ',')}} </td>
                  </tr>

                  @php $sumval+=$item['val']; @endphp
              @endforeach
              <tr class="font-size-12 " style="font-weight: bold">
                  <td style="font-weight:normal;">الإجمــــــــالي  </td>
                  <td>   </td>
                  <td> {{number_format($sumval, 2, '.', ',')}} </td>
              </tr>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              </tbody>
          </table>
      @endif
  </div>
@endsection
