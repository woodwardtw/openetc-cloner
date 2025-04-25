<?php 
/*
Plugin Name: OpenETC Cloner
Plugin URI:  https://github.com/
Description: Let's clone sites via gravity form & NS Cloner
Version:     1.0
Author:      Tom Woodward
Author URI:  https://tomwoodward.us
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: open-etc-cloner

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action('wp_enqueue_scripts', 'opened_duplicator_scripts');

function opened_duplicator_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('ca-cloner-main-js', plugin_dir_url( __FILE__) . 'js/ca-cloner-main.js', $deps, $version, $in_footer); 
    wp_enqueue_style( 'ca-cloner-main-css', plugin_dir_url( __FILE__) . 'css/ca-cloner-main.css');
}

//includes directory.
$ca_cloner_inc_dir = 'inc';

//array of files to include.
$ca_cloner_includes = array(
    '/data.php',
    '/acf.php',
    '/gravity.php'          
);

// Include files.
foreach ( $ca_cloner_includes as $file ) {
    require_once plugin_dir_path(__FILE__) . 'inc' . $file ;
}


//GET URL OF CLONE SITE
function acf_fetch_site_url(){
  global $post;
  $html = '';
  $site_url = get_field('site_url');
    if( $site_url) {      
        $html = $site_url;  
        return $html;    
    }

}

//BUTTON BUILDER and DIALOG
function build_site_clone_button($content){
    global $post;
    $source_id = get_field('site_id', $post->ID);
    if ($post->post_type === 'clone'){
       $button = clone_button_maker();
       $form = do_shortcode( "[gravityform id='1' ajax='false' field_values='cloner={$source_id}']" ); 
       $modal ="
        <div id='clone-modal' class='hidden'>
            <button id='close'>X</button>
                {$form}
        </div>
        <dialog id='in-process'>
            <h1>Cloning in process . . . </h1>
              <p>Please leave the page open. You will be redirected to the new site when the clone is completed.</p>              
        </dialog>
       ";
        return $content . $button . $modal ;
    }
    else {
        return $content;
    }
}

add_filter( 'the_content', 'build_site_clone_button' );

//builds clone button link
function clone_button_maker(){
    return "<button id='modal-button' class='dup-button'>Clone the site</button>";
}


//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

