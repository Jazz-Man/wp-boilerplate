<?php

namespace JazzMan\Themes;

use JazzMan\AutoloadInterface\AutoloadInterface;

/**
 * Class EnqueueScripts.
 */
class EnqueueScripts implements AutoloadInterface
{
    public function load()
    {
        add_action('wp_enqueue_scripts', [$this, 'cdn_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('wp-boilerplate-style', app_url_path('dist/css/main.css'));

        wp_enqueue_script('wp-boilerplate-script', app_url_path('dist/js/main.js'));
    }

    public function cdn_scripts()
    {
        wp_deregister_script('jquery-core');
        wp_deregister_script('jquery');

        wp_register_script('jquery-core', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', false,
            null, true);
        wp_register_script('jquery', false, ['jquery-core'], null, true);

        $cdn_libs = [
            'lodash' => 'https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js',
            'moment' => 'https://cdn.jsdelivr.net/npm/moment@2.22.2/moment.min.js',
            'react' => 'https://cdn.jsdelivr.net/npm/react@16.8.4/umd/react.production.min.js',
            'react-dom' => 'https://cdn.jsdelivr.net/npm/react-dom@16.8.4/umd/react-dom.production.min.js',
        ];

        foreach ($cdn_libs as $handle => $url) {
            $this->deregister_script($handle, $url);
        }
    }

    /**
     * @param string      $handle
     * @param string|null $new_url
     */
    private function deregister_script(string $handle, $new_url = null)
    {
        $registered_scripts = wp_scripts()->registered;

        if (!empty($registered_scripts[$handle])) {
            /** @var \_WP_Dependency $js_lib */
            $js_lib = $registered_scripts[$handle];

            wp_dequeue_script($js_lib->handle);
            wp_deregister_script($js_lib->handle);

            if (!empty($new_url)) {
                wp_register_script($js_lib->handle, $new_url, $js_lib->deps, $js_lib->ver, true);

                if (!empty($js_lib->extra) && \is_array($js_lib->extra)) {
                    foreach ($js_lib->extra as $position => $data) {
                        wp_add_inline_script($js_lib->handle, end($data), $position);
                    }
                }
            }
        }
    }
}
