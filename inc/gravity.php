<?php
/**
 * gravity forms functions
 *
 * 
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$form_id = RGFormsModel::get_form_id('duplicate site');//get form id that matches "duplicate site" - can replace with options setup that is currently commented out in inc/acf.php but it'll take a bit of work in the javascript to fix the IDs


add_action( 'gform_after_submission_' . $form_id, 'gform_site_cloner', 10, 2 );//specific to the gravity form id

function gform_site_cloner($entry, $form){
    //**FROM https://support.neversettle.it/article/22-how-to-call-ns-cloner-to-copy-sites-from-other-plugins **//

    //make sure ns cloner is loaded
    if ( ! function_exists( 'ns_cloner_perform_clone' ) ) {
            return;
    }
    $user_id = get_current_user_id();
    $response = ns_cloner_perform_clone( array(
        'clone_mode'   => 'core',
        'source_id'    => rgar( $entry, '1' ), // any blog/site id on network,
        'target_name'  => rgar( $entry, '3' ),
        'target_title' => rgar( $entry, '2' ),
        'user_id'      => $user_id
        //'debug'          => 1
    ) );
    
    //this is what's in the example plugin but I think it's wrong
    if (  is_wp_error( $response ) ) {
    	write_log($response);
       //GFCommon::log_debug( 'cloner_site_error: result => ' . $response->get_error_message() );
    }
    
    GFCommon::log_debug( 'cloner_site_success: result => ' . print_r( $response, true ) );

    //REDIRECT TO THE CREATED SITE
    opened_cloner_redirect(rgar( $entry, '3' ));

}

function opened_cloner_redirect($name){
    $base_url = network_site_url();
    sleep(5);
    wp_redirect($base_url . $name); 
    exit;
}



//GRAVITY FORM PROVISIONING BASED ON CLONE POSTS
add_filter( 'gform_pre_render_'.$form_id, 'populate_posts' );
add_filter( 'gform_pre_validation_'.$form_id, 'populate_posts' );
add_filter( 'gform_pre_submission_filter_'.$form_id, 'populate_posts' );
add_filter( 'gform_admin_pre_render_'.$form_id, 'populate_posts' );
function populate_posts( $form ) {
 
    foreach ( $form['fields'] as &$field ) {
 
        if ( $field->id != 1 ) {
            continue;
        }
 
        // you can add additional parameters here to alter the posts that are retrieved
        // more info: http://codex.wordpress.org/Template_Tags/get_posts
        $posts = get_posts( 'numberposts=-1&post_status=publish&post_type=clone' );
 
        $choices = array();
 
        foreach ( $posts as $post ) {
            $clone_id = get_field('site_id', $post->ID);
           
            $choices[] = array( 'text' => $post->post_title, 'value' => $clone_id);
        }
 
        // update 'Select a Post' to whatever you'd like the instructive option to be
        $field->placeholder = 'Select a site to clone';
        $field->choices = $choices;
 
    }
 
    return $form;
}
