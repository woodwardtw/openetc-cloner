<?php
/**
 * custom post types and Rest API modifications
 *
 * 
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// WordPress Multisite Subdirectory URL Checker
// This code registers a custom REST API endpoint to check used subdirectory URLs

/**
 * Register custom REST API endpoints for checking multisite subdirectory URLs
 * Add this to your theme's functions.php or in a custom plugin
 */
function register_multisite_endpoints() {
  // Endpoint to get all sites
  register_rest_route('multisite/v1', '/sites', array(
    'methods' => 'GET',
    'permission_callback' => '__return_true', // Allow anyone to access this endpoint
    'callback' => 'get_multisite_sites'
  ));
  
  // Endpoint to check if a specific path exists - using alphanumeric, underscore, dash chars
  register_rest_route('multisite/v1', '/check/(?P<path>[a-zA-Z0-9_-]+)', array(
    'methods' => 'GET',
    'permission_callback' => '__return_true', // Allow anyone to access this endpoint
    'callback' => 'check_multisite_path'
  ));
}

/**
 * Callback function to get all sites in the multisite network
 * Returns site details including path (subdirectory URL)
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function get_multisite_sites($request) {
  // Ensure we're in a multisite environment
  if (!is_multisite()) {
    return new WP_Error('not_multisite', 'This is not a WordPress multisite installation', array('status' => 400));
  }
  
  // Get all sites in the network
  $sites = get_sites(array(
    'public'   => 1,
    'archived' => 0,
    'deleted'  => 0
  ));
  
  $site_data = array();
  
  foreach ($sites as $site) {
    $site_details = get_blog_details($site->blog_id);
    
    // Clean up path (remove leading and trailing slashes)
    $path = trim($site_details->path, '/');
    $path = $path === '' ? '(root)' : $path;
    
    $site_data[] = array(
      'id'     => $site->blog_id,
      'name'   => $site_details->blogname,
      'url'    => $site_details->siteurl,
      'path'   => $path,
      'active' => !$site_details->archived && !$site_details->deleted
    );
  }
  
  return rest_ensure_response($site_data);
}

/**
 * Callback function to check if a specific path exists in the multisite network
 * Returns path availability information
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response|WP_Error Response object or error
 */
function check_multisite_path($request) {
  // Ensure we're in a multisite 
  if (!is_multisite()) {
    return new WP_Error('not_multisite', 'This is not a WordPress multisite installation', array('status' => 400));
  }
  
  // Get the path from the request
  $path_to_check = $request['path'];
  
  // Get all sites in the network
  $sites = get_sites(array(
    'public'   => 1,
    'archived' => 0,
    'deleted'  => 0
  ));
  
  $paths = array();
  $path_exists = false;
  $matching_site = null;
  
  foreach ($sites as $site) {
    $site_details = get_blog_details($site->blog_id);
    
    // Clean up path (remove leading and trailing slashes)
    $path = trim($site_details->path, '/');
    
    // Add to paths array
    $paths[] = $path;
    
    // Check if this path matches the one we're looking for
    if ($path === $path_to_check) {
      $path_exists = true;
      $matching_site = array(
        'id'     => $site->blog_id,
        'name'   => $site_details->blogname,
        'url'    => $site_details->siteurl,
        'path'   => $path,
        'active' => !$site_details->archived && !$site_details->deleted
      );
    }
  }
  
  // Return results
  return rest_ensure_response(array(
    'path' => $path_to_check,
    'exists' => $path_exists,
    'site' => $matching_site,
    //'all_paths' => $paths
  ));
}

// Hook into WordPress REST API initialization
add_action('rest_api_init', 'register_multisite_endpoints');


/*
CREATE CLONE CUSTOM POST TYPE
*/

// Register Custom Post Type clone
// Post Type Key: clone

function create_clone_cpt() {

  $labels = array(
    'name' => __( 'Clones', 'Post Type General Name', 'textdomain' ),
    'singular_name' => __( 'Clone', 'Post Type Singular Name', 'textdomain' ),
    'menu_name' => __( 'Clone', 'textdomain' ),
    'name_admin_bar' => __( 'Clone', 'textdomain' ),
    'archives' => __( 'Clone Archives', 'textdomain' ),
    'attributes' => __( 'Clone Attributes', 'textdomain' ),
    'parent_item_colon' => __( 'Clone:', 'textdomain' ),
    'all_items' => __( 'All Clones', 'textdomain' ),
    'add_new_item' => __( 'Add New Clone', 'textdomain' ),
    'add_new' => __( 'Add New', 'textdomain' ),
    'new_item' => __( 'New Clone', 'textdomain' ),
    'edit_item' => __( 'Edit Clone', 'textdomain' ),
    'update_item' => __( 'Update Clone', 'textdomain' ),
    'view_item' => __( 'View Clone', 'textdomain' ),
    'view_items' => __( 'View Clones', 'textdomain' ),
    'search_items' => __( 'Search Clones', 'textdomain' ),
    'not_found' => __( 'Not found', 'textdomain' ),
    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
    'featured_image' => __( 'Featured Image', 'textdomain' ),
    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
    'insert_into_item' => __( 'Insert into clone', 'textdomain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this clone', 'textdomain' ),
    'items_list' => __( 'Clone list', 'textdomain' ),
    'items_list_navigation' => __( 'Clone list navigation', 'textdomain' ),
    'filter_items_list' => __( 'Filter Clone list', 'textdomain' ),
  );
  $args = array(
    'label' => __( 'clone', 'textdomain' ),
    'description' => __( '', 'textdomain' ),
    'labels' => $labels,
    'menu_icon' => '',
    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
    'taxonomies' => array('category'),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 5,
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-universal-access-alt',
  );
  register_post_type( 'clone', $args );
  
  // flush rewrite rules because we changed the permalink structure
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}
add_action( 'init', 'create_clone_cpt', 0 );
