<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

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
    public function getUserDetails($jwt)
    {
        $response = Http::withToken($jwt)->get($this->baseUrl . "GetUserDetails");
        dd($response);
        // Check if response is ok and contains data
        if ($response->successful() && isset($response->json()['data'])) {
            return $response->json()['data'];
        }

        // Log or return full response for debugging
        return [
            'success' => false,
            'error'   => 'Invalid response from API',
            'response' => $response->json()
        ];
    }

    // Generate RSA encrypted payment string
    public function generatePaymentString(string $orderId, float $amount, string $publicKey): string
    {
        $plaintext = "{$orderId}|{$amount}";
        $rsa = RSA::loadPublicKey($publicKey);
        $encrypted = $rsa->encrypt($plaintext);
        return base64_encode($encrypted);
    }

    public function PayFromSession3ds(array $payFromCardRequest, string $jwt): ?object
    {
        try {
            $response = $this->client->request(
                'POST',
                "cards/pay/session3ds",
                [
                    'headers' => [
                        'content-type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $jwt",
                    ],
                    'body' => json_encode($payFromCardRequest),
                ]
            );

            return json_decode($response->getBody()->getContents());

        } catch (\Throwable $e) {
            return (object)[
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    

    public function createSession(array $data, string $jwt): object
    {
            $response = $this->client->post('cards/session', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer $jwt",
                ],
                'body' => json_encode($data),
            ]);

            return json_decode((string) $response->getBody());
        }
    }
