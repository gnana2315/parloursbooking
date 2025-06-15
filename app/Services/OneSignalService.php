<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

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

    public function sendToUser($deviceToken, $title, $message)
    {
        return Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,
            'include_player_ids' => [$deviceToken],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
        ]);
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