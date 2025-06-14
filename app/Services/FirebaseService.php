<?php
    namespace App\Services;

    use Illuminate\Support\Facades\Http;

    class FirebaseService
    {
        protected $url = 'https://fcm.googleapis.com/v1/projects/parlours-booking/messages:send';
        protected $accessToken;

        public function __construct()
        {
            $this->accessToken = $this->getAccessToken();
        }

        protected function getAccessToken()
        {
            $serviceAccount = json_decode(file_get_contents(storage_path('app/firebase/firebase.json')), true);

            $client = new \Google_Client();
            $client->setAuthConfig($serviceAccount);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            return $client->fetchAccessTokenWithAssertion()['access_token'];
        }

        public function sendNotification($deviceToken, $title, $body)
        {
            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ]
            ];

            return Http::withToken($this->accessToken)
                ->post($this->url, $payload)
                ->json();
        }
    }
?>