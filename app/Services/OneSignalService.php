<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $apiKey;
    protected $customerAppId;
    protected $customerApiKey;
    protected $vendorAppId;
    protected $vendorApiKey;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.api_key');
        $this->customerAppId = config('services.onesignal.customer_app_id');
        $this->customerApiKey = config('services.onesignal.customer_api_key');
        $this->vendorAppId = config('services.onesignal.vendor_app_id');
        $this->vendorApiKey = config('services.onesignal.vendor_api_key');
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
            'app_id' => $this->customerAppId,
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
            'Authorization' => 'Basic ' . $this->customerApiKey,
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', $payload);
        Log::info('OneSignal Customer Response:', ['Response' => $response->json()]);
        return $response;
    }

    public function sendToVendor($userID, $title, $message, $customData = [])
    {
        $payload = [
            'app_id' => $this->vendorAppId,
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
            'Authorization' => 'Basic ' . $this->vendorApiKey,
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