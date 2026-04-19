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
        
        // Fallback to env if config not set
        if (!$this->appId) {
            $this->appId = env('ONESIGNAL_APP_ID');
        }
        if (!$this->apiKey) {
            $this->apiKey = env('ONESIGNAL_API_KEY');
        }
    }

    /**
     * Send notification to all users
     */
    public function sendToAll($title, $message, $data = [])
    {
        try {
            $payload = [
                'app_id' => $this->appId,
                'included_segments' => ['Subscribed Users'], // Changed from 'All' to 'Subscribed Users'
                'headings' => ['en' => $title],
                'contents' => ['en' => $message],
                'data' => $data,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);
            
            $responseBody = $response->json();
            Log::info('OneSignal SendToAll Response:', ['Response' => $responseBody]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('OneSignal SendToAll Error:', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Send notification to specific user by external_user_id
     * Fixed version with better error handling
     */
    public function sendToUser($userID, $title, $message, $customData = [])
    {
        try {
            // Validate inputs
            if (empty($userID)) {
                Log::warning('OneSignal sendToUser: User ID is empty');
                return $this->errorResponse('User ID is required');
            }

            if (empty($this->appId) || empty($this->apiKey)) {
                Log::error('OneSignal: Missing configuration', [
                    'app_id' => $this->appId ? 'Set' : 'Missing',
                    'api_key' => $this->apiKey ? 'Set' : 'Missing'
                ]);
                return $this->errorResponse('OneSignal configuration missing');
            }

            $payload = [
                'app_id' => $this->appId,
                'include_external_user_ids' => [(string) $userID],
                'headings' => ['en' => $title],
                'contents' => ['en' => $message],
                'channel_for_external_user_ids' => 'push',
            ];

            // Add custom data if provided (changed from 'custom_data' to 'data' - OneSignal uses 'data')
            if (!empty($customData)) {
                $payload['data'] = $customData;
            }

            // Optional: Add additional parameters for better delivery
            $payload['android_visibility'] = 1;
            $payload['priority'] = 10;
            
            Log::info('OneSignal Request Payload:', [
                'user_id' => $userID,
                'title' => $title,
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://onesignal.com/api/v1/notifications', $payload);
            
            $responseBody = $response->json();
            
            // Log detailed response
            Log::info('OneSignal Response:', [
                'user_id' => $userID,
                'status' => $response->status(),
                'success' => $response->successful(),
                'response' => $responseBody
            ]);
            
            // Check for warnings (unsubscribed users)
            if ($response->successful() && isset($responseBody['warnings'])) {
                Log::warning('OneSignal Warning:', [
                    'user_id' => $userID,
                    'warnings' => $responseBody['warnings']
                ]);
                
                // Check if user is unsubscribed
                if (isset($responseBody['warnings']['invalid_external_user_ids'])) {
                    Log::info('User is unsubscribed from notifications', ['user_id' => $userID]);
                }
            }
            
            // Check for errors
            if (!$response->successful() || isset($responseBody['errors'])) {
                $errorMsg = $responseBody['errors'][0] ?? 'Unknown error';
                Log::error('OneSignal Error:', [
                    'user_id' => $userID,
                    'error' => $errorMsg,
                    'full_response' => $responseBody
                ]);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('OneSignal Exception in sendToUser:', [
                'user_id' => $userID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Send notification to multiple users
     */
    public function sendToMultipleUsers($userIDs, $title, $message, $data = [])
    {
        try {
            if (empty($userIDs)) {
                return $this->errorResponse('User IDs array is empty');
            }
            
            // Convert all IDs to strings
            $userIDs = array_map('strval', $userIDs);
            
            $payload = [
                'app_id' => $this->appId,
                'include_external_user_ids' => $userIDs,
                'headings' => ['en' => $title],
                'contents' => ['en' => $message],
                'channel_for_external_user_ids' => 'push',
                'data' => $data,
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);
            
            $responseBody = $response->json();
            
            Log::info('OneSignal Multiple Users Response:', [
                'user_count' => count($userIDs),
                'users' => $userIDs,
                'response' => $responseBody
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('OneSignal sendToMultipleUsers Error:', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Send notification to device by player ID
     */
    public function sendNotification($deviceToken, $title, $message, $data = [])
    {
        try {
            if (empty($deviceToken)) {
                return $this->errorResponse('Device token is required');
            }
            
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
            
            $responseBody = $response->json();
            Log::info('OneSignal Device Notification Response:', ['response' => $responseBody]);
            
            return $responseBody;
            
        } catch (\Exception $e) {
            Log::error('OneSignal sendNotification Error:', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check if user is subscribed to notifications
     */
    public function checkUserSubscription($externalUserId)
    {
        try {
            // Note: OneSignal API doesn't have a direct endpoint to check subscription by external_id
            // Alternative approach: Track in your database or try sending a test notification
            
            $payload = [
                'app_id' => $this->appId,
                'include_external_user_ids' => [(string) $externalUserId],
                'headings' => ['en' => 'Test'],
                'contents' => ['en' => 'Test notification'],
                'channel_for_external_user_ids' => 'push',
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);
            
            $responseBody = $response->json();
            
            // If there's a warning about invalid external user ids, user is not subscribed
            if (isset($responseBody['warnings']['invalid_external_user_ids'])) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error checking user subscription:', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Create error response to maintain consistent return type
     */
    protected function errorResponse($message)
    {
        return response()->json([
            'success' => false,
            'errors' => [$message]
        ], 400);
    }
}