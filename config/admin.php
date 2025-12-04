<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Token Expiration
    |--------------------------------------------------------------------------
    |
    | This value determines how long admin tokens remain valid before they
    | expire. The value is specified in hours. Set to null or 0 for tokens
    | that never expire (not recommended for security reasons).
    |
    | Mobile app tokens do not use this expiration and remain valid until
    | the user explicitly logs out.
    |
    */
    'token_expiration_hours' => env('ADMIN_TOKEN_EXPIRATION_HOURS', 4),

    /*
    |--------------------------------------------------------------------------
    | Admin Roles
    |--------------------------------------------------------------------------
    |
    | List of roles that are considered admin roles and can access the
    | admin dashboard. Users must have at least one of these roles to
    | login via the admin authentication endpoint.
    |
    */
    'roles' => [
        'Super Admin',
        'Admin',
        'Manager',
        'Institution Admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Refresh Threshold
    |--------------------------------------------------------------------------
    |
    | When the remaining token validity is below this threshold (in minutes),
    | the frontend should prompt or automatically refresh the token.
    | Default is 30 minutes before expiration.
    |
    */
    'token_refresh_threshold_minutes' => env('ADMIN_TOKEN_REFRESH_THRESHOLD', 30),
];
