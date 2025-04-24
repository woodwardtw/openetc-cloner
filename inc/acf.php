<?php
/**
 * acf functions
 *
 * 
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//CREATE OPTIONS PAGE

// add_action('acf/init', function() {
//   if( function_exists('acf_add_options_page') ) {

//    acf_add_options_page(array(
//             'page_title'    => 'Clone Zone Settings',
//             'menu_title'    => 'Cloner Settings',
//             'menu_slug'     => 'clone-zone-settings',
//             'capability'    => 'edit_posts',
//             'redirect'      => false
//         ));

//   }
// });

//LOCAL STORAGE OF ACF DATA
//save acf json
add_filter('acf/settings/save_json', 'clone_zone_json_save_point');
 
function clone_zone_json_save_point( $path ) {
    // update path
    $path = plugin_dir_path(__FILE__) . 'acf-json'; //replace w get_stylesheet_directory() for theme    
    // return
    return $path;
    
}

// load acf json
add_filter('acf/settings/load_json', 'clone_zone_json_load_point');

function clone_zone_json_load_point( $paths ) {    
    // remove original path (optional)
    unset($paths[0]);
    // append path
    $paths[] = plugin_dir_path(__FILE__)  . '/acf-json';//replace w get_stylesheet_directory() for theme
    // return
    return $paths;
    
}