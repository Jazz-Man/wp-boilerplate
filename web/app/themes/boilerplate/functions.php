<?php

use JazzMan\Themes\EnqueueScripts;
use JazzMan\Themes\SetupTheme;
use JazzMan\Themes\Sidebars;

if (function_exists('app_autoload_classes')) {
    app_autoload_classes([
        SetupTheme::class,
        EnqueueScripts::class,
        Sidebars::class,
    ]);
}
