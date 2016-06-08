<?php

// http://codex.wordpress.org/Creating_Options_Pages

class GPMSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Maps"
        add_submenu_page('edit.php?post_type=gpmaps', 'GooglePress Maps Admin', 'Maps Settings', 'manage_options', 'my-setting-admin', array( $this, 'create_admin_page' ));
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'gpm_options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php _e('Map settings','gpm') ?></h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'my-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            'gpm_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            _('My Custom Settings','gpm'), // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'map_type', // ID
            'Map type', // Title 
            array( $this, 'map_type_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

//        add_settings_field(
//            'title', 
//            'Title', 
//            array( $this, 'title_callback' ), 
//            'my-setting-admin', 
//            'setting_section_id'
//        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['map_type'] ) )
            $new_input['map_type'] = sanitize_text_field( $input['map_type'] ); // absint( $input['map_type'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        return $new_input;
    }

    public function print_section_info()
    {
        _e('Enter your settings below:','GPM');
    }

    public function map_type_callback()
    {
        $return = '';
        $type = isset( $this->options['map_type'] ) ? esc_attr( $this->options['map_type']) : '';
        $types = array(
            "ROADMAP" => "Road map",
            "SATELLITE" => "Satellite",
            "HYBRID" => "Hybrid",
            "TERRAIN" => "Terrain",
        );
        $return .='<select id="map_type" name="gpm_options[map_type]">';
        foreach($types as $k => $t) {
            $selected = $k == $type ? 'selected="true"' : '';
            $return .= "<option value='$k' $selected>$t</option>";
        }
        $return .= '</select>';
        echo $return;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="gpm_options[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }
}

if( is_admin() )
    $gpm_settings_page = new GPMSettingsPage();

// Usage:
// 
// $options = get_option( 'gpm_options' )
// echo isset($options['map_type']) ? $options['map_type'] : '12';
//  