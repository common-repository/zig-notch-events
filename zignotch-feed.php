<?php
/*
Plugin Name:        ZIGnotch
Plugin URI:         www.zignotch.com
Plugin Version:     2.1.7
Version:            2.1.7

Description:        Engage your attendees like never before. Run virtual, hybrid, physical events on an end-to-end platform. Create communities and monetize through membership

Author:             Babylon Software Solution

Requires at least:  5.2
Requires PHP:       7.3

License:            GNU General Public License v2
License URI:        https://www.gnu.org/licenses/gpl-2.0.html
*/
error_reporting(E_ERROR | E_PARSE);

if(! defined('ABSPATH')) exit;
global $wp_object_cache;
global $wp_query;
global $post;

include(plugin_dir_path(__FILE__) . "includes/functions/functions.php");
include(plugin_dir_path(__FILE__) . "includes/functions/styles-and-scripts.php");

if(get_option('zig_notch_api_key') == '' || get_option('zig_notch_api_key') == null){
    add_query_arg("errors", "Error: Problem with api key.");
}
else{
    // Elementor Widgets
    include(plugin_dir_path(__FILE__) . "includes/functions/elementor/widgets.php");
    // Rest API routs
    include(plugin_dir_path(__FILE__) . "includes/functions/rest.php");
}