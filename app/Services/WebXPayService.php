<?php

namespace App\Services;

use GuzzleHttp\Client;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class WebXPayService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected Client $client;

    public function __construct()
    {
        $this->baseUrl = config('services.webxpay.base_url'); // e.g., http://tokenize.stagingxpay.info/t/api/
        $this->username = config('services.webxpay.username'); // stagingxpay_user
        $this->password = config('services.webxpay.password'); // LW8drgW5Aqia
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    // Authenticate and return JWT token
    public function auth(): string
    {
        $data = array("username" => "$this->username", "password" => "$this->password");

        $response = $this->client->request(
            'POST',
            "auth",
            [
                'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode($data)
            ]
        );

        try {
            $response = json_decode((string) $response->getBody());

            if (isset($response->token)) {
                return $response->token;
            }

            return null;
        } catch (\Throwable $th) {
            return $response;
        }
    }

    // Get user details (publicKey, secretKey)
    public function getUserDetails(string $jwt): object
    {
        $response = $this->client->request(
            'GET',
            "merchant/user",
            [
                'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => "Bearer $jwt"]
            ]
        );        

        try {
            $response = json_decode((string) $response->getBody());

            if (isset($response->token)) {
                return $response->token;
            }

            return null;
        } catch (\Throwable $th) {
            return $response;
        }
    }

    // Generate RSA encrypted payment string
    public function generatePaymentString(string $booking_ref_no, float $amount, string $publicKey): string
    {
        $publickey = str_replace('\n', "\n", $publicKey);
            
        // Create plaintext: order_id|total_amount
        $plaintext = $booking_ref_no . '|' . number_format($amount, 2, '.', '');

        // Load public key
        $publicKey = PublicKeyLoader::load($publickey)
            ->withPadding(RSA::ENCRYPTION_PKCS1);

        // Encrypt
        $encrypted = $publicKey->encrypt($plaintext);

            
        // Base64 encode for transmission
        $payment = base64_encode($encrypted);

        return $payment;
    }
}