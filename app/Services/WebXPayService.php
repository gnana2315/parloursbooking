<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebXPayService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $merchantNumber;

    public function __construct()
    {
        $this->baseUrl = config('webxpay.api_url');
        $this->username = config('webxpay.username');
        $this->password = config('webxpay.password');
        $this->merchantNumber = config('webxpay.merchant_number');
    }

    // LOGIN & GET TOKEN
    public function login()
    {
        $response = Http::asForm()->post($this->baseUrl . '/apiLogin', [
            'api_username'       => $this->username,
            'api_password'       => $this->password,
            'merchant_number'    => $this->merchantNumber,
        ]);

        if ($response->successful()) {
            return $response->json()['token'] ?? null;
        }

        return null;
    }

    // GET TRANSACTION BY ORDER REFERENCE
    public function getTransactionByOrderRef($token, $orderRef)
    {
        return Http::withToken($token)
            ->asForm()
            ->post($this->baseUrl . '/getTransactionData', [
                'order_refference_number' => $orderRef,
            ])->json();
    }

    // GET TRANSACTION BY MERCHANT REFERENCE
    public function getTransactionByMerchantRef($token, $merchantRef)
    {
        return Http::withToken($token)
            ->asForm()
            ->post($this->baseUrl . '/getTransactionByMerchantReference', [
                'merchant_reference' => $merchantRef,
            ])->json();
    }
}