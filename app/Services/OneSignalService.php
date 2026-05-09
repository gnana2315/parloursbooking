<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $apiKey;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.api_key');
    }

    public function sendToAll($title, $message)
    {
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,
            'included_segments' => ['All'],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ]);
    }

    public function sendToCustomer($userID, $title, $message, $customData = [])
    {
        $payload = [
            'app_id' => env('ONESIGNAL_CUSTOMER_APP_ID'),
            'include_external_user_ids' => [(string) $userID], // ⚡ use external ID here
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'channel_for_external_user_ids' => 'push', // ensures it targets push notifications
        ];
        Log::info('OneSignal Customer Payload:', ['Payload' => $payload]);

        // Add custom data if provided
        if (!empty($customData)) {
            $payload['custom_data'] = $customData;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ONESIGNAL_CUSTOMER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', $payload);
        Log::info('OneSignal Customer Response:', ['Response' => $response->json()]);
        return $response;
    }

    public function sendToVendor($userID, $title, $message, $customData = [])
    {
        $payload = [
            'app_id' => env('ONESIGNAL_VENDOR_APP_ID'),
            'include_external_user_ids' => [(string) $userID], // ⚡ use external ID here
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'channel_for_external_user_ids' => 'push', // ensures it targets push notifications
        ];
        Log::info('OneSignal Vendor Payload:', ['Payload' => $payload]);

        // Add custom data if provided
        if (!empty($customData)) {
            $payload['custom_data'] = $customData;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ONESIGNAL_VENDOR_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', $payload);
        Log::info('OneSignal Vendor Response:', ['Response' => $response->json()]);
        return $response;
    }
    
    public function sendNotification($deviceToken, $title, $message, $data = [])
    {
        $payload = [
            'app_id' => $this->appId,
            'include_player_ids' => [$deviceToken],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', $payload);

        return $response->json();
    }
}
?>