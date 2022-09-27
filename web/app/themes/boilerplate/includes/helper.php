<?php

if (!function_exists('app_is_production')) {

    function app_is_production(): bool {
        return \defined('WP_ENV') && WP_ENV === 'production';
    }
}
