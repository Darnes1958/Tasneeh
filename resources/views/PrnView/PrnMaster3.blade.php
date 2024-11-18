

<!doctype html>

<html lang="ar" dir="rtl">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="https://cdn.tailwindcss.com"></script>
  <style>




      tr {
          line-height: 18px;
      }
      th {
          text-align: center;
          border: 1pt solid  gray;
          font-size: 12px;
          height: 30px;
      }
      caption {
          font-family: DejaVu Sans, sans-serif ;

      }
      thead {

          font-family: DejaVu Sans, sans-serif;
      }

      td {
          font-size: 12px;
          text-align: right;
          border: 1pt solid  lightgray;
      }

      br[style] {
          display:none;
      }


  </style>
</head>
<body  >
<div class="header">
    <div>
        @php
         $customer=\App\Models\OurCompany::where('Company',\Illuminate\Support\Facades\Auth::user()->company)->first();
        @endphp
        <label style="font-size: 20pt; margin-right: 60px;margin-bottom: 20px;margin-top: 20px;padding: 0;" >
            {{ $customer->CompanyName}}
        </label>
    </div>
    <div >
        <label style="font-size: 16pt; margin-right: 60px;margin-bottom: 20px;margin-top: 20px;padding: 0;">
            {{$customer->CompanyNameSuffix}}
        </label>
    </div>

</div>
<br>


    <div >

      @yield('mainrep')


    </div>
</body>
</html>

