<?php

namespace JazzMan\Themes;

use _WP_Dependency;
use JazzMan\AutoloadInterface\AutoloadInterface;
use JazzMan\Performance\Optimization\Enqueue;

/**
 * Class EnqueueScripts.
 */
class EnqueueScripts implements AutoloadInterface
{
    public function load()
    {
        add_action('wp_enqueue_scripts', [$this, 'cdn_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
//        add_action('wp_print_styles', [$this, 'remove_assets']);
    }

    public function enqueue_scripts()
    {
        $grid_css_file = app_dir_path('dist/css/bootstrap-grid.css');

        if (file_exists($grid_css_file)) {
            $grid_css_current_file_time = file_get_contents($grid_css_file);

            wp_register_style('wp-boilerplate-grid', false); // phpcs:ignore
            wp_enqueue_style('wp-boilerplate-grid');

            wp_add_inline_style('iconic-grid', $grid_css_current_file_time);
        }

        wp_enqueue_style('wp-boilerplate-style', app_url_path('dist/css/main.css'));

        wp_enqueue_script('wp-boilerplate-script', app_url_path('dist/js/main.js'));
    }

    public function cdn_scripts()
    {
        $registered_scripts = wp_scripts()->registered;

        $libs = [
            'lodash',
            'moment',
            'react',
            'react-dom',
        ];

        $cdn_base_url = 'https://cdn.jsdelivr.net/npm';

        $cdn_libs = [];

        foreach ($libs as $lib) {
            if (!empty($registered_scripts[$lib]) && $registered_scripts[$lib] instanceof _WP_Dependency && $registered_scripts[$lib]->ver) {
                $is_react = \in_array($lib, ['react', 'react-dom']);

                $lib_name = $is_react ? '.production' : '';
                $lib_prefix = $is_react ? 'umd/' : '';

                $cdn_libs[$lib] = "{$cdn_base_url}/{$lib}@{$registered_scripts[$lib]->ver}/{$lib_prefix}{$lib}{$lib_name}.min.js";
            }
        }

        if (!empty($cdn_libs)) {
            foreach ($cdn_libs as $handle => $url) {
                Enqueue::deregisterScript($handle, $url);
            }
        }
    }

    public function remove_assets()
    {
        $deregister_styles = [
        ];

        $deregister_scripts = [
        ];

        foreach ($deregister_styles as $style) {
            Enqueue::deregisterStyle($style);
        }

        foreach ($deregister_scripts as $script) {
            Enqueue::deregisterScript($script);
        }
    }

}
