<?php

namespace JazzMan\Themes;

use JazzMan\AutoloadInterface\AutoloadInterface;

/**
 * Class SetupTheme.
 */
class SetupTheme implements AutoloadInterface
{
    public function load()
    {
        add_action('after_setup_theme', [$this, 'setup_theme']);
    }

    public function setup_theme()
    {
        load_theme_textdomain('wp-boilerplate', get_template_directory() . '/languages');

        add_theme_support('title-tag');
        add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption']);
        add_theme_support('align-wide');

        register_nav_menus([
            'main_menu' => 'Main Navigation',
            'sidebar_menu' => 'Sidebar Navigation',
        ]);
    }
}
