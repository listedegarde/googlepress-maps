<?php 
/**
 * Plugin Name: GooglePress Maps
 * Plugin URI: http://
 * Description: Google Maps plugin for creating collaborative maps.
 * Version: 1.0
 * Author: Jonathan Frazer
 * Author URI: http://www.jonathanfrazer.ca
 * License: GPL2
 */

defined('ABSPATH') or die("No script kiddies please!");
define('PREFIX', '_gpm_');

// Define custom post type
add_action( 'init', 'create_gpmaps' );
function create_gpmaps() {
    $labels = array(
        'name' => __('Maps', 'gpm'),
        'singular_name' => __('Map', 'gpm'),
        'add_new' => __('Add New', 'gpm'),
        'add_new_item' => __('Add New Map', 'gpm'),
        'edit_item' => __('Edit Map', 'gpm'),
        'new_item' => __('New Map', 'gpm'),
        'view_item' => __('View Map', 'gpm'),
        'search_items' => __('Search Maps', 'gpm'),
        'not_found' =>  __('Nothing found', 'gpm'),
        'not_found_in_trash' => __('Nothing found in Trash', 'gpm'),
        'parent_item_colon' => ''
	);
 
	$args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'menu_icon' => 'dashicons-location-alt',
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title','thumbnail','author','comments')  //'editor',
    ); 
    register_post_type( 'gpmaps', $args );

    // Taxonomies
    register_taxonomy("Layers", array("gpmaps"), array("hierarchical" => false, "label" => "Layers", "singular_label" => "Layer", "rewrite" => true));

}

// Custom page templates and CSS
add_filter( 'template_include', 'gpmaps_templates' );
function gpmaps_templates( $template ) {
    $dir = plugin_dir_path( __FILE__ );
    $post_types = array( 'gpmaps' );

    if ( is_post_type_archive( $post_types ) && ! file_exists( get_stylesheet_directory() . '/archive-gpmaps.php' ) )
        $template = $dir.'templates/archive-gpmaps.php';
    if ( is_singular( $post_types ) && ! file_exists( get_stylesheet_directory() . '/single-gpmaps.php' ) )
        $template = $dir.'templates/single-gpmaps.php';
    if ( ! file_exists( get_stylesheet_directory() . '/gpmcss.css' ) )
        wp_enqueue_style( 'gpmaps-css', plugin_dir_url( __FILE__ ).'templates/gpmcss.css' );

    return $template;
}

// Metaboxes
// https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress

add_action( 'init', 'gpm_initialize_gpm_meta_boxes', 9999 );
function gpm_initialize_gpm_meta_boxes() {
    if ( ! class_exists( 'gpm_Meta_Box' ) ) {
        require_once 'metabox/init.php';
        require_once 'metabox/lib/cmb-field-map.php';
       // wp_enqueue_script( 'jquery_exif_init', plugin_dir_url( __FILE__ ) . 'metabox/js/jquery.exif.js', array(), null );
    }
}

function be_sample_metaboxes( $meta_boxes ) {
    $prefix = PREFIX; // Prefix for all fields
    $meta_boxes['test_metabox'] = array(
        'id' => 'test_metabox',
        'title' => 'Test Metabox',
        'pages' => array('gpmaps'), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Layer',
                'desc' => 'Choose which layer this pin should be placed in or add a new layer.',
                'id' => $prefix . 'layer',
                'taxonomy' => 'Layers', //Enter Taxonomy Slug
                'type' => 'taxonomy_select',    
            ),
            array(
                'name' => 'New Layer',
                'desc' => '',
                'id' => $prefix . 'newlayer',
                'type' => 'text'
            ),
            array(
                'name' => 'Picture',
                'desc' => 'Upload a picture you wish to share of the location',
                'id' => $prefix . 'file',
                'type' => 'file',
                // 'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
            ),
            array(
                'name' => 'GPS Location',
                'desc' => 'Drag the marker to set the exact location',
                'id' => $prefix . 'location',
                'type' => 'pw_map',
                'sanitization_cb' => 'pw_map_sanitise',
            ),
            array(
                'name' => 'Rating',
                'desc' => '',
                'id' => $prefix . 'rating',
                'type'    => 'select',
                'options' => array(
                    '1' => __( '1', 'gpm' ),
                    '2' => __( '2', 'gpm' ),
                    '3' => __( '3', 'gpm' ),
                    '4' => __( '4', 'gpm' ),
                    '5' => __( '5', 'gpm' ),
                ),
                'default' => '4',
            ),
            array(
                'name' => 'Best Shooting Time',
                'desc' => '',
                'id' => $prefix . 'bst',
                'type' => 'text'
            ),
            array(
                'name' => 'Time of year',
                'desc' => '',
                'id' => $prefix . 'toy',
                'type' => 'text'
            ),
            array(
                'id'          => $prefix . 'all_pros',
                'type'        => 'group',
                'description' => __( 'Location Pros', 'gpm' ),
                'options'     => array(
                    //'group_title'   => __( 'Pro #{#}', 'gpm' ),
                    'group_title'   => '',
                    'add_button'    => __( 'Add Another Pro', 'gpm' ),
                    'remove_button' => __( 'Remove Pro', 'gpm' ),
                    'sortable'      => true,
                ),
                'fields'      => array(
                    array(
                        'name' => 'Pro',
                        'id'   => 'pro',
                        'type' => 'text',
                    ),
                ),
            ),
            array(
                'id'          => $prefix . 'all_cons',
                'type'        => 'group',
                'description' => __( 'Location Cons', 'gpm' ),
                'options'     => array(
                    'group_title'   => '',
                    'add_button'    => __( 'Add Another Con', 'gpm' ),
                    'remove_button' => __( 'Remove Con', 'gpm' ),
                    'sortable'      => true,
                ),
                'fields'      => array(
                    array(
                        'name' => 'Con',
                        'id'   => 'con',
                        'type' => 'text',
                    ),
                ),
            ),
            array(
                'name' => 'Description',
                'desc' => '',
                'id' => $prefix . 'description',
                'type' => 'wysiwyg'
            ),
            array(
                'id'          => $prefix . 'more_images',
                'type'        => 'group',
                'description' => __( 'Additional images', 'gpm' ),
                'options'     => array(
                    'group_title'   => __( 'Image #{#}', 'gpm' ),
                    'add_button'    => __( 'Add another image', 'gpm' ),
                    'remove_button' => __( 'Remove image', 'gpm' ),
                    'sortable'      => true,
                ),
                'fields'      => array(
                    array(
                        'name' => 'Image',
                        'desc' => 'Upload the image',
                        'id'   => 'image',
                        'type' => 'file',
                    ),
                    array(
                        'name' => 'Additional information',
                        'desc' => '',
                        'id' => $prefix . 'info',
                        'type' => 'textarea_code'
                    ),
                ),
            ),
            /*array(
                'id'          => $prefix . 'repeat_group',
                'type'        => 'group',
                'description' => __( 'Any additional images (optional)', 'gpm' ),
                'options'     => array(
                    'group_title'   => __( 'Image #{#}', 'gpm' ), // since version 1.1.4, {#} gets replaced by row number
                    'add_button'    => __( 'Add Another Image', 'gpm' ),
                    'remove_button' => __( 'Remove Image', 'gpm' ),
                    'sortable'      => true, // beta
                ),
                // Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
                'fields'      => array(
                    array(
                        'name' => 'Image Title',
                        'id'   => 'title',
                        'type' => 'text',
                        // 'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
                    ),
                    array(
                        'name' => 'Image',
                        'desc' => 'Upload the image',
                        'id'   => 'image',
                        'type' => 'file',
                    ),
                    array(
                        'name' => 'ISO',
                        'desc' => '',
                        'id' => $prefix . 'iso',
                        'type' => 'text'
                    ),
                    array(
                        'name' => 'Aperture',
                        'desc' => '',
                        'id' => $prefix . 'aperture',
                        'type' => 'text'
                    ),
                    array(
                        'name' => 'Shutter speed',
                        'desc' => '',
                        'id' => $prefix . 'speed',
                        'type' => 'text'
                    ),
                    array(
                        'name' => 'Additional information',
                        'desc' => '',
                        'id' => $prefix . 'info',
                        'type' => 'wysiwyg'
                    ),
                ),
            ),*/
        ),
    );

    return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'be_sample_metaboxes' );


// Add Settings to Maps menu
include_once __dir__.'/gpm-settings.php';

/**
 * Save post metadata when a post is saved.
 * @param int $post_id The ID of the post.
 */
function save_gmp_meta( $post_id ) {

    /*
     * In production code, $slug should be set only once in the plugin,
     * preferably as a class property, rather than in each function that needs it.
     */
    $slug = 'gpmaps';
    $prefix = PREFIX;

    // If this isn't a 'book' post, don't update it.
    if ( $slug != $_POST['post_type'] ) {
        return;
    }

    // - Update the post's metadata.

    if ( isset( $_POST[$prefix.'newlayer'] )  && !empty($_POST[$prefix.'newlayer']) ) {
        $term = wp_insert_term(
            $_POST[$prefix.'newlayer'], // the term 
            'Layers' //, // the taxonomy
//            array(
//              'description'=> 'A yummy apple.',
//              'slug' => 'apple',
//              'parent'=> $parent_term_id
//            )
        );
        $term_info = get_term_by('id',$term['term_id'],'Layers');
        $_POST[$prefix.'layer'] = $term_info->slug;
        
        //update_post_meta( $post_id, 'book_author', sanitize_text_field( $_REQUEST['book_author'] ) );
    }
    unset($_POST[$prefix.'newlayer']);
    return;
}
add_action( 'save_post', 'save_gmp_meta' );


/****************************************************/
/* Front End
 */

add_shortcode( 'cmb-form', 'cmb_do_frontend_form' );
/**
 * Shortcode to display a CMB form for a post ID.
 * @param  array  $attr Metabox config array
 * @return string       Form HTML markup
 */
function cmb_do_frontend_form( $attr = array() ) {
    // Make sure a WordPress post ID is specified
    if ( ! isset( $attr['id'] ) )
        return __( "Please add an 'id' attribute to the shortcode.", 'cmb' );

    // Default metabox id
    $metabox_id = 'test_metabox';

    // Let shortcode override metabox id
    if ( isset( $attr['metabox_id'] ) ) {
        $metabox_id = esc_attr( $attr['metabox_id'] );
    }

    // Get all metaboxes
    $meta_boxes = apply_filters( 'cmb_meta_boxes', array() );

    // If the metabox specified doesn't exist, yell about it.
    if ( ! isset( $meta_boxes[ $metabox_id ] ) )
        return __( "A metabox with the specified 'metabox_id' doesn't exist.", 'cmb' );

    // This is the WordPress post ID where the data should be stored/displayed.
    $object_id = absint( $attr['id'] );
    
    $object_id = "";
    
    if (!isset($object_id) || empty($object_id)) {
        $new_id = intercept_post_id();
        if ( $new_id ) {
            var_dump($new_id);
            $object_id = $new_id;
        }
        else {
            echo "hoho";
            var_dump($new_id);
        }
    }

    // Shortcodes need to return their data, not echo it.
    $echo = false;

    // Get our form
    $form = cmb_metabox_form( $meta_boxes[ $metabox_id ], $object_id, $echo );
    
    echo $form;
}

function intercept_post_id() {
    $prefix = PREFIX;
    // Check for $_POST data
    if (empty($_POST)) {
        return false;
    }

    // Check nonce
    if (!( isset($_POST['submit-cmb'], $_POST['wp_meta_box_nonce']) && wp_verify_nonce($_POST['wp_meta_box_nonce'], cmb_Meta_Box::nonce()) )) {
        return;
    }

    // Setup and sanitize data
    if (isset($_POST[$prefix . 'title'])) {

        //add_filter('user_has_cap', array($this, 'grant_publish_caps'), 0, 3);

        $new_submission = wp_insert_post(array(
            'post_title' => implode(' ', array_slice(explode(' ', sanitize_text_field($_POST[$prefix . 'info'])), 0, 4)),
            'post_author' => get_current_user_id(),
            'post_status' => 'draft', // Set to draft so we can review first
            'post_type' => 'gpmaps',
            //'post_content_filtered' => wp_kses($_POST[$prefix . 'memorial_story'], '<b><strong><i><em><h1><h2><h3><h4><h5><h6><pre><code><span>'),
        ), true);

        // If no errors, save the data into a new post draft
        if (!is_wp_error($new_submission)) {
            return $new_submission;
        }
    }

    return false;
}


// Get single map via AJAX
add_action( 'wp_ajax_nopriv_GetSingleMap', 'GetSingleMapAJAX' );
add_action( 'wp_ajax_GetSingleMap', 'GetSingleMapAJAX' );

function GetSingleMapAJAX() {
    $dir = plugin_dir_path( __FILE__ );
    $post_types = array( 'gpmaps' );
    $template = get_stylesheet_directory() . '/single-gpmaps-ajax.php';

    if ( ! file_exists( get_stylesheet_directory() . '/single-gpmaps-ajax.php' ) )
        $template = $dir.'templates/single-gpmaps-ajax.php';
    
    include_once $template;
    die();
}

