<?php

namespace JazzMan\Themes;

/**
 * Class App.
 */
class App
{

    public function __construct()
    {
        add_action('after_setup_theme', [__CLASS__, 'restore_user_sensitive_data_request'], PHP_INT_MIN);
        $this->autoload_classes();
    }

    public static function restore_user_sensitive_data_request()
    {
        if (! empty($_REQUEST['_data'])) {
            $request_method = $_SERVER['REQUEST_METHOD'];

            try {
                $user_data = app_json_decode(app_base64_encode_data(wp_unslash($_REQUEST['_data'])));

                switch ($request_method) {
                    case 'POST':
                        foreach ($user_data as $key => $value) {
                            $_POST[$key] = $value;
                        }

                        if (! empty($_POST['_data'])) {
                            unset($_POST['_data']);
                        }
                        break;
                    case 'GET':
                        foreach ($user_data as $key => $value) {
                            $_GET[$key] = $value;
                        }

                        if (! empty($_GET['_data'])) {
                            unset($_GET['_data']);
                        }
                        break;
                    default:
                        foreach ($user_data as $key => $value) {
                            $_REQUEST[$key] = $value;
                        }

                        if (! empty($_REQUEST['_data'])) {
                            unset($_REQUEST['_data']);
                        }
                        break;
                }
            } catch (\Exception $exception) {
                \error_log($exception);
            }
        }
    }

    /**
     * @return bool
     */
    public static function is_production()
    {
        return \defined('WP_ENV') && WP_ENV === 'production';
    }

    private function autoload_classes()
    {
        $classes = [
            SetupTheme::class,
            EnqueueScripts::class,
            Sidebars::class
        ];

        app_autoload_classes($classes);
    }

}
