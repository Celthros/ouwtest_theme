<?php

/**
 * Plugin Name: WP Learn Hooks
 * Description: A simple plugin to demonstrate how to use hooks in WordPress.
 * Version: 1.0
 */

add_filter('the_content', 'wp_learn_amend_content');

function wp_learn_amend_content($content): string
{
    return $content . '<p>Thanks for reading!</p>';
}
