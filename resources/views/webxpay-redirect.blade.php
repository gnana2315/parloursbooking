<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to WebXPay</title>
    <style>
        body { font-family: Arial; text-align: center; padding-top: 80px; }
        .loader { width: 150px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{transform:scale(1);}50%{transform:scale(1.1);} }
    </style>
</head>
<body>
    <h2>Redirecting to WebXPay...</h2>
    <img src="{{ asset('images/loading.gif') }}" alt="Loading..." class="loader">

    <form id="webxpayForm" method="POST" action="https://stagingxpay.info/index.php?route=checkout/billing">
        <input type="hidden" name="payment" value="{{ $payment }}">
        <input type="hidden" name="secret_key" value="{{ $secretKey }}">

        <input type="hidden" name="first_name" value="{{ $customer['first_name'] }}">
        <input type="hidden" name="last_name" value="{{ $customer['last_name'] }}">
        <input type="hidden" name="email" value="{{ $customer['email'] }}">
        <input type="hidden" name="contact_number" value="{{ $customer['contact'] }}">
        <input type="hidden" name="address_line_one" value="{{ $customer['address1'] }}">
        <input type="hidden" name="city" value="{{ $customer['city'] }}">
        <input type="hidden" name="country" value="{{ $customer['country'] }}">
        <input type="hidden" name="process_currency" value="{{ $processCurrency }}">
    </form>

    <script>
        document.getElementById("webxpayForm").submit();
    </script>
</body>
</html>
