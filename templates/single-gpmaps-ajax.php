<?php
    $prefix = '_gpm_';
//    $post = get_post($_POST['id']);
//    var_dump($post);
//    return;
    $the_query = new WP_Query('post_type=gpmaps&page_id='.(int)$_POST['id']);

    function get_attachment_id_from_src ($image_src) {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var($query);
        return $id;
    }
    
?>

<div id="primary" class="map-content">
    <div id="content" role="main">

        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

            <?php //get_template_part( 'content', get_post_format() );  ?>

            <?php
            // The real stuff goes here:
            $title = get_the_title();
            $picture = get_post_meta(get_the_ID(), $prefix . 'file', true);
            $location = get_post_meta(get_the_ID(), $prefix . 'location', true);
            $bst = get_post_meta(get_the_ID(), $prefix . 'bst', true);
            $toy = get_post_meta(get_the_ID(), $prefix . 'toy', true);
            $rating = get_post_meta(get_the_ID(), $prefix . 'rating', true);
            $description = wpautop(get_post_meta(get_the_ID(), $prefix . 'description', true));
            ?>

            <h1 class='gpm_title'><?php echo $title; ?></h1>
            <ul class="rslides">
                <li><img src="<?php echo $picture; ?>" class='gpm_image' alt="<?php echo $title; ?>" /></li>
                <?php
                $images = get_post_meta(get_the_ID(), $prefix . 'more_images', true);

                foreach ($images as $key => $image) {
                    echo "<li><img src='" . $image['image'] . "' class='gpm_image' alt='" . $title . "' /></li>";
                    $image_info[] = $image[$prefix.'info'];
                }
                ?>
            </ul>
            <div class='site-content gpm_desctiption'>
                                
                <ul class="rslides-pager">
                    <?php 
                        $iid = get_attachment_id_from_src($picture);
                        $ig = wp_get_attachment_image_src($iid,'thumbnail');
                    ?>
                    <li><a href="#"><img style="width:<?php echo $ig[1] ?>;height:<?php echo $ig[2] ?>;" src="<?php echo $ig[0] ?>" class='gpm_image' alt="<?php echo $title; ?>" /></a></li>
                    <?php
                    foreach ($images as $key => $image) {
                        $iid = get_attachment_id_from_src($image['image']);
                        $ig = wp_get_attachment_image_src($iid,'thumbnail');
                        echo "<li><a href='#'><img style='width:{$ig[1]};height:{$ig[2]};' src='{$ig[0]}' class='gpm_image' alt='" . $title . "' /></a></li>";
                    }
                    ?>
                </ul>
                
                <div class="clear"></div>
                
                <h2 class='gpm_desc_title'><?php _e('Description:', 'gpm'); ?></h2>
                <div class='gpm_desc_info' id="desc-0"><?php echo $description; ?></div>
                <?php 
                    $id = 1;
                    foreach ($images as $key => $image) {
                        echo "<div class='gpm_desc_info' style='display:none;' id='desc-$id'>{$image[$prefix.'info']}</div>";
                        $id++;
                    }
                ?>
            </div>
            <div class='widget-area gpm_camera_info'>
                <div class='gpm_pros_cons'>
                    <?php
                        $pros = get_post_meta(get_the_ID(), $prefix . 'all_pros', true);
                        if (!empty($pros)) {
                            echo "<div class='gpm_all_pros'><h2 class='gpm_sub_desc_title'>" . __('Pros:', 'gpm') . "</h2><ul class='gpm_pros'>";
                        }

                        foreach ($pros as $key => $entry) {
                            echo "<li>" . $entry['pro'] . "</li>";
                        }

                        if (!empty($pros)) { echo "</ul></div>"; }
                        
                        $cons = get_post_meta(get_the_ID(), $prefix . 'all_cons', true);
                        if (!empty($cons)) {
                            echo "<div class='gpm_all_cons'><h2 class='gpm_sub_desc_title'>" . __('Cons:', 'gpm') . "</h2><ul class='gpm_cons'>";
                        }

                        foreach ($cons as $key => $entry) {
                            echo "<li>" . $entry['con'] . "</li>";
                        }

                        if (!empty($cons)) { echo "</ul></div>"; }
                    ?>
                </div>
                <hr/>
                <p class='gpm_camera'><?php _e('Best shooting time:', 'gpm');
                    echo " " . $bst; ?></p>
                <p class='gpm_iso'><?php _e('Time of year:', 'gpm');
                    echo " " . $toy; ?></p>
                <p class='gpm_aperture'><?php _e('Rating:', 'gpm');
                    echo " " . $rating; ?></p>
                <!--https://maps.google.com?saddr=Current+Location&daddr=-->
                <!--<iframe width="100%" height="320" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>&hl=es;z=14&amp;output=embed"></iframe>-->
            </div>
            <div class="clear"></div>
            <div id="get-directions-div">
                <h2 class='gpm_desc_title'><?php _e('Directions','gpm'); ?></h2>
                <input id="fromtxt" type="text" value="<?php _e('Current Location','gpm'); ?>" onfocus="if(this.value=='<?php _e('Current Location','gpm') ?>'){this.value=''}" onfocus="if(this.value=='<?php _e('Current Location','gpm') ?>'){this.value=''}" onblur="if(this.value==''){this.value='<?php _e('Current Location','gpm') ?>'}" onkeydown="if(event.keyCode == 13){jQuery('#gobutton').click();};" />
                <button onclick="document.getElementById('gmsrc').src='https://maps.google.com/maps?saddr='+jQuery('#fromtxt').val()+'&daddr=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>&hl=en;z=14&amp;output=embed'" id="gobutton"><?php _e('Go','gpm'); ?></button>
                <br/><br/>
                <iframe id="gmsrc" width="100%" height="400px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?saddr=Current+Location&daddr=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>&hl=en;z=14&amp;output=embed"></iframe>

            </div>

    <?php global $withcomments;

        $withcomments = true; 
        comments_template('', true); ?>

<?php endwhile; // end of the loop. ?>

    </div><!-- #content -->
</div><!-- #primary -->

<?php wp_reset_postdata(); ?>