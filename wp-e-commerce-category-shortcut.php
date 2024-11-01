<?php
/**
  * Plugin Name: WP e-Commerce Category Shortcut
  * Description: WP e-Commerce Category Shortcut for WP e-Commerce uses information available within the Single Product template to display related Products  * that belong to the same Product Category or a specific category/tag as specified in the shortcode [related]. 
  * If the category doesn't exist it will revert to 'related products'.
  * Based on WP e-Commerce Related by Onnay Okheng
  * Version: 0.5.0
  * Author: QuorraDesign
  * Author URI: http://quorradesign.net/
  **/

function on_wpec_related_add_settings_page($page_hooks, $base_page) {
	$page_hooks[] = add_submenu_page($base_page,__('- Related Products'), __('- Related Products'), 9, 'wpec-related-products', 'on_wpec_panel');
	return $page_hooks;
}

add_filter('wpsc_additional_pages', 'on_wpec_related_add_settings_page', 10, 2);

/**
 * Function for displaying the related products
 *
 * @global type $post 
 */
function on_wpec_related($atts){
    global $post;

// get attributes from shortcode
	
	extract( shortcode_atts( array(
		'category' => '',
		'tag' => ''
	), $atts ) );
    
        $display_on     = get_option('on_wpec_display', 'Single Product');
    
        // checking if on single product
        if(!is_singular('wpsc-product')) return;
    
        // get related from produt category.
        $product_cat = wp_get_object_terms(wpsc_the_product_id(), 'wpsc_product_category');
        $product_tag = wp_get_object_terms(wpsc_the_product_id(), 'product_tag');
        
		// insert category from shortcode or calling on_wpec_related($atts) command in theme
        
		if ( $category != '' || $tag != '' ) {
				$cat_array_name_list[0] = $category;
				$tag_array_name_list[0] = $tag;
		} else {
			// cat in array
			foreach ($product_cat as $cat_item) {
				$cat_array_name_list[] = $cat_item->slug;
			}
			// tag in array
			foreach ($product_tag as $tag_item) {
				$tag_array_name_list[] = $tag_item->slug;
			}
		}
        		
        $number     = (get_option('on_wpec_number') == '')? 4: get_option('on_wpec_number');
        $title      = (get_option('on_wpec_title') == '')? 'Related Products': get_option('on_wpec_title');
			if ( $title  == 'blank' ){
				$title = '';
			};
        
		// check if a category or tag was requested (if so override related by), if both are entered we'll go back to what's chosen on the admin page
		if ( $tag != '' || $category != '' ) {
			if ( $tag != '' && $category != '' ) { // end user assigned both a category or a tag back to default behaviour
				$related_by = get_option('on_wpec_related_by', 'wpsc_product_category');
			} elseif ( $tag != '' ) { // end user assigned a tag - lets use it
				$related_by = 'product_tag';
			} elseif ( $category != '' ) { // end user assigned a category - lets use it
				$related_by = 'wpsc_product_category';
			};
		} else { // nothing assigned on load, use whats set in the back end
			$related_by = get_option('on_wpec_related_by', 'wpsc_product_category');
		}
	
        if($related_by == 'wpsc_product_category'){
            $tax    = 'wpsc_product_category';
            $terms  = $cat_array_name_list;
        }else{
            $tax    = 'product_tag';
            $terms  = $tag_array_name_list;            
        }

        if (empty($related_product)) {
             $query = array (
                'showposts' => $number,
                'orderby'   => 'rand',
                'post_type' => 'wpsc-product',
                'tax_query' => array(
                        array(
                                'taxonomy'  => $tax,
                                'field'     => 'slug',
                                'terms'     => $terms
                        )
                ),
                'post__not_in' => array ($post->ID),
            );
            $related_product = new WP_Query($query);

            if(!$related_product->have_posts()){

                 $query = array (
                    'showposts' => $number,
                    'orderby'   => 'rand',
                    'post_type' => 'wpsc-product',
                    'post__not_in' => array ($post->ID),
                );
                $related_product = new WP_Query($query);
            }

            if($related_product->have_posts()):
                
                echo "<div class='wpec-related-wrap'>";
            
                echo "<h2>".$title."</h2>";
                
                while($related_product->have_posts()) : $related_product->the_post();
            ?>

                    <div class="wpec-related-product product-<?php echo wpsc_the_product_id(); ?> <?php echo wpsc_category_class(); ?>">

                        <?php if(get_option('on_wpec_image') == 'on') : ?>
                            <div class="wpec-related-image" id="related-pro-<?php echo wpsc_the_product_id(); ?>">
                                    <a href="<?php echo wpsc_the_product_permalink(); ?>">

                                        <?php if(wpsc_the_product_thumbnail()) : ?>
                                                    <img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(100, 100); ?>"/>
                                        <?php else: ?>
                                                    <img class="no-image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="No Image" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo WPSC_CORE_THEME_URL; ?>wpsc-images/noimage.png" width="100" height="100" />	
                                        <?php endif; ?>
                                    </a>
                            </div><!--close imagecol-->
                        <?php endif; ?>

                            <h3 class="wpec-related-title">
                                    <?php if(get_option('hide_name_link') == 1) : ?>
                                            <?php echo wpsc_the_product_title(); ?>
                                    <?php else: ?> 
                                            <a class="wpsc_product_title" href="<?php echo wpsc_the_product_permalink(); ?>"><?php echo wpsc_the_product_title(); ?></a>
                                    <?php endif; ?>
                            </h3>


                        <?php if(get_option('on_wpec_price') == 'on') : ?>
                            <div class="product-info">
                                    <div class="pricedisplay <?php echo wpsc_the_product_id(); ?>"><?php _e('Price', 'wpsc'); ?>: <span id='product_price_<?php echo wpsc_the_product_id(); ?>' class="currentprice pricedisplay"><?php echo wpsc_the_product_price(); ?></span></div>
                            </div>
                        <?php endif; ?>

                    </div><!-- close default_product_display -->

<?php
                endwhile;
                
                echo "</div><div class='clear'></div>";
                
            endif;
            wp_reset_postdata();
        }
        
}

// add the shortcode [related]

add_shortcode('related', 'on_wpec_call');

// put through output buffer so shortcode placement works properly

function on_wpec_call($atts) {
	ob_start();
		on_wpec_related($atts);
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
};

/**
 * This is style for related product, default.
 */
function on_wpec_related_style(){
?>
        <style>
            .wpec-related-wrap{margin: 20px 0; padding: 0; display: inline-block;}
            .wpec-related-product{float: left; padding: 0 3px; width: 110px;}
            .wpec-related-title{margin:0 !important;}
        </style>
                    
<?php
}


/**
 * init, first time call the plugin.
 */
function on_wpec_related_init(){
    if(!is_admin()){
        $place_related  = get_option('on_wpec_place', 'wpsc_product_addon_after_descr');
        $display_on     = get_option('on_wpec_display', 'Single Product');
        
        // adding style on header
        add_action('wp_head','on_wpec_related_style');
        
        // hoon into wpec page
        if($display_on != 'Manual') {
            add_action($place_related, 'on_wpec_related');
		}
    }
}
add_action('init', 'on_wpec_related_init');

/**
 * Function for display the Plugin Panel Options.
 */
function on_wpec_panel() { ?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2><?php _e('WP e-Commerce Category Shortcuts Options', 'quorradesign'); ?></h2>
    
    <form method="post" action="options.php" id="options" style="float: left;">
    <?php wp_nonce_field('update-options') ?>
                
        <table class="form-table">
            <tbody>

                <tr valign="top">
                    <th scope="row"><?php _e('Title', 'quorradesign'); ?></th>
                    <td>
                            <input type="text" name="on_wpec_title" placeholder="Your title here" value="<?php echo get_option('on_wpec_title'); ?>" />
                            <br/><?php _e('Default is "Related Products", put "blank" to disable title.', 'quorradesign'); ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Number of related products', 'quorradesign'); ?></th>
                    <td>
                            <input type="text" name="on_wpec_number" placeholder="Number of products" value="<?php echo get_option('on_wpec_number'); ?>" />
                            <br/><?php _e('Default is 4.', 'quorradesign'); ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Show image', 'quorradesign'); ?></th>
                    <td>                        
                            <?php $checked_image = (get_option('on_wpec_image') == 'on') ? ' checked="yes"' : ''; ?>                    
                            <label id="on_wpec_image" ><input type="checkbox" id="on_wpec_image" name="on_wpec_image"<?php echo $checked_image; ?> /> Enabled / Disabled</label>                                    
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Show price', 'quorradesign'); ?></th>
                    <td>                        
                            <?php $checked_price = (get_option('on_wpec_price') == 'on') ? ' checked="yes"' : ''; ?>                    
                            <label id="on_wpec_price" ><input type="checkbox" id="on_wpec_price" name="on_wpec_price"<?php echo $checked_price; ?> /> Enabled / Disabled</label>                                    
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Related by', 'quorradesign'); ?></th>
                    <td>
                        <?php $related_array  = array('wpsc_product_category', 'product_tag'); ?>
                        <?php $related        = get_option('on_wpec_related_by', 'wpsc_product_category'); ?>
                        <select name="on_wpec_related_by">
                        <?php 
                            foreach($related_array as $item):
                                $selected = ($related == $item)? ' selected="selected"':'';
                                echo '<option'.$selected.'>'.$item.'</option>';
                            endforeach;
                        ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Placement products', 'quorradesign'); ?></th>
                    <td>
                        <?php $place_array  = array('wpsc_product_before_description', 'wpsc_product_addons', 'wpsc_product_addon_after_descr', 'wpsc_theme_footer'); ?>
                        <?php $place        = get_option('on_wpec_place', 'wpsc_product_addon_after_descr'); ?>
                        <select name="on_wpec_place">
                        <?php 
                            foreach($place_array as $item):
                                $selected = ($place == $item)? ' selected="selected"':'';
                                echo '<option'.$selected.'>'.$item.'</option>';
                            endforeach;
                        ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Display on', 'quorradesign'); ?></th>
                    <td>
                        <?php $display_array  = array('Single Product', 'Manual'); ?>
                        <?php $display        = get_option('on_wpec_display', 'Single Product'); ?>
                        <select name="on_wpec_display">
                        <?php 
                            foreach($display_array as $item):
                                $selected = ($display == $item)? ' selected="selected"':'';
                                echo '<option'.$selected.'>'.$item.'</option>';
                            endforeach;
                        ?>
                        </select>
                        <?php _e('Put this code &lt;?php on_wpec_related() ?&gt;, or use the [related] shortcode, you can also use category="" or tag="" if "Manual".', 'quorradesign') ?>
                    </td>
                </tr>

            </tbody>
        </table>
        
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="on_wpec_image, on_wpec_number, on_wpec_title, on_wpec_price, on_wpec_related_by, on_wpec_place, on_wpec_display" />
        <div class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e('Save Settings', 'quorradesign'); ?>"/></div>

    </form>

</div>

<?php } ?>
