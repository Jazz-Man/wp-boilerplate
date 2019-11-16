<?php

namespace JazzMan\Themes\Menu;

/**
 * Trait MenuTrait.
 */
trait MenuTrait
{
    /**
     * @param \WP_Post|\stdClass $item
     * @param int                $depth
     * @param array              $custom_atts
     *
     * @return string
     */
    private function get_link_attributes($item, $depth = 0, array $custom_atts = [])
    {
        $hide_link          = (bool)get_post_meta($item->ID, 'menu-item-mm-hide-link', true);

        $atts          = [];
        $atts['title'] = ! empty($item->attr_title) ? $item->attr_title : '';

        if (!$hide_link){
            $atts['href']  = ! empty($item->url) ? $item->url : '';
        }else{
            $atts['href'] = '#';
        }

        $atts['id']    = "nav-link-{$item->ID}";

        $atts['class'] = [
            'nav-link',
            "level-{$depth}",
            $item->current ? 'active' : '',
        ];

        if ('_blank' === $item->target && empty($item->xfn)) {
            $atts['rel'] = 'noopener noreferrer';
        } else {
            $atts['rel'] = $item->xfn;
        }

        if ( ! empty($item->classes) && \in_array('livechat', $item->classes)) {
            $atts['onclick'] = 'LC_API.open_chat_window();';
        }

        if ( ! empty($custom_atts)) {
            $atts = array_merge_recursive($atts, $custom_atts);
        }

        return app_add_attr_to_el($atts);
    }

    /**
     * @param \WP_Post|\stdClass $item
     *
     * @param array              $custom_classes
     *
     * @param int                $depth
     *
     * @return string
     */

    private function get_item_classes($item, array $custom_classes = [],$depth = 0)
    {
        $defaults = [
            'nav-item',
            "menu-item-{$item->ID}",
            "level-{$depth}"
        ];

        if ( ! empty($custom_classes)) {
            $defaults = wp_parse_args($custom_classes, $defaults);
        }

        $classes = wp_parse_args($item->classes, $defaults);

        $classes = array_filter($classes);
        $classes = array_unique($classes);

        return app_add_attr_to_el([
            'class' => $classes,
            'id'    => "menu-item-{$item->ID}",
        ]);
    }
}
