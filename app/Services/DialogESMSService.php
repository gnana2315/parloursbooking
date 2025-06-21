<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class DialogESMSService
{
    public function sendMessage(string $apiKey, array $numberList, string $message, string $sourceAddress): string
    {
        $ch = curl_init();
        $list = implode(",", $numberList);
        $pushNotificationUrl = "https://yourdomain.com/sms-webhook"; // Optional

        $url = "https://e-sms.dialog.lk/api/v1/message-via-url/create/url-campaign?"
             . "esmsqk={$apiKey}&list={$list}&source_address={$sourceAddress}"
             . "&message=" . urlencode($message)
             . "&push_notification_url=" . urlencode($pushNotificationUrl);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = 'Error: ' . curl_error($ch);
        } else {
            $result = match (trim($response)) {
                "1"     => "Success",
                "2001"  => "Error occurred during campaign creation",
                "2002"  => "Bad request",
                "2003"  => "Empty number list",
                "2004"  => "Empty message body",
                "2005"  => "Invalid number list format",
                "2006"  => "Not eligible to send messages via GET requests",
                "2007"  => "Invalid key",
                "2008"  => "Not enough balance or package messages",
                "2009"  => "No valid numbers found (masked numbers)",
                "2010"  => "Not eligible to consume package",
                "2011"  => "Transactional error",
                default => "Unknown response: " . $response,
            };
        }

        curl_close($ch);
        return $result;
    }

    public function checkBalance(string $apiKey): string
    {
        $ch = curl_init();
        $url = "https://e-sms.dialog.lk/api/v1/message-via-url/check/balance?esmsqk={$apiKey}";

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = 'Error: ' . curl_error($ch);
        } else {
            [$status, $balance] = explode('|', $response, 2);
            $result = match (trim($status)) {
                "1"     => "Success - Balance: " . $balance,
                "2001"  => "Error during campaign creation",
                "2002"  => "Bad request",
                "2006"  => "Not eligible (admin access required)",
                "2007"  => "Invalid key",
                default => "Unknown response or error",
            };
        }

        curl_close($ch);
        return $result;
    }
}
