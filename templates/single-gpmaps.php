<?php
/**
 * The Template for displaying all single maps
 *
 * @package WordPress
 * @subpackage GooglePress_Maps
 * @since GooglePress Maps 1.0
 */
$prefix = '_gpm_';
get_header();
?>

<div id="primary" class="map-content">
    <div id="content" role="main">

        <?php while (have_posts()) : the_post(); ?>

            <?php //get_template_part( 'content', get_post_format() );  ?>

            <?php
            // The real stuff goes here:
            $title = get_the_title();
            $picture = get_post_meta(get_the_ID(), $prefix . 'file', true);
            $location = get_post_meta(get_the_ID(), $prefix . 'location', true);
            $model = get_post_meta(get_the_ID(), $prefix . 'camera_model', true);
            $iso = get_post_meta(get_the_ID(), $prefix . 'iso', true);
            $aperture = get_post_meta(get_the_ID(), $prefix . 'aperture', true);
            $speed = get_post_meta(get_the_ID(), $prefix . 'speed', true);
            $info = wpautop(get_post_meta(get_the_ID(), $prefix . 'info', true));
            ?>

            <h1 class='gpm_title'><?php echo $title; ?></h1>
            <img src="<?php echo $picture; ?>" class='gpm_image' alt="<?php echo $title; ?>" />
            <div class='site-content gpm_desctiption'>
                <h2 class='gpm_desc_title'><?php _e('Description:', 'gpm'); ?></h2>
                    <?php echo $info; ?>

                <div class='gpm_additional_picutres'>
                    <?php
                    $entries = get_post_meta(get_the_ID(), $prefix . 'repeat_group', true);
                    if (!empty($entries)) {
                        echo "<h2 class='gpm_sub_desc_title'>" . __('Additional images:', 'gpm') . "</h2>";
                    }

                    foreach ((array) $entries as $key => $entry) {
                        //if ( isset( $entry['image'] ) ) {            
                        //	$sub_image = wp_get_attachment_image( $entry['image'], 'share-pick', null, array(
                        //		'class' => 'full',
                        //	) );
                        //}
                        // Do something with the data
                        echo "
                            <div class='gpm_sub_camera_info'>
                                    <img src='" . $entry['image'] . "' class='gpm_image' alt='" . $title . "' />
                                    <h3>" . esc_html($entry['title']) . "</h3>
                                    <p class='gpm_camera'>" . __('Camera:', 'gpm') . " " . $model . "</p>
                                    <p class='gpm_iso'>" . __('ISO:', 'gpm') . " " . $entry[$prefix . 'iso'] . "</p>
                                    <p class='gpm_aperture'>" . __('Aperture:', 'gpm') . " " . $entry[$prefix . 'aperture'] . "</p>
                                    <p class='gpm_shutter_speed'>" . __('Shutter speed:', 'gpm') . " " . $entry[$prefix . 'speed'] . "</p>
                                    " . wpautop($entry[$prefix . 'info']) . "
                            </div>
                            ";
                    }
                    ?>
                </div>
            </div>
            <div class='widget-area gpm_camera_info'>
                <p class='gpm_camera'><?php _e('Camera:', 'gpm');
                    echo " " . $model; ?></p>
                <p class='gpm_iso'><?php _e('ISO:', 'gpm');
                    echo " " . $iso; ?></p>
                <p class='gpm_aperture'><?php _e('Aperture:', 'gpm');
                    echo " " . $aperture; ?></p>
                <p class='gpm_shutter_speed'><?php _e('Shutter speed:', 'gpm');
                    echo " " . $speed; ?></p>
                <iframe width="100%" height="320" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>&hl=es;z=14&amp;output=embed"></iframe>
            </div>
            <div class="clear"></div>

    <?php comments_template('', true); ?>

<?php endwhile; // end of the loop. ?>

    </div><!-- #content -->
</div><!-- #primary -->

<?php get_footer(); ?>
