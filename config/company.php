<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | This file contains company-specific configuration that will be used
    | throughout the application, especially for the website homepage.
    |
    */

    'name' => env('COMPANY_NAME', 'LogGenerator'),
    
    'tagline' => env('COMPANY_TAGLINE', 'Simplifying Digital Logbook Management'),
    
    'description' => env('COMPANY_DESCRIPTION', 'Transform your traditional paper-based logbooks into powerful digital solutions. Streamline data collection, enhance accessibility, and boost productivity with our intuitive logbook management platform.'),
    
    /*
    |--------------------------------------------------------------------------
    | App Store Links
    |--------------------------------------------------------------------------
    */
    
    'android_app_url' => env('ANDROID_APP_URL', 'https://play.google.com/store/apps/details?id=com.loggenerator.app'),
    
    'ios_app_url' => env('IOS_APP_URL', 'https://apps.apple.com/app/loggenerator/id123456789'),
    
    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */
    
    'email' => env('COMPANY_EMAIL', 'support@loggenerator.com'),
    
    'phone' => env('COMPANY_PHONE', '+62 21 1234 5678'),
    
    'address' => env('COMPANY_ADDRESS', 'Jakarta, Indonesia'),
    
    /*
    |--------------------------------------------------------------------------
    | Social Media Links
    |--------------------------------------------------------------------------
    */
    
    'social' => [
        'facebook' => env('COMPANY_FACEBOOK', ''),
        'twitter' => env('COMPANY_TWITTER', ''),
        'linkedin' => env('COMPANY_LINKEDIN', ''),
        'instagram' => env('COMPANY_INSTAGRAM', ''),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Website Settings
    |--------------------------------------------------------------------------
    */
    
    'website_url' => env('APP_URL', 'http://localhost'),
    
    'logo_url' => env('COMPANY_LOGO_URL', '/images/logo.png'),
    
    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard Settings
    |--------------------------------------------------------------------------
    */
    
    'admin_dashboard_enabled' => env('ADMIN_DASHBOARD_ENABLED', true),
    
    'admin_dashboard_url' => env('ADMIN_DASHBOARD_URL', '/admin'),
];