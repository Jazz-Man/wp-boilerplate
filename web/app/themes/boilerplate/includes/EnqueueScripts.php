<?php

namespace JazzMan\Themes;

use JazzMan\AutoloadInterface\AutoloadInterface;
use JazzMan\Performance\Optimization\Enqueue;

/**
 * Class EnqueueScripts.
 */
class EnqueueScripts implements AutoloadInterface {

    public const SCRIPTS_HANDLE_PREFIX = 'wp-boilerplate-';

    public const SCRIPTS_CONFIG_HANDLE = 'wp_boilerplate_init';
    public static $nonce_action = 'wp-boilerplate-nonce';

    public function load(): void {
        add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts'] );
        add_filter( 'wp_resource_hints', [__CLASS__, 'wp_resource_hints'], 10, 2 );

        if ( app_is_production() ) {
            add_action(
                'wp_print_styles',
                static function (): void {
                    wp_deregister_style( 'dashicons' );
                },
                100
            );
        }
    }

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
     * @see https://symfony.com/doc/current/web_link.html
     * @see https://wicg.github.io/priority-hints/
     * @see ResourceHints::getResourceHints()
     */
    public static function wp_resource_hints( array $hintUrls, string $relationType ): array {
        $get_manifest_url = static fn ( string $url, string $as ): array => [
            'href' => app_manifest_url( $url ),
            'as' => $as,
        ];

        switch ( $relationType ) {
            //            case 'dns-prefetch':
            //
            //                break;
            //            case 'preconnect':
            //
            //                break;
            case 'preload':

                $hintUrls[] = $get_manifest_url( '/css/bootstrap-grid.css', 'style' );
                $hintUrls[] = $get_manifest_url( '/css/fonts.css', 'style' );
                $hintUrls[] = $get_manifest_url( '/css/main.css', 'style' );
                $hintUrls[] = $get_manifest_url( '/js/main.js', 'script' );

                $fonts_dir = app_dir_path( 'dist/fonts' );

                if ( is_dir( $fonts_dir ) ) {

                    $fonts = app_files_in_path( $fonts_dir, '/\.woff2$/' );

                    foreach ( $fonts as $font ) {
                        if ( $font->isFile() ) {
                            $hintUrls[] = [
                                'href' => app_url_path( "dist/fonts/{$font->getFilename()}" ),
                                'as' => 'font',
                                'type' => 'font/woff2',
                            ];
                        }
                    }
                }

                break;
        }

        return $hintUrls;
    }

    public static function enqueue_scripts(): void {
        self::localize_scripts();

        wp_register_style( self::SCRIPTS_HANDLE_PREFIX.'grid', app_manifest_url( '/css/bootstrap-grid.css' ), [], false );
        wp_register_style( self::SCRIPTS_HANDLE_PREFIX.'fonts', app_manifest_url( '/css/fonts.css' ), [], false );
        wp_register_style( self::SCRIPTS_HANDLE_PREFIX.'wp-blocks', app_manifest_url( '/css/wp-blocks.css' ) );

        $style_deps = [
            self::SCRIPTS_HANDLE_PREFIX.'grid',
            self::SCRIPTS_HANDLE_PREFIX.'fonts',
            self::SCRIPTS_HANDLE_PREFIX.'wp-blocks',
        ];

        wp_enqueue_style( self::SCRIPTS_HANDLE_PREFIX.'style', app_manifest_url( '/css/main.css' ), $style_deps );

        wp_enqueue_script( self::SCRIPTS_HANDLE_PREFIX.'script', app_manifest_url( '/js/main.js' ) );
    }

    public static function nonce(): bool|string {
        return wp_create_nonce( self::$nonce_action );
    }

    public function remove_assets(): void {
        $deregister_styles = [];

        $deregister_scripts = [];

        foreach ( $deregister_styles as $style ) {
            Enqueue::deregisterStyle( $style );
        }

        foreach ( $deregister_scripts as $script ) {
            Enqueue::deregisterScript( $script );
        }
    }

    private static function localize_scripts(): void {
        wp_register_script( self::SCRIPTS_CONFIG_HANDLE, false );
        wp_enqueue_script( self::SCRIPTS_CONFIG_HANDLE );

        $args = [
            'home_url' => get_home_url(),
            'nonce' => self::nonce(),
            'rest_nonce' => wp_create_nonce( 'wp_rest' ),
        ];

        $args = apply_filters( 'localize_scripts_wp_boilerplate_config', $args );

        wp_localize_script( self::SCRIPTS_CONFIG_HANDLE, 'WpBoilerplateConfig', $args );
    }
}
