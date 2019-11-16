<?php

namespace JazzMan\Themes;

use JazzMan\AutoloadInterface\AutoloadInterface;

/**
 * Class Sidebars.
 */
class Sidebars implements AutoloadInterface
{
    public function load()
    {
        add_action('widgets_init', [$this, 'register_sidebars']);
    }

    public function register_sidebars()
    {
        register_sidebar([
            'name'          => 'Sidebar',
            'id'            => 'sidebar',
        ]);
    }
}
