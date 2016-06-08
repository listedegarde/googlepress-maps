<?php
/**
 * The template for displaying GPMaps Archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * @link http://
 *
 * @package WordPress
 * @subpackage GooglePress_Maps
 * @since GooglePress Maps 1.0
 */
$prefix = '_gpm_';
get_header();


$dir = plugin_dir_path( __FILE__ );
$pindir = get_stylesheet_directory() . '/images/';

if ( !file_exists( get_stylesheet_directory() . '/images/pin.png' ) )
    $pindir = plugin_dir_url( __FILE__ ).'images/';

$options = get_option( 'gpm_options' );

wp_enqueue_script( 'responsive_slides', plugin_dir_url( __FILE__ ) . 'js/responsiveslides.min.js', array(), null );

?>

<section id="primary" class="map-archive-content">
    <div id="content" role="main">

        <?php if (have_posts()) : ?>

            <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
            <script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ); ?>js/google-maps.js"></script>

            <div style="overflow:hidden;height:500px;width:100%;">
                <div id="gmap_markers_list" style="float:left;height:500px;width:20%;overflow:auto;">
                    <ul id="marker_list"></ul>
                </div>
                <div id="gmap_canvas" style="float:left;height:500px;width:80%;"></div>
                <style>#gmap_canvas img{max-width:none!important;background:none!important}</style>
                <div class="clear"></div>
            </div>
            <div id="content-info"></div>

            <script type="text/javascript">
                var gmarkers = [];
                var icon1 = "<?php echo $pindir; ?>pin.png";
                var icon2 = "<?php echo $pindir; ?>pinh.png";
                function init_map() {
                    var locations = [
                        <?php
                        /* Start the Loop */
                        $i = 0;
                        while (have_posts()) : the_post();
                            $i++;
                            $title = get_the_title();
                            $author = get_the_author();
                            $taxx = get_the_terms($post->ID, 'Layers');
                            $taxs = array();
                            // Yeah, I know it will only show the last one, but there should
                            // only be one Layer per pin.
                            foreach ($taxx as $tax) {
                                $taxs = $tax;
                            }
                            $picture = get_post_meta(get_the_ID(), $prefix . 'file', true);
                            $location = get_post_meta(get_the_ID(), $prefix . 'location', true);
                            $info = wpautop(get_post_meta(get_the_ID(), $prefix . 'info', true));
                            echo "['<h2>$title</h2><img src=\"$picture\" style=\"width:100%;height:auto;\" alt=\"$title\" />"
                                    . "<a href=\"".get_permalink(get_the_ID())."\">".__('More info','gpm')."</a><br/>"
                                    . "<a href=\"https://maps.google.com?saddr=Current+Location&daddr={$location['latitude']},{$location['longitude']}\" target=\"_blank\">".__('Get directions','gpm')."</a>"
                                    . "', {$location['latitude']}, {$location['longitude']}, '".get_the_ID()."', '$title',".  json_encode($taxs).", '$author'],";
                        endwhile;
                        
                        // Could be useful for sorting lists, prn:
                        // http://stackoverflow.com/questions/15314872/group-and-count-html-elements-by-data-attribute-in-jquery
                        ?>
                    ];
                    var myOptions = {
                        zoom: 14,
                        center: new google.maps.LatLng(40.805478, -73.96522499999998),
                        mapTypeId: google.maps.MapTypeId.<?php echo isset($options['map_type']) ? $options['map_type'] : "ROADMAP"; ?>
                    };
                    map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);

                    infoWindow = new google.maps.InfoWindow();
                    var bounds = new google.maps.LatLngBounds();
                    var marker, i;

                    for (i = 0; i < locations.length; i++) {
                        var position = new google.maps.LatLng(locations[i][1], locations[i][2]);
                        var marker = createMarker(position,locations[i]);

                        //extend the bounds to include each marker's position
                        bounds.extend(position);
                    }

                    map.fitBounds(bounds);
                    
                    jQuery("#marker_list li").click(function(){
                        console.log(jQuery(this).attr("id"));
                        google.maps.event.trigger(gmarkers[jQuery(this).attr("id").split("gp-pin-").join("")], "click");
                    }).mouseover(function() {
                        google.maps.event.trigger(gmarkers[jQuery(this).attr("id").split("gp-pin-").join("")], "mouseover");
                    }).mouseout(function() {
                        google.maps.event.trigger(gmarkers[jQuery(this).attr("id").split("gp-pin-").join("")], "mouseout");
                    });
                }
                
                function createMarker(latlng,lc) {
                    var name  = lc[4];
                    var html  = lc[0];
                    var id    = lc[3];
                    var layer = lc[5];
                    var auth  = lc[6];
                    var contentString = "<div class='infoWindow'>"+html+"</div>";
                    var marker = new google.maps.Marker({
                        position: latlng,
                        map: map,
                        title: name,
                        label: name,
                        icon: icon1,
                        id:id
                    });
                    
                    gmarkers[id] = marker;
                    jQuery("#marker_list").append("<li id='gp-pin-"+id+"' data-layer='"+layer.name+"' data-author='"+auth+"'><span class='gp-pin'></span>"+name+"</li>");

                    google.maps.event.addListener(marker, 'click', function() {
//                        infoWindow.setContent(contentString); 
//                        infoWindow.open(map,marker);
                        
                        jQuery.ajax({
                            type:'POST',
                            url:'/wp-admin/admin-ajax.php',
                            type:'post',
                            data: {
                                // this is the action hook to call the handler "MyAjaxFunction"
                                action: 'GetSingleMap',
                                id:id // you can retrieve this using $_POST['id'];
                            },
                            success:function(data){
                                // returned data by wordpress
                                jQuery("#content-info").html(data);
                                // https://github.com/viljamis/ResponsiveSlides.js
                                jQuery(".rslides").responsiveSlides({
                                    auto:false,
                                    pager:true,
                                    manualControls:".rslides-pager",
                                    before:function(){
                                        jQuery(".gpm_desc_info").hide();
                                        jQuery("#desc-"+jQuery(".rslides_here").index()).show();
                                    }
                                });
                            }
                        });
                        // Markers on left:
                        jQuery("#marker_list .current").removeClass("current");
                        jQuery("#gp-pin-"+marker.id).addClass("current");
                    });
                    google.maps.event.addListener(marker, 'mouseover', function() {
                        marker.setIcon(icon2);
                        jQuery("#gp-pin-"+marker.id).addClass("active");
                    });
                    google.maps.event.addListener(marker, 'mouseout', function() {
                        marker.setIcon(icon1);
                        jQuery("#marker_list .active").removeClass("active");
                    });
                }
                google.maps.event.addDomListener(window, 'load', init_map);
            </script>

            <?php
            twentytwelve_content_nav('nav-below');
                else :
            ?>
                <?php get_template_part('content', 'none'); ?>
            <?php endif; ?>

    </div><!-- #content -->
</section><!-- #primary -->

<?php get_footer(); ?>
