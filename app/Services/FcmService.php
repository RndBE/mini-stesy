<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class FcmService
{
    private $projectId;

    public function __construct()
    {
        // Try to get project ID from config or extract from credentials file
        $this->projectId = config('services.fcm.project_id', env('FCM_PROJECT_ID'));
    }

    /**
     * Get OAuth2 Bearer Token using google/auth package.
     */
    private function getAccessToken()
    {
        $credentialsPath = storage_path('app/firebase_credentials.json');
        
        if (!file_exists($credentialsPath)) {
            Log::error('FCM credentials file not found at ' . $credentialsPath);
            return null;
        }

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);
            
            if (!$this->projectId) {
                // Parse JSON to get project_id if not set in env
                $json = json_decode(file_get_contents($credentialsPath), true);
                if (isset($json['project_id'])) {
                    $this->projectId = $json['project_id'];
                }
            }
            
            $token = $credentials->fetchAuthToken();
            return $token['access_token'] ?? null;
            
        } catch (\Exception $e) {
            Log::error('FCM getAccessToken error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification to a specific token.
     */
    public function sendNotification(string $fcmToken, string $title, string $body, array $data = [])
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken || !$this->projectId) {
            Log::error('Cannot send FCM: Missing access token or project ID');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $message);

            if (!$response->successful()) {
                Log::error('FCM Send Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM Request Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a silent data-only notification (no notification popup on the device).
     */
    public function sendSilentNotification(string $fcmToken, array $data = [])
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken || !$this->projectId) {
            Log::error('Cannot send FCM: Missing access token or project ID');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $fcmToken,
                'data' => $data,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $message);

            if (!$response->successful()) {
                Log::error('FCM Silent Send Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM Request Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast notification to all registered users (or filtered).
     */
    public function broadcastNotification(string $title, string $body, array $data = [])
    {
        // Get all unique tokens
        $tokens = DB::table('fcm_tokens')->pluck('fcm_token')->unique();
        
        if ($tokens->isEmpty()) {
            Log::warning('FCM broadcast skipped: no registered tokens');
            return;
        }

        $sent = 0;
        $failed = 0;

        // Ideally, this should be chunked if there are thousands of tokens.
        foreach ($tokens as $token) {
            // Note: In a real production system with many users, 
            // you'd use a Job/Queue here to send asynchronously.
            if ($this->sendNotification($token, $title, $body, $data)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        Log::info('FCM broadcast finished', [
            'title' => $title,
            'tokens' => $tokens->count(),
            'sent' => $sent,
            'failed' => $failed,
        ]);
    }
}
