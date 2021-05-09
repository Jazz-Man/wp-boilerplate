<?php

namespace JazzMan\Themes;

use JazzMan\AutoloadInterface\AutoloadInterface;
use JazzMan\Performance\Optimization\Enqueue;
use JazzMan\Performance\Optimization\Http;

/**
 * Class EnqueueScripts.
 */
class EnqueueScripts implements AutoloadInterface
{
    /**
     * @var \JazzMan\AppConfig\Manifest
     */
    private $manifest;

    public static $nonce_action = 'wp-boilerplate-nonce';

    public const SCRIPTS_HANDLE_PREFIX = 'wp-boilerplate-';
    public const SCRIPTS_CONFIG_HANDLE = 'wp_boilerplate_init';

    public function load()
    {
        $this->manifest = app_manifest();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('app_preload_links', [$this, 'preload_link']);
        add_filter('style_loader_tag', [$this, 'add_async_style'], 10, 4);

        if (App::is_production()) {
            add_action(
                'wp_print_styles',
                static function () {
                    wp_deregister_style('dashicons');
                },
                100
            );
        }
    }

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
     * @see https://symfony.com/doc/current/web_link.html
     * @see https://wicg.github.io/priority-hints/
     */
    public function preload_link(array $links): array
    {
        try {
            $fonts_dir = app_dir_path('dist/fonts');
            $fonts = app_files_in_path($fonts_dir, '/\\.woff2$/');

            foreach ($fonts as $font) {
                if ($font->isFile()) {
                    $url = app_url_path("dist/fonts/{$font->getFilename()}");
                    $links[] = Http::preloadFont($url, 'font/woff2');
                }
            }
        } catch (\Exception $exception) {
            app_error_log($exception, __METHOD__);
        }

        $links[] = Http::preloadLink($this->manifest->getUrl('/css/bootstrap-grid.css'), 'style');
        $links[] = Http::preloadLink($this->manifest->getUrl('/css/fonts.css'), 'style');
        $links[] = Http::preloadLink($this->manifest->getUrl('/css/main.css'), 'style');
        $links[] = Http::preloadLink($this->manifest->getUrl('/js/main.js'), 'script');

        $dns_prefetch = [
            'https://www.googletagmanager.com',
            'https://www.gstatic.com',
        ];

        foreach ($dns_prefetch as $url) {
            $links[] = Http::preconnectLink($url);
        }

        return $links;
    }

    public function add_async_style(string $tag, string $handle, string $href, string $media): string
    {
        if ('print' === $media) {
            $tag = \sprintf(
                '<link rel="%s" id="%s-css" href="%s" media="%s" onload="this.media=\'all\'; this.onload=null;" />',
                'stylesheet',
                $handle,
                $href,
                $media
            );
        }

        return $tag;
    }

    public function enqueue_scripts()
    {
        $this->localize_scripts();

        $crawler = app_get_crawler_detect();

        wp_register_style(self::SCRIPTS_HANDLE_PREFIX.'grid', $this->manifest->getUrl('/css/bootstrap-grid.css'), [], false, $crawler->isCrawler() ? 'all' : 'print');
        wp_register_style(self::SCRIPTS_HANDLE_PREFIX.'fonts', $this->manifest->getUrl('/css/fonts.css'), [], false, $crawler->isCrawler() ? 'all' : 'print');
        wp_register_style(self::SCRIPTS_HANDLE_PREFIX.'wp-blocks', $this->manifest->getUrl('/css/wp-blocks.css'));

        $style_deps = [
            self::SCRIPTS_HANDLE_PREFIX.'grid',
            self::SCRIPTS_HANDLE_PREFIX.'fonts',
            self::SCRIPTS_HANDLE_PREFIX.'wp-blocks',
        ];

        wp_enqueue_style(self::SCRIPTS_HANDLE_PREFIX.'style', $this->manifest->getUrl('/css/main.css'), $style_deps);

        $is_google = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/\s+(Chrome\/|Googlebot\/)/i',
            ],
        ]);

        if (!$is_google) {
            $polyfills = [
                'Document',
                'IntersectionObserver',
                'IntersectionObserverEntry',
                'ResizeObserver',
                'Object.fromEntries',
                'Object.assign',
                'Object.is',
                'URL',
                'URLSearchParams',
                'Promise',
                'fetch',
                'matchMedia',
                'document',
            ];

            wp_enqueue_script(
                'polyfill.io',
                add_query_arg(
                    [
                        'features' => \implode(',', $polyfills),
                    ],
                    'https://polyfill.io/v3/polyfill.min.js'
                )
            );
        }

        wp_enqueue_script(self::SCRIPTS_HANDLE_PREFIX.'script', $this->manifest->getUrl('/js/main.js'));
    }

    private function localize_scripts()
    {
        wp_register_script(self::SCRIPTS_CONFIG_HANDLE, false);
        wp_enqueue_script(self::SCRIPTS_CONFIG_HANDLE);

        $args = [
            'home_url' => get_home_url(),
            'nonce' => self::nonce(),
            'rest_nonce' => wp_create_nonce('wp_rest'),
        ];

        $args = apply_filters('localize_scripts_wp_boilerplate_config', $args);

        wp_localize_script(self::SCRIPTS_CONFIG_HANDLE, 'WpBoilerplateConfig', $args);

        unset($args);
    }

    /**
     * @return bool|string
     */
    public static function nonce()
    {
        return wp_create_nonce(self::$nonce_action);
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
