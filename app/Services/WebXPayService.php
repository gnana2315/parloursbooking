<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use GuzzleHttp\Client;

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
        $data = array("username" => "$username", "password" => "$password");

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
        $res = $this->client->get('user-details', [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
            ],
        ]);
        return json_decode($res->getBody()->getContents());
    }

    // Generate RSA encrypted payment string
    public function generatePaymentString(string $orderId, float $amount, string $publicKey): string
    {
        $plaintext = "{$orderId}|{$amount}";
        $rsa = RSA::loadPublicKey($publicKey);
        $encrypted = $rsa->encrypt($plaintext);
        return base64_encode($encrypted);
    }
}
