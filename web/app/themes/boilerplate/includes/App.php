<?php

namespace JazzMan\Themes;

/**
 * Class App.
 */
class App
{

    public function __construct()
    {
        $classes = [
            SetupTheme::class
        ];

        app_autoload_classes($classes);
    }

}
