<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Firebase Cloud Messaging Service
 * 
 * Handles sending push notifications via FCM
 * 
 * Setup Requirements:
 * 1. Get Firebase service account JSON from Firebase Console
 * 2. Place it in storage/app/firebase/service-account.json
 * 3. Or set FIREBASE_CREDENTIALS in .env with JSON content
 */
class FirebaseService
{
    private $credentials;
    private $projectId;
    private $fcmEndpoint = 'https://fcm.googleapis.com/v1/projects/{project-id}/messages:send';

    public function __construct()
    {
        $this->loadCredentials();
    }

    /**
     * Load Firebase credentials from file or env
     */
    private function loadCredentials()
    {
        try {
            // Try to load from file first
            $credentialsPath = storage_path('app/firebase/service-account.json');
            
            if (file_exists($credentialsPath)) {
                $credentialsJson = file_get_contents($credentialsPath);
                $this->credentials = json_decode($credentialsJson, true);
            } else {
                // Fallback to environment variable
                $credentialsJson = env('FIREBASE_CREDENTIALS');
                if ($credentialsJson) {
                    $this->credentials = json_decode($credentialsJson, true);
                }
            }

            if ($this->credentials && isset($this->credentials['project_id'])) {
                $this->projectId = $this->credentials['project_id'];
                $this->fcmEndpoint = str_replace('{project-id}', $this->projectId, $this->fcmEndpoint);
            } else {
                Log::warning('Firebase credentials not configured properly');
            }
        } catch (Exception $e) {
            Log::error('Failed to load Firebase credentials: ' . $e->getMessage());
        }
    }

    /**
     * Get OAuth 2.0 access token for FCM
     */
    private function getAccessToken(): ?string
    {
        try {
            if (!$this->credentials) {
                throw new Exception('Firebase credentials not loaded');
            }

            // Create JWT
            $now = time();
            $payload = [
                'iss' => $this->credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ];

            // Sign JWT with private key
            $privateKey = $this->credentials['private_key'];
            $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
            $payload = json_encode($payload);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode($payload);

            $signature = '';
            openssl_sign(
                $base64UrlHeader . "." . $base64UrlPayload,
                $signature,
                $privateKey,
                OPENSSL_ALGO_SHA256
            );

            $base64UrlSignature = $this->base64UrlEncode($signature);
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('Failed to get FCM access token: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error('FCM token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send notification to a single device
     *
     * @param string $deviceToken FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param array $options Additional FCM options (android, apns, webpush)
     * @return bool Success status
     */
    public function sendToDevice(
        string $deviceToken,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ): bool {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('Cannot send FCM: No access token');
                return false;
            }

            $message = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                    'android' => $options['android'] ?? [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => $options['apns'] ?? [
                        'headers' => [
                            'apns-priority' => '10',
                        ],
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'content-available' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->fcmEndpoint, $message);

            if ($response->successful()) {
                Log::info('FCM sent successfully to device: ' . substr($deviceToken, 0, 20) . '...');
                return true;
            }

            Log::error('FCM send failed: ' . $response->body());
            return false;

        } catch (Exception $e) {
            Log::error('FCM send exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple devices
     *
     * @param array $deviceTokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Results with success/failure counts
     */
    public function sendToMultipleDevices(
        array $deviceTokens,
        string $title,
        string $body,
        array $data = []
    ): array {
        $results = [
            'success' => 0,
            'failure' => 0,
            'total' => count($deviceTokens),
        ];

        foreach ($deviceTokens as $token) {
            $sent = $this->sendToDevice($token, $title, $body, $data);
            if ($sent) {
                $results['success']++;
            } else {
                $results['failure']++;
            }
        }

        return $results;
    }

    /**
     * Send notification to all active tokens of a user
     *
     * @param string $userId User ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Results
     */
    public function sendToUser(
        string $userId,
        string $title,
        string $body,
        array $data = []
    ): array {
        $tokens = \App\Models\FcmToken::forUser($userId)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return [
                'success' => 0,
                'failure' => 0,
                'total' => 0,
                'message' => 'No active tokens found for user',
            ];
        }

        return $this->sendToMultipleDevices($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple users
     *
     * @param array $userIds Array of user IDs
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Results
     */
    public function sendToUsers(
        array $userIds,
        string $title,
        string $body,
        array $data = []
    ): array {
        $tokens = \App\Models\FcmToken::whereIn('user_id', $userIds)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return [
                'success' => 0,
                'failure' => 0,
                'total' => 0,
                'message' => 'No active tokens found for users',
            ];
        }

        return $this->sendToMultipleDevices($tokens, $title, $body, $data);
    }

    /**
     * Check if Firebase is configured
     */
    public function isConfigured(): bool
    {
        return $this->credentials !== null && $this->projectId !== null;
    }
}
