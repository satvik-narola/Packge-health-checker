<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Features
    |--------------------------------------------------------------------------
    |
    | Here you can enable or disable specific features of the package.
    |
    */

    'features' => [
        'deprecated_check' => env('PACKAGE_HEALTH_DEPRECATED_CHECK', true),
        'security_check' => env('PACKAGE_HEALTH_SECURITY_CHECK', true),
        'compatibility_check' => env('PACKAGE_HEALTH_COMPATIBILITY_CHECK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Check Settings
    |--------------------------------------------------------------------------
    |
    | Configure the security vulnerability checking.
    |
    */

    'security' => [
        'advisories_url' => 'https://raw.githubusercontent.com/FriendsOfPHP/security-advisories/master/database/{package}.json',
        'timeout' => 5, // Request timeout in seconds
        'fallback_enabled' => true, // Enable fallback when primary check fails
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    |
    | Configure the dashboard access and behavior.
    |
    */

    'dashboard' => [
        'path' => 'package-health',
        'middleware' => ['web'],
        'restrict_access' => env('PACKAGE_HEALTH_RESTRICT_ACCESS', true),
        'allowed_emails' => explode(',', env('PACKAGE_HEALTH_ALLOWED_EMAILS', '')),
        'allowed_domains' => explode(',', env('PACKAGE_HEALTH_ALLOWED_DOMAINS', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for package health results.
    |
    */

    'cache' => [
        'enabled' => env('PACKAGE_HEALTH_CACHE_ENABLED', true),
        'duration' => env('PACKAGE_HEALTH_CACHE_DURATION', 3600), // in seconds
    ],
];