<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Parloursbooking.com | Vendors Contract</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ URL::asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ URL::asset('admin/css/adminlte.css') }}">
  <style>
    ul{list-style-type:none;}
  </style>
</head>
<body>
<div class="wrapper">
  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <table border="0">
        <tr>
          <th><img src="{{ public_path('images/parlours-booking-logob.png') }}" alt="PB Logo" width="72px" class="brand-image img-circle"></th>
          <th align="left">
            <h2 class="page-header">
              <span class="brand-text font-weight-light">Parlours Booking Payout Receipt</span>
            </h2>
          </th>
        </tr>
      </table>
    </div>
    <!-- info row -->
    <div class="row invoice-info">
        <table border="0" style="width:100%;">
            <tr>
            <th style="width:50%; text-align:left;">            
                From
            </th>
            <th style="width:50%; text-align:right;">
                To
            </th>
            </tr>
            <tr>
            <td>
                <address>
                <strong>Parlours Booking PVT LTD</strong><br>
                No:40, Vivekananda Road, Wellawatta<br>
                Colombo 06, Sri Lanka<br>
                Phone: (+94) 77 225 8864<br>
                Email: info@parloursbooking.com
                </address>
            </td>          
            <td style="text-align:right;">
                <address>
                <strong>{{$payoutHistory->vendors->pbv_business_name}}</strong><br>
                {{$payoutHistory->vendors->pbv_person_name ? $payoutHistory->vendors->pbv_person_name . '<br>' : ''}}
                {{$payoutHistory->vendors->pbv_address}}<br>
                Phone: {{$payoutHistory->vendors->pbv_contactno}}{{$payoutHistory->vendors->pbv_person_contactno ? ' | ' . $payoutHistory->vendors->pbv_person_contactno : ''}}<br>
                Email: {{$payoutHistory->vendors->pbv_email}}
                </address>
            </td>
            </tr>
        </table>
        <br>
        <br>
        <!-- /.col -->
        <div class="row">
            <table border="0" style="width:100%;">
                <tr>
                    <th style="width:50%; text-align:left;">
                        <b>Payment Method: {{ $payoutHistory->pbvph_payment_method }}</b>
                    </th>
                    <th style="width:50%; text-align:right;">
                        <b>Date: {{ $payoutHistory->created_at->format('Y-m-d') }}</b>
                    </th>
                </tr>
            </table>
        </div>
        <!-- /.col -->
    </div>
    <br>
    <br>
    <!-- /.row -->
    <!-- {{ $payoutHistory->payoutItems }} -->
    <!-- Table row -->
    <div class="row">
      <div class="col-12 table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
                <th>B.Ref</th>
                <th>P.T Ref</th>
                <th>Total</th>
                <th>Platform Fee</th>
                <th>Earned</th>
            </tr>
          </thead>
          <tbody>
            @foreach($payoutHistory->payoutItems as $item)
                {{ $item }}
                <tr>
                    <td>{{ $item->booking ? $item->booking->pbb_ref_no : 'N/A' }}</td>
                    <td>{{ $item->payment ? $item->payment->pbpt_transaction_id . ' | ' . $item->payment->pbpt_payment_ref_no : 'N/A' }}</td>
                    <td>{{ $item->payment ? 'Rs. ' . number_format($item->payment->pbpt_final_amount, 2) : 'N/A' }}</td>
                    <td>{{ $item->payment ? 'Rs. ' . number_format($item->payment->pbpt_platform_fee, 2) : 'N/A' }}</td>
                    <td>{{ $item->payment ? 'Rs. ' . number_format($item->payment->pbpt_vendor_amount, 2) : 'N/A' }}</td>
                </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <br>
    <br>
    <!-- /.row -->
    <div class="row">
        <div class="col-6">
            <p><strong>Payment Description:</strong> {{ $payoutHistory->pbvph_description }}</p>
        </div>
        <div class="col-6">
            <p><strong>Total Paid:</strong> {{ 'Rs. ' . number_format($payoutHistory->pbvph_amount, 2) }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-12" style="text-align:center;">
            <p>Thank you for using Parlours Booking! This is a system-generated receipt.</p>
            <p><small>Generated on {{ now()->format('Y-m-d H:i:s') }}</small></p>
        </div>
    </div>
  </section>
  <!-- /.content -->
</div>
<!-- ./wrapper -->
<!-- Page specific script -->
<script>
  window.addEventListener("load", window.print());
</script>
</body>
</html>