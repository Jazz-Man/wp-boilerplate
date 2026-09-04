<?php

namespace JazzMan\Themes;

use JazzMan\AutoloadInterface\AutoloadInterface;

/**
 * Class Sidebars.
 */
class Sidebars implements AutoloadInterface {

    public function load(): void {
        add_action( 'widgets_init', [__CLASS__, 'register_sidebars'] );
    }

    public static function register_sidebars(): void {
        register_sidebar( [
            'name' => 'Sidebar',
            'id' => 'sidebar',
        ] );
    }
}
