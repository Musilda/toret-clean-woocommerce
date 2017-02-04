<?php
/**

Plugin Name: Toret Clean WooCommerce
Description: This plugin will remove all products from a WooCommerce store.
Version: 1.0.0
Author: Vladislav Musilek
Author URI: http://toret.cz/
Text Domain: toret-clean-woocommerce
Domain Path: /languages

Copyright: 2017 Vladislav Musílek
License: GNU General Public License v2.0 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
	
**/

add_action( 'plugins_loaded', 'toret_clean_woocommerce_init' );

function toret_clean_woocommerce_init() {
	
	load_plugin_textdomain( 'toret-clean-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	add_action( 'admin_menu', 'toret_clean_woocommerce_admin_menu' );

}

function toret_clean_woocommerce_admin_menu() {
	
	if ( current_user_can( 'manage_woocommerce' ) ) {
	
		add_submenu_page('woocommerce',
			__( 'Clean WooCommerce', 'toret-clean-woocommerce' ),  
			__( 'Remove All Products', 'toret-clean-woocommerce' ) , 
			'manage_woocommerce', 
			'toret_clean_woocommerce_page', 
			'toret_clean_woocommerce_page'
		);

	}

}	
	
function toret_clean_woocommerce_page() {
	?>

	<div class="wrap">
  	<script>
            jQuery(document).ready(function() { 
        
                jQuery('.tcw_delete').on( 'click', function(e){
                    e.preventDefault();
                    jQuery('.delete_notice').empty()
                    jQuery('.delete_notice').append('<p><?php _e('Deleting products', 'toret-clean-woocommerce'); ?></p>');
                    tcw_delete_products();

                });

                jQuery('.tcw_delete_images').on( 'click', function(e){
                    e.preventDefault();
                    jQuery('.delete_notice').empty()
                    jQuery('.delete_notice').append('<p><?php _e('Deleting images', 'toret-clean-woocommerce'); ?></p>');
                    tcw_delete_images();

                });

                
                

                function tcw_delete_products() {
 
                    var data = {
                        action    : 'tcw_delete_products'
                    };

                    jQuery.post(ajaxurl, data, function(response) {

                        if( response != 'finish' ){
                            tcw_delete_products(); 
                            jQuery('.delete_notice').empty()
                    		jQuery('.delete_notice').append( response );
                        }else{
                            jQuery('.delete_notice').empty()
                    		jQuery('.delete_notice').append( '<p><?php _e('All done!', 'toret-clean-woocommerce'); ?></p>' );
                        }
                    });
            
                }


                function tcw_delete_images() {
 
                    var data = {
                        action    : 'tcw_delete_images'
                    };

                    jQuery.post(ajaxurl, data, function(response) {

                        if( response != 'finish' ){
                            tcw_delete_images(); 
                            jQuery('.delete_notice').empty()
                    		jQuery('.delete_notice').append( response );
                        }else{
                            jQuery('.delete_notice').empty()
                    		jQuery('.delete_notice').append( '<p><?php _e('All done!', 'toret-clean-woocommerce'); ?></p>' );
                        }
                    });
            
                }


               
               

            });
        </script>
  		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    	<div style="clear:both;"></div>  

			<?php
  
  				$products_count = 0;
  				foreach ( wp_count_posts( 'product' ) as $product )
					$products_count += $product;
  				foreach ( wp_count_posts( 'product_variation' ) as $variation )
					$products_count += $variation;
  
  			if ( ! $products_count ) {
				echo '<h2>' . __( 'No products found.', 'toret-clean-woocommerce') . '</h2>';
			} else {
				echo '<h2>' . sprintf(__( 'Found %s products.', 'toret-clean-woocommerce'), $products_count ) . '</h2>';
				echo '<br />';
				echo '<p>' . __( 'There is not warning before delete. All products will be removed', 'toret-clean-woocommerce') . '</p>';
				echo '<br />';
				echo '<p><a href="#" class="button button-primary tcw_delete">' . __( 'Delete all products.', 'toret-clean-woocommerce') . '</a></p>';
				
			}

			$images_count = 0;
  				foreach ( wp_count_posts( 'attachment' ) as $image )
					$images_count += $image;
  
  			if ( ! $images_count ) {
				echo '<h2>' . __( 'No attachments found.', 'toret-clean-woocommerce') . '</h2>';
			} else {
				echo '<h2>' . sprintf(__( 'Found %s attacments.', 'toret-clean-woocommerce'), $images_count ) . '</h2>';
				echo '<br />';
				echo '<p>' . __( 'There is not warning before delete. All attachments will be removed', 'toret-clean-woocommerce') . '</p>';
				echo '<br />';
				echo '<p><a href="#" class="button button-primary tcw_delete_images">' . __( 'Delete all images.', 'toret-clean-woocommerce') . '</a></p>';
				
			}
		
			?>

  		<div class="delete_notice"></div>
		<div style="clear:both;"></div> 
	</div>
	<?php
}



add_action( 'wp_ajax_tcw_delete_products', 'tcw_delete_products' );
function tcw_delete_products(){

        $args = array( 
				'post_type'   => array( 'product', 'product_variation' ),
				'post_status' => 'any',
				'numberposts' => 10, 
				);
		$products = get_posts( $args );

		if( empty( $products ) ){
			echo 'finish';
		}else{
			foreach( $products as $product ) {
				wp_delete_post( $product->ID, $force_delete = true );
			}

			$products_count = 0;
  				foreach ( wp_count_posts( 'product' ) as $product )
					$products_count += $product;
  				foreach ( wp_count_posts( 'product_variation' ) as $variation )
					$products_count += $variation;
		
			echo '<p>'.$products_count.' '.__( 'products remains','toret-clean-woocommerce' ).'</p>';

		}
        
        exit();

}

add_action( 'wp_ajax_tcw_delete_images', 'tcw_delete_images' );
function tcw_delete_images(){

        $args = array( 
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'numberposts' => 10, 
				);
		$images = get_posts( $args );

		if( empty( $images ) ){
			echo 'finish';
		}else{
			foreach( $images as $image ) {
				wp_delete_attachment( $image->ID, true );
			}

			$images_count = 0;
  				foreach ( wp_count_posts( 'attachment' ) as $img )
					$images_count += $img;
  				
			echo '<p>'.$images_count.' '.__( 'images remains','toret-clean-woocommerce' ).'</p>';

		}
        
        exit();

}

 
