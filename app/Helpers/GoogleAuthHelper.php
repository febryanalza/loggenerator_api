<?php

namespace App\Helpers;

class GoogleAuthHelper
{
    /**
     * Generate a random password for Google authenticated users
     * Creates a secure random password with letters and numbers
     * 
     * @param int $length Password length (default: 20)
     * @return string Generated random password
     */
    public static function generateRandomPassword(int $length = 20): string
    {
        // Character set: uppercase, lowercase letters and numbers
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomPassword = '';

        // Generate random password
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomPassword;
    }

    /**
     * Generate a more secure random password with special characters
     * 
     * @param int $length Password length (default: 20)
     * @return string Generated secure random password
     */
    public static function generateSecureRandomPassword(int $length = 20): string
    {
        // Character set with special characters for higher security
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $charactersLength = strlen($characters);
        $randomPassword = '';

        // Ensure at least one of each type
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specials = '!@#$%^&*';

        // Start with one of each type
        $randomPassword .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $randomPassword .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $randomPassword .= $numbers[random_int(0, strlen($numbers) - 1)];
        $randomPassword .= $specials[random_int(0, strlen($specials) - 1)];

        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $randomPassword .= $characters[random_int(0, $charactersLength - 1)];
        }

        // Shuffle the password to randomize position of required characters
        return str_shuffle($randomPassword);
    }

    /**
     * Check if a user needs a random password (Google users without password)
     * 
     * @param string|null $currentPassword Current hashed password
     * @param string $authProvider Authentication provider
     * @return bool True if needs random password
     */
    public static function needsRandomPassword(?string $currentPassword, string $authProvider = 'google'): bool
    {
        return empty($currentPassword) && $authProvider === 'google';
    }

    /**
     * Generate random password specifically for Google authentication
     * Uses alphanumeric characters only to avoid potential issues
     * 
     * @return string Generated password for Google users
     */
    public static function generateGoogleUserPassword(): string
    {
        return self::generateRandomPassword(20);
    }
}
