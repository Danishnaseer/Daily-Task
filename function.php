<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */
// Remove zxcvbn.min.js
//disable zxcvbn.min.js in wordpress
add_action('wp_print_scripts', 'remove_password_strength_meter');
function remove_password_strength_meter() {
    // Deregister script about password strenght meter
    wp_dequeue_script('zxcvbn-async');
    wp_deregister_script('zxcvbn-async');
}
 // Remove zxcvbn.min.js


// Sale Badge Remove on Frontstore
 add_filter('woocommerce_sale_flash', 'woo_custom_hide_sales_flash');
function woo_custom_hide_sales_flash()
{
return false;
}
// Set Addto Cart Button Order
remove_action( 'woocommerce_single_product_summary', 
'woocommerce_template_single_add_to_cart', 30 );
add_action( 'woocommerce_single_product_summary', 
'woocommerce_template_single_add_to_cart', 15 );
 // Catedgory page Layout set Description Move to Bottom

remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
add_action( 'woocommerce_after_main_content', 'woocommerce_taxonomy_archive_description', 100 );





//related Product Text
function custom_related_products_text( $translated_text, $text, $domain ) {
  switch ( $translated_text ) {
    case 'Related products' :
      $translated_text = __( 'Customer Also Bought Product', 'woocommerce' );
      break;
  }
  return $translated_text;
}

function get_the_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] )) 
	{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} 
	else 
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
return $ip;
}


add_filter( 'gettext', 'custom_related_products_text', 20, 3 );

// Product Page tabs Order Change
add_filter( 'woocommerce_product_tabs', 'woo_reorder_tabs', 98 );
function woo_reorder_tabs( $tabs ) {

// 	$tabs['reviews']['priority'] = 5;			// Reviews first
	// $tabs['description']['priority'] = 10;			// Description second
	//$tabs['additional_information']['priority'] = 15;	// Additional information third
	$tabs['description']['priority'] = 5;  
    $tabs['reviews']['priority'] = 10;           // Reviews first
            // Description second
    $tabs['additional_information']['priority'] = 15;   // Additional information third

	return $tabs;
}

//remove Addtional tab
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );


function woo_remove_product_tabs( $tabs ) {

   // unset( $tabs['description'] );      	// Remove the description tab
   // unset( $tabs['reviews'] ); 			// Remove the reviews tab
    unset( $tabs['additional_information'] );  	// Remove the additional information tab

    return $tabs;
}




//product Listing layout
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 11 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 13 );
add_action('woocommerce_email_order_details', 'before_email_order_details_transaction_id', 5, 4 );
function before_email_order_details_transaction_id( $order, $sent_to_admin, $plain_text, $email ) {

    if( $order->get_transaction_id() && $sent_to_admin )
        echo '<p><strong>' . __("Transaction id") . ': </strong>' . $order->get_transaction_id() . '<p>';
}
function bd_rrp_sale_price_html( $price, $product ) {
	
  if ( $product->is_on_sale() ) :
    $has_sale_text = array(
      '<del>' => '<span class="ret-pri">Retail Price:</span><del> ',
      '<ins>' => '<br> <ins><b>Sale Price: </b>'
    );
    $return_string = str_replace(array_keys( $has_sale_text ), array_values( $has_sale_text ), $price);
  else :
    $retun_string = 'Retail Price: ' . $price;
  endif;

  return $return_string;
}
// add_filter( 'woocommerce_get_price_html', 'bd_rrp_sale_price_html', 100, 2 );
//add_filter( 'woocommerce_get_price_html', 'show_sale_percentage_loop', 12 );
 add_action( 'woocommerce_before_shop_loop_item_title', 'show_sale_percentage_loop', 12 );


//add_action( 'woocommerce_before_get_price_html', 'show_sale_percentage_loop', 12 );
function show_sale_percentage_loop() {
global $product;
	
	if ( $product->is_on_sale() ) {
	 
		if ( ! $product->is_type( 'variable' ) ) {
			 
			$max_percentage = ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100;
			 
			} else {
			 
			$max_percentage = 0;
			 
			foreach ( $product->get_children() as $child_id ) {
				$variation = wc_get_product( $child_id );
				$price = $variation->get_regular_price();
				$sale = $variation->get_sale_price();
				if ( $price != 0 && ! empty( $sale ) ) $percentage = ( $price - $sale ) / $price * 100;
				if ( $percentage > $max_percentage ) {
					$max_percentage = $percentage;
				}
			}
		 
		}
		 
	//	echo "<div class='sale-perc'>You Save: " . round($max_percentage) . "%</div>";
		echo "<div class='sale-perc' style='font-size: 12px!important;
    color: #ffffff!important;
    font-weight: 500!important;
    background: #494949;width: 135px;
    text-align: center;
    position: relative;
    line-height: 1.5;
    margin: 5px auto;'>" . round($max_percentage) . "% OFF APPLIED</div>";
	 
	}
 
}

//product Listing layout

add_action('woocommerce_order_status_processing', 'custom_processing');
function custom_processing($order_id) {
    if (is_admin()) {
        //header('Location: http://www.google.com');
    } else {
        $order_id; // do whatever you want
        //$order = wc_get_order( $order_id );
        $contents = new WC_Order($order_id);
		$items = $contents->get_items();
		
		$order = wc_get_order( $order_id );
   		$coupons = $order->get_used_coupons();
		
		foreach ( $coupons as $coupon ) {
			$coupon_obj = new WC_Coupon( $coupon );
			$coupon_discount_type = $coupon_obj->get_discount_type();

        	// Do whatever you want with the coupon details
    	}


// 		$transactionID = $contents->get_transaction_id();
		//$fp = fopen('/home/customer/www/fanjackets.com/public_html/app/test.log', 'a');
		//echo "<pre>" . $res . "<pre>";
		//$res = "Contents: " . print_r($contents , true);
		//fwrite($fp, $res);
		//fclose($fp);
		include '/home/customer/www/fanjackets.com/public_html/app/getset.php';
		//$res = $contents;
		////$headers = "MIME-Version: 1.0" . "\r\n";
		//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		//$headers .= "From: API Order Axfashions <noreply@axfashions.com>";
    	//mail("borntoaskquestion@gmail.com","Axfashions TEST",$res,$headers,'-fdailyorder@axfashions.com');
	
    }
   
}


add_action( 'woocommerce_before_shop_loop_item_title', 'show_variation_sku_underneath_product_title' );
function show_variation_sku_underneath_product_title() {

    global $product; if ( $product->is_type('variable') ) {
        ?>
   <script>
        jQuery(document).ready(function($) {     
            $('input.variation_id').change( function(){
				
                if( '' != $('input.variation_id').val() ) {
					

                    jQuery.ajax( {

                        url: '<?php echo admin_url( 'admin-ajax.php'); ?>',
                        type: 'post',
                        data: { 
                            action: 'get_variation_sku', 
                            variation_id: $('input.variation_id').val()
                        },
                        success: function(data) {
							$('div.early').siblings('.variation-sku').remove();
//                             $('div.ks-calculator-container').siblings('.variation-sku').remove();
                            if(data.length > 0) {
// 								$('div.ks-calculator-container').after('<p class="variation-sku" style="color:#717171">' + data + '</p>');
								$('div.early').after('<p class="variation-sku" style="color:#717171">' + data + '</p>');
                            }
                        }
                    });

                }
            });
        });
        </script>
  <?php
    }
}
add_action('wp_ajax_get_variation_sku' , 'get_variation_sku');
add_action('wp_ajax_nopriv_get_variation_sku','get_variation_sku');
function get_variation_sku() {
    $variation_id = intval( $_POST['variation_id'] );
    $sku = '';
    if ( $product = wc_get_product( $variation_id ) ) $sku = $product->get_sku();
    echo 'SKU :'.$sku;
    wp_die(); // this is required to terminate immediately and return a proper response
}

add_filter('comment_flood_filter', '__return_false');

add_filter( 'woocommerce_cart_item_name', 'ywp_product_image_on_checkout', 10, 3 );
function ywp_product_image_on_checkout( $name, $cart_item, $cart_item_key ) {
     
    if ( ! is_checkout() )
        return $name;

    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
 
    $thumbnail = $_product->get_image();
 
    $image = '<div class="ywp-product-image" style="width: 120px;margin-right: 5%;">'
                . $thumbnail .
            '</div>'; 

    return $image . $name;
}


function show_pro_cat() {
    $cart_items = WC()->cart->get_cart();
	$catname = [];
	$procount = 0;
	foreach ( $cart_items as $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product_categories = wp_get_post_terms( $product_id, 'product_cat' );
		foreach( $product_categories as $category ) {
			$categoryname = $category->name;
			if($categoryname == 'Express Delivery')
			{
				$catname[] = $category->name;
			}
		}
		$procount++;
    }
	return array($catname,$procount);
}

function filter_woocommerce_cart_shipping_method_full_label( $label, $method ) {
		$Categories = show_pro_cat();
		$ProductCat = $Categories[0];
		$ProductCount = $Categories[1];
		$CountCat = count($ProductCat);
		if($CountCat == $ProductCount)
		{
			if (in_array("Express Delivery", $ProductCat))
			{
				$label = str_replace( '4 - 8 Days', '3 Days Delivery', $label );
			}
			else{
				$label = $label;
			}
		}
		else{
			$label = $label;
		}
		return $label;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'filter_woocommerce_cart_shipping_method_full_label', 10, 2 );









// TO hide the customer also bi
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

add_action( 'woocommerce_after_single_product_summary', 'related_upsell_products', 15 );

function related_upsell_products() {
    // Do not display upsell section
}

 
  
 
 

 

