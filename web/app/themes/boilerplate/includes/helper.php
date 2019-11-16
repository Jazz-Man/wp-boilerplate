<?php

if ( ! function_exists('is_rest')) {
    /**
     * @return bool
     */
    function is_rest()
    {
        $rest_prefix = trailingslashit(rest_get_url_prefix());

        return (false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix));
    }
}


if ( ! function_exists('app_add_attr_to_el')) {
    /**
     * @param array $attr
     *
     * @return string
     */
    function app_add_attr_to_el(array $attr)
    {
        $attributes = '';
        foreach ($attr as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(' ', array_filter($value));
            }
            if ('class' === $key && '' === $value) {
                continue;
            }

            $attributes .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        return $attributes;
    }
}