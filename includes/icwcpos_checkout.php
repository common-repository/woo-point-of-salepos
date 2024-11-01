<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_CHECKOUT')){
	/*
	 * Class Name ICWCPOS_CHECKOUT
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_CHECKOUT extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			//clear_error_log();
			//error_log(print_r($_POST,true));
			
		}
		
		public function create_order( $data = array() ) {
			$order_id = 0;						
			$customer_user     = isset($_POST['customer_user']) 	 ? $_POST['customer_user']	  : 0;
			if($customer_user > 0){
				$user_meta =  get_user_meta($customer_user);
				$save_fields = $this->get_customer_meta_fields();			
				foreach ( $save_fields as $fieldset ) {		
					foreach ( $fieldset['fields'] as $key => $field ) {
						if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
							$data[$key] = '';
						}elseif(isset($user_meta[$key])){
							$meta_value =  (isset($user_meta[$key][0]) ? $user_meta[$key][0] : '');
							$data[$key] = isset($_POST[$key]) ? $_POST[$key] : $meta_value;							
							
						}
					}
				}
			}
			
			$_POST['_payment_method']  = isset($_POST['payment_method_id']) 		   ? $_POST['payment_method_id'] 			: '';
			$_POST['order_status']	= "wc-pending";
			
			do_action('ic_pos_before_create_order');
			
			do_action( 'woocommerce_before_checkout_process' );
			
			$cart_items 	    = isset($_POST['cart_items'])    	? $_POST['cart_items']   		 : array();
			$cart_fee   	      = isset($_POST['cart_fee']) 	  	  ? $_POST['cart_fee'] 	 	   : array();
			$cart_shippings    = isset($_POST['cart_shipping']) 	 ? $_POST['cart_shipping']	  : array();
			$custom_meta       = isset($_POST['custom_meta']) 	   ? $_POST['custom_meta']	    : array();
			$customer_user     = isset($_POST['customer_user']) 	 ? $_POST['customer_user']	  : 0;
			$item_meta   	 	 = isset($_POST['item_meta']) 	     ? $_POST['item_meta']	      : array();
			$order_note 	    = isset($_POST['order_note'])    	? $_POST['order_note']   		 : '';
			$order_total 	   = isset($_POST['order_total'])   	   ? $_POST['order_total']  		: 0;
			$payment_method 	= isset($_POST['payment_method_id']) ? $_POST['payment_method_id']  : '';			
			$_POST['country']  = isset($data['billing_country']) 		   ? $data['billing_country'] 			: '';
			$_POST['state'] 	= isset($data['billing_state']) 			 ? $data['billing_state'] 			  : '';
			$_POST['postcode'] = isset($data['billing_postcode']) 		  ? $data['billing_postcode'] 		   : '';
			$_POST['city'] 	 = isset($data['billing_city']) 			  ? $data['billing_city'] 			   : '';
			
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$payment_method = trim($payment_method);
			
			$shipping_disabled     = get_option( 'woocommerce_ship_to_countries' );
			
			$order_id = wp_insert_post(
				apply_filters(
					'woocommerce_new_order_data',
					array(
						'post_date'     => gmdate( 'Y-m-d H:i:s'),
						'post_date_gmt' => gmdate( 'Y-m-d H:i:s'),
						'post_type'     => 'shop_order',
						'post_status'   => 'wc-' . apply_filters( 'woocommerce_default_order_status', 'pending' ),
						'ping_status'   => 'closed',
						'post_author'   => 1,
						'post_title'    => $this->get_post_title(),
						'post_password' => uniqid( 'order_' ),
						'post_parent'   => 0,
						'post_excerpt'  => '',
					)
				), true
			);
			
			$order        = wc_get_order( $order_id );
			
			
			
			foreach ( $data as $key => $value ) {
				if ( is_callable( array( $order, "set_{$key}" ) ) ) {
					$order->{"set_{$key}"}( $value );

					// Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
					// TODO: Fix conditional to only include shipping/billing address fields in a smarter way without str(i)pos.
				} elseif ( ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) )&& ! in_array( $key, array( 'shipping_method', 'shipping_total', 'shipping_tax' ), true ) ) {
					$order->update_meta_data( '_' . $key, $value );
				}
			}
			
			$cart_hash          = md5(rand(9999,99999).$order_total);
			$order->set_created_via( '_ic_pos' );
			$order->set_cart_hash( $cart_hash );
			$order->set_customer_id( apply_filters( 'woocommerce_checkout_customer_id', $customer_user));
			$order->set_currency(get_woocommerce_currency());			
			$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
			$order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
			$order->set_customer_user_agent( wc_get_user_agent() );
			$order->set_customer_note($order_note);
			$order->set_payment_method(isset($available_gateways[$payment_method] ) ? $available_gateways[$payment_method] : $payment_method);
			
			//$order->set_shipping_total( WC()->cart->get_shipping_total() );
			//$order->set_discount_total( WC()->cart->get_discount_total() );
			//$order->set_discount_tax( WC()->cart->get_discount_tax() );
			//$order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
			//$order->set_shipping_tax( WC()->cart->get_shipping_tax() );
			
			$order->set_order_key( 'wc_' . apply_filters( 'woocommerce_generate_order_key', uniqid( 'order_' ) ) );
			$order->set_total($order_total);
			
			
			do_action( 'woocommerce_checkout_create_order', $order, $data );
			
			// Save the order.
			$order_id = $order->save();
			
			do_action( 'woocommerce_checkout_update_order_meta', $order_id, $data );
			
			do_action( 'woocommerce_process_shop_order_meta', $order_id, $data );
			
			$calculate_tax_args = array();
			$calculate_tax_args['country']  = isset($_POST['tax_country'])  ? strtoupper(wc_clean($_POST['tax_country']))  : '';
			$calculate_tax_args['state']    = isset($_POST['tax_state']) 	? strtoupper(wc_clean($_POST['tax_state']))    : '';
			$calculate_tax_args['postcode'] = isset($_POST['tax_postcode']) ? strtoupper(wc_clean($_POST['tax_postcode'])) : '';
			$calculate_tax_args['city']     = isset($_POST['tax_city']) 	 ? strtoupper(wc_clean($_POST['tax_city']))     : '';
			
			/*Add order items*/
			$new_custom_meta = array();
			$i = 0;
			foreach($custom_meta as $meta_data){
				if($meta_data['meta_value'] > 0){
					$new_custom_meta[$meta_data['post_id']][$i]['meta_key']   = 'employee_id';
					$new_custom_meta[$meta_data['post_id']][$i]['meta_value'] = $meta_data['meta_value'];
					$i = $i +1;
					
					$new_custom_meta[$meta_data['post_id']][$i]['meta_key']   = 'employee_name';
					$new_custom_meta[$meta_data['post_id']][$i]['meta_value'] = $meta_data['meta_title'];
					$i = $i +1;
				}
			}
			
			foreach($item_meta as $meta_data){				
				$new_custom_meta[$meta_data['post_id']][$i]['meta_key']   = $meta_data['meta_key'];
				$new_custom_meta[$meta_data['post_id']][$i]['meta_value'] = $meta_data['meta_value'];
				$i = $i +1;
			}
			
			$prices_include_tax    = get_option( 'woocommerce_prices_include_tax' );
			foreach($cart_items as $cart_item){
								
				$item_to_add   = isset($cart_item['post_id']) 	   ? $cart_item['post_id'] 	   : 0;
				$quantity 	  = isset($cart_item['quantity']) 	  ? $cart_item['quantity'] 	  : 1;
				$line_subtotal = isset($cart_item['line_subtotal']) ? $cart_item['line_subtotal'] : 0;
				$line_total 	= isset($cart_item['line_total']) 	? $cart_item['line_total'] 	: 0;				
				
				$product 	   = wc_get_product($item_to_add);
				
				if($prices_include_tax == 'yes'){
					$args = array(
						//'subtotal'     => wc_get_price_excluding_tax( $product, array( 'qty' => $quantity,'price' => ($line_subtotal/$quantity))),
						//'total'        => wc_get_price_excluding_tax( $product, array( 'qty' => $quantity,'price' => ($line_total/$quantity)) ),						
						'subtotal'     => $line_subtotal,
						'total'        => $line_total,						
						'quantity'     => $quantity
					);
				}else{
					$args = array(
						//'subtotal'     => wc_get_price_including_tax( $product, array( 'qty' => $quantity,'price' => ($line_subtotal/$quantity))),
						//'total'        => wc_get_price_including_tax( $product, array( 'qty' => $quantity,'price' => ($line_total/$quantity)) ),
						'subtotal'     => $line_subtotal,
						'total'        => $line_total,
						'quantity'     => $quantity
					);				
				}
				
				
				$item_id = $order->add_product($product,$quantity, $args);
				if($item_id){
					if(isset($new_custom_meta[$item_to_add])){
						foreach($new_custom_meta[$item_to_add] as $post_id => $meta_data){
							wc_add_order_item_meta( $item_id, $meta_data['meta_key'], $meta_data['meta_value'] );
						}
					}
				}
			}
			//$order = wc_get_order( $order_id );
			//$order->calculate_taxes( $calculate_tax_args );
			//$order->calculate_totals( false );
			//$order->save();
			/*Add order items*/
			
			/*Add Shipping*/
			if($shipping_disabled != 'disabled'){
				foreach($cart_shippings as $cart_shipping){
					$amount = isset($cart_shipping['amount']) ? $cart_shipping['amount'] : 0;
					if($amount > 0){
						$amount = isset($cart_shipping['amount']) ? $cart_shipping['amount'] : 0;
						$name = isset($cart_shipping['name']) ? $cart_shipping['name'] : '';
						$title = isset($cart_shipping['title']) ? $cart_shipping['title'] : 0;
						
						// Add new shipping
						
						$item = new WC_Order_Item_Shipping();
						$item->set_shipping_rate( new WC_Shipping_Rate() );
						$item->set_order_id( $order_id );
						
						$item->set_props(
							array(
								'method_id'    => $name,
								'method_title' => $title,
								'total'        => $amount
							)
						);
						$item_id = $item->save();
					}
				}
				//$order = wc_get_order( $order_id );
				//$order->calculate_taxes( $calculate_tax_args );
				//$order->calculate_totals( false );
				//$order->save();			
			}
			/*End Shipping*/
			
			/*Add Fee*/
			foreach($cart_fee as $cartfee){
				$amount = isset($cartfee['amount']) ? $cartfee['amount'] : 0;
				if($amount > 0){
					if ( strstr( $amount, '%' ) ) {
						$formatted_amount = $amount;
						$percent          = floatval( trim( $amount, '%' ) );
						$amount           = $order->get_total() * ( $percent / 100 );
					} else {
						$amount           = floatval( $amount );
						$formatted_amount = wc_price( $amount, array( 'currency' => $order->get_currency() ) );
					}
		
					$fee = new WC_Order_Item_Fee();
					$fee->set_amount( $amount );
					$fee->set_total( $amount );
					$fee->set_name( sprintf( __( '%s fee', 'icwcpos'), $formatted_amount ) );
		
					$order->add_item( $fee );
				}
			}			
			$order->save();
			/*End Fee*/
			
			
			$order->set_shipping_total($order->get_shipping_total() );
			$order->set_discount_total($order->get_discount_total() );
			$order->set_discount_tax($order->get_discount_tax() );
			$order->set_shipping_tax($order->get_shipping_tax() );
			$order->save();
			
			
			// Grab the order and recalculate taxes			
			$order = wc_get_order( $order_id );
			$order->calculate_taxes( $calculate_tax_args );
			$order->calculate_totals( false );
			$order->save();
			
			
			/*Available Gateways*/
			$results = array();			
			if(isset($available_gateways[$payment_method])){
				$results = $available_gateways[$payment_method]->process_payment($order_id);				
				//error_log(print_r($results,true));
			}else{
				foreach($available_gateways as $key => $available_gateway){
					if($key == $payment_method){
						$results = $available_gateway->process_payment($order_id);				
					}
				}
				
			}
			
			$payment_result = isset($results['result']) ? $results['result'] : '';
			
			if($payment_result == 'success'){
				$order->set_date_paid( current_time( 'timestamp', true ) );
				$order->set_date_completed( current_time( 'timestamp', true ) );
				$message = __('IC POS Transaction completed.', 'icwcpos');
				$order->update_status('wc-completed', $message );
			}
			
			$order->add_order_note(__('order was added by woocommerce point of sale plugin','icwcpos'), false, true );
			
			if(isset($_SESSION['current_location_id'])){
				$current_location_id = $_SESSION['current_location_id'];
				add_post_meta($order_id,'_pos_location_id',$current_location_id,true);
				
				$pos_location_name = $_SESSION['current_location_name'];
				add_post_meta($order_id,'_pos_location_name',$pos_location_name,true);
				
				//$current_location_name = $_SESSION['current_location_name'];
				//add_post_meta($order_id,'pos_location_name',$current_location_name,true);
			}
			
			//if($customer_user > 0){
				//$user_meta =  get_user_meta($customer_user);
				//error_log(print_r($user_meta,true));
				
				//$save_fields = $this->get_customer_meta_fields();
			
				//foreach ( $save_fields as $fieldset ) {
		
					//foreach ( $fieldset['fields'] as $key => $field ) {
						//if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
							//add_post_meta( $order_id, '_'.$key, isset( $_POST[ $key ] ) );
						//}elseif(isset($user_meta[$key])){							
							//error_log(print_r($user_meta,true));							
							//$meta_value =  (isset($user_meta[$key][0]) ? $user_meta[$key][0] : '');
							
							//$test = update_post_meta($order_id, '_'.$key, $meta_value, true);
							
							//$test = update_post_meta($order_id, '_fdsafdasf', 'yes');
							
						//	error_log($order_id.', '.'_'.$key.', '.$meta_value.' '.$test);
						//}
					//}
				//}
			//}
			$receipt_content = '';
			$return = array();
			ob_start();
			$this->get_receipt_content($order);
			$receipt_content = ob_get_contents();
			ob_end_clean();
			
			$receipt_id = $order->get_order_key();
			//error_log($receipt_id);			
			$return['receipt_content'] = $receipt_content;
			$return['receipt_id'] = str_replace("wc_order_","",$receipt_id);
			echo json_encode($return);
			die;
		}
		
		function get_receipt_content_by_order_key($order_key = ''){
			$order_key = isset($_POST['receipt_id']) ? $_POST['receipt_id'] : $order_key;
			$order_key = "wc_order_".$order_key;
			$order_id = wc_get_order_id_by_order_key($order_key);
			$order = wc_get_order( $order_id );
			$this->get_receipt_content($order);
			die;
		}
		
		function get_receipt_content($order){
			require_once('icwcpos_front_thank_you_page.php');
			$ICWCPOS_FRONT_THANK_YOU_PAGE = new ICWCPOS_FRONT_THANK_YOU_PAGE($this->constants);
			$ICWCPOS_FRONT_THANK_YOU_PAGE->create_thank_you_page($order);
		}
		
		protected function get_post_title() {
			return sprintf( __( 'Order &ndash; %s', 'icwcpos'), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'icwcpos') ) );
		}		
	}/*End Class*/
}
