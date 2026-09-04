<?php

/**
 * Configuration overrides for WP_ENV === 'ngrok'.
 */

use Roots\WPConfig\Config;

require_once __DIR__.'/development.php';

$ngrok_domain_validator = static fn (): array => [
    'options' => [
        'regexp' => '/(?:[\w_-]+\.)?(?:ngrok|ngrok-free)\.(?:[a-z]{2,3})/i',
    ],
];

$proto = app_get_server_data( 'HTTP_X_FORWARDED_PROTO', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_REQUIRE_SCALAR );

$forwarded_host = app_get_server_data( 'HTTP_X_FORWARDED_HOST', FILTER_VALIDATE_REGEXP, $ngrok_domain_validator() );

$abuse_interstitial = filter_input( INPUT_COOKIE, 'abuse_interstitial', FILTER_VALIDATE_REGEXP, $ngrok_domain_validator() );

if ( empty( $proto ) ) {
    return;
}

if ( empty( $forwarded_host ) ) {
    return;
}

if ( empty( $abuse_interstitial ) ) {
    return;
}

if ( $forwarded_host !== $abuse_interstitial ) {
    return;
}

$host = sprintf( '%s://%s', $proto, $forwarded_host );

Config::define( 'WP_HOME', $host );
Config::define( 'WP_SITEURL', "{$host}/wp" );

Config::define( 'WP_MAIL_SMTP_DEBUG', true );
Config::define( 'WP_MAIL_SMTP_DEBUG_LEVEL', 2 );

$_SERVER['HTTP_HOST'] = $forwarded_host;

unset(
    $ngrok_domain_validator,
    $proto,
    $forwarded_host,
    $abuse_interstitial,
    $host
);
