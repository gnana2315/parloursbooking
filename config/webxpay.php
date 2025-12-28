<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebXPay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for WebXPay payment gateway integration
    |
    */

    'public_key' => env('WEBXPAY_PUBLIC_KEY', ''),
    'secret_key' => env('WEBXPAY_SECRET_KEY', ''),
    'checkout_url' => env('WEBXPAY_CHECKOUT_URL', ''),
    'enc_method' => env('WEBXPAY_ENC_METHOD', ''),
    'currency' => env('WEBXPAY_CURRENCY', ''),
    'cms' => env('WEBXPAY_CMS', ''),
];
