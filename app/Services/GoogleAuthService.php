<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Oauth2;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    private GoogleClient $client;
    private array $allowedClientIds;
    private string $projectId = 'loggenerator-473712';

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret', ''));
        $this->client->setAccessType('offline');
        
        // Get all allowed client IDs from config
        $this->allowedClientIds = array_filter(config('services.google.allowed_client_ids', []));
    }

    /**
     * Verify Google ID token and extract user data
     * Supports multiple client IDs (Web, Android, iOS)
     *
     * @param string $idToken
     * @return array|null
     * @throws Exception
     */
    public function verifyIdToken(string $idToken): ?array
    {
        try {
            // First, try to verify with each allowed client ID
            $payload = null;
            $verifiedClientId = null;
            
            foreach ($this->allowedClientIds as $clientId) {
                try {
                    // Set client ID for verification
                    $this->client->setClientId($clientId);
                    $payload = $this->client->verifyIdToken($idToken);
                    
                    if ($payload) {
                        $verifiedClientId = $clientId;
                        Log::info('Token verified with client ID', ['client_id' => $clientId]);
                        break;
                    }
                } catch (Exception $e) {
                    // Continue to next client ID
                    continue;
                }
            }
            
            if (!$payload) {
                Log::warning('Google ID token verification failed with all client IDs', [
                    'token_length' => strlen($idToken),
                    'allowed_client_ids_count' => count($this->allowedClientIds)
                ]);
                return null;
            }

            // Additional security checks
            if (!$this->validateTokenPayload($payload, $verifiedClientId)) {
                Log::warning('Token payload validation failed', [
                    'iss' => $payload['iss'] ?? 'unknown',
                    'aud' => $payload['aud'] ?? 'unknown'
                ]);
                return null;
            }

            // Extract user data from payload
            $userData = [
                'google_id' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'],
                'avatar_url' => $payload['picture'] ?? null,
                'email_verified' => $payload['email_verified'] ?? false,
                'locale' => $payload['locale'] ?? null,
                'family_name' => $payload['family_name'] ?? null,
                'given_name' => $payload['given_name'] ?? null,
                'verified_client_id' => $verifiedClientId,
                'client_platform' => $this->getClientPlatform($verifiedClientId),
            ];

            Log::info('Google ID token verified successfully', [
                'google_id' => $userData['google_id'],
                'email' => $userData['email'],
                'client_platform' => $userData['client_platform']
            ]);

            return $userData;

        } catch (Exception $e) {
            Log::error('Google ID token verification error', [
                'error' => $e->getMessage(),
                'token_length' => strlen($idToken)
            ]);
            throw new Exception('Failed to verify Google ID token: ' . $e->getMessage());
        }
    }

    /**
     * Get user info using access token (alternative method)
     *
     * @param string $accessToken
     * @return array|null
     * @throws Exception
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $this->client->setAccessToken($accessToken);
            
            $oauth2 = new Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();

            $userData = [
                'google_id' => $userInfo->getId(),
                'email' => $userInfo->getEmail(),
                'name' => $userInfo->getName(),
                'avatar_url' => $userInfo->getPicture(),
                'email_verified' => $userInfo->getVerifiedEmail(),
                'locale' => $userInfo->getLocale(),
                'family_name' => $userInfo->getFamilyName(),
                'given_name' => $userInfo->getGivenName(),
            ];

            Log::info('Google user info retrieved successfully', [
                'google_id' => $userData['google_id'],
                'email' => $userData['email']
            ]);

            return $userData;

        } catch (Exception $e) {
            Log::error('Google user info retrieval error', [
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to get Google user info: ' . $e->getMessage());
        }
    }

    /**
     * Validate Google credentials format
     *
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(array $credentials): bool
    {
        $requiredFields = ['id_token'];
        $optionalFields = ['access_token', 'server_auth_code'];

        // Check if at least id_token is provided
        if (!isset($credentials['id_token']) || empty($credentials['id_token'])) {
            return false;
        }

        // Validate id_token format (should be JWT)
        $idToken = $credentials['id_token'];
        $parts = explode('.', $idToken);
        
        return count($parts) === 3; // JWT should have 3 parts separated by dots
    }

    /**
     * Extract basic info from ID token without full verification (for quick checks)
     *
     * @param string $idToken
     * @return array|null
     */
    public function extractBasicInfo(string $idToken): ?array
    {
        try {
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                return null;
            }

            // Decode payload (middle part)
            $payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);
            
            if (!$payload) {
                return null;
            }

            return [
                'google_id' => $payload['sub'] ?? null,
                'email' => $payload['email'] ?? null,
                'name' => $payload['name'] ?? null,
                'exp' => $payload['exp'] ?? null, // expiration time
                'iat' => $payload['iat'] ?? null, // issued at time
            ];

        } catch (Exception $e) {
            Log::warning('Failed to extract basic info from ID token', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate token payload for security
     *
     * @param array $payload
     * @param string $verifiedClientId
     * @return bool
     */
    private function validateTokenPayload(array $payload, string $verifiedClientId): bool
    {
        // Check issuer
        $validIssuers = ['https://accounts.google.com', 'accounts.google.com'];
        if (!isset($payload['iss']) || !in_array($payload['iss'], $validIssuers)) {
            return false;
        }

        // Check audience (should be one of our client IDs)
        if (!isset($payload['aud']) || !in_array($payload['aud'], $this->allowedClientIds)) {
            return false;
        }

        // Check if token is expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        // Check if token was issued in the future (with some tolerance)
        if (isset($payload['iat']) && $payload['iat'] > (time() + 300)) { // 5 minutes tolerance
            return false;
        }

        return true;
    }

    /**
     * Get platform type based on client ID
     *
     * @param string $clientId
     * @return string
     */
    private function getClientPlatform(string $clientId): string
    {
        $webClientId = config('services.google.client_id');
        $androidClientId = config('services.google.allowed_client_ids')[1] ?? null;
        $iosClientId = config('services.google.allowed_client_ids')[2] ?? null;

        if ($clientId === $webClientId) {
            return 'web';
        } elseif ($clientId === $androidClientId) {
            return 'android';
        } elseif ($clientId === $iosClientId) {
            return 'ios';
        }

        return 'unknown';
    }

    /**
     * Get all allowed client IDs
     *
     * @return array
     */
    public function getAllowedClientIds(): array
    {
        return $this->allowedClientIds;
    }

    /**
     * Check if client ID is allowed
     *
     * @param string $clientId
     * @return bool
     */
    public function isClientIdAllowed(string $clientId): bool
    {
        return in_array($clientId, $this->allowedClientIds);
    }
}