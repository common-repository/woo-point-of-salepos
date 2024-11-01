<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_TEMPLATE')){
	/*
	 * Class Name ICWCPOS_TEMPLATE
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_TEMPLATE extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			
			$this->constants = $constants;
			
			$permalink = get_option('wc_permalink_point_of_sale', '');
			$permalink = empty($permalink) ? 'point-of-sale' : $permalink;
			$regex = '^' . $permalink . '/?$';
			
			$this->regex = $regex;
		
			add_rewrite_tag('%point-of-sale%', '([^&]+)');
			add_rewrite_rule($regex, 'index.php?point-of-sale=1', 'top');
			add_filter('option_rewrite_rules',	 array($this, 'option_rewrite_rules'), 1);
			add_action('template_redirect',		array($this, 'template_redirect'),	 1);
			
			add_filter('show_admin_bar', array($this, 'show_admin_bar'));
		}
		
		/*
		* Make sure cache contains POS rewrite rule
		*
		* @param $rules
		* @return bool
		*/
		public function option_rewrite_rules( $rules ) {
			return isset( $rules[ $this->regex ] ) ? $rules : false;
		}
		
		function is_point_of_sale(){
			global $wp;
			//print_r($wp);die;
			if( isset( $wp->query_vars['point-of-sale'] ) && $wp->query_vars['point-of-sale'] == 1 ){
			  return true;
			}
			
			return false;
		}
		
		function show_admin_bar(){
			if($this->is_point_of_sale()){
				return false;
			}
			return true;
		}
		  
		/**
		* Output the POS template
		*/
		 public function template_redirect() {
			// check is pos
			
			if(!$this->is_point_of_sale()){
				return;
			}
		
			// check auth
			if ( !is_user_logged_in() ) {
			  add_filter( 'login_url', array( $this, 'login_url' ) );
			  auth_redirect();
			}
			
			
		
			// check privileges
			//if ( !current_user_can( 'access_ic_point_of_sale' ) )
			  /* translators: wordpress */
			  //wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		
			// disable cache plugins
			//$this->no_cache();
		
			// last chance before template is rendered
			//do_action( 'woocommerce_pos_template_redirect' );
		
			// add head & footer actions
			//add_action( 'woocommerce_pos_head', array( $this, 'head' ) );
			//add_action( 'woocommerce_pos_footer', array( $this, 'footer' ) );
			
			$assets_url 			= $this->constants['assets_url'];			
			$tax_rates 	  		 = array();
			$settings 	   		  = $this->get_settings();
			$shipping_methods	  = array();
			$payment_gateways	  = array();
			
			$employee		  	  = array();
			$customer_fields  	   = array();
			$i18n 				  = array();
			
			
			//$tax_rates	  		 = $this->get_tax__rates();
			
			$tax_based_on = get_option('woocommerce_tax_based_on');
			
			$location_name = '';
			if(isset($_SESSION['current_location_name'])){
				$location_name = $_SESSION['current_location_name'];
			}else{
				$location_name = '';
			}
			
			require_once('icwcpos_front_thank_you_page.php');
			
			// now show the page
			include ICWCPOS_PATH.'template/template.php';
			
			//echo "Point of Sale template";
			
			exit;
		
		  }
		  
		  function login_url($login_url = ''){
			 return $login_url;
		  }
		  
		  
		  function get_variable_products_query(){
		  	   global $wpdb;
				
				$sql = "SELECT";
				
				$sql .= " post_parent";
				
				$sql .= " FROM $wpdb->posts AS posts";
				
				$sql .= " WHERE 1*1";
				
				$sql .= " AND post_type IN ('product_variation')";
				
				$sql .= " AND post_status IN ('publish')";
				
				$sql .= " GROUP BY post_parent";
				
				//$sql .= " ORDER BY post_parent ASC";
				
				//$results = $wpdb->get_results($sql);
				
				//$variable_products 		= $this->get_items_id_list($results,'post_parent','','string');
				
				return $sql;
		  }
		  
		  function get_pos_product_data($type = 'limit_row', $limit = 10){
				global $wpdb;
				
				$variable_products_query = $this->get_variable_products_query();
				
				$term 		 = $this->get_request("term",'');
				
				$sql = "SELECT";
				
				if($type == 'limit_row'){
					$sql .= " ID AS post_id";
					
					$sql .= ", post_parent AS post_parent";
					
					$sql .= ", post_type AS post_type";
					
					$sql .= ", post_title";
				}
				
				if($type == 'count'){
					$sql .= " COUNT(ID)";
				}
				
				$sql .= " FROM $wpdb->posts AS posts";
				
				if($term != "" and $term != '-1'){				
					$sql .= " LEFT JOIN {$wpdb->postmeta} AS sku ON sku.post_id = posts.ID";				
				}
				
				$sql .= " WHERE 1*1";
				
				$sql .= " AND post_type IN ('product','product_variation')";
				
				$sql .= " AND post_status IN ('publish')";
				
				if($term != "" and $term != '-1'){
					
					$sql .= " AND sku.meta_key = '_sku'";
					
					$sql .= " AND (posts.post_title LIKE '%{$term}%' OR sku.meta_value LIKE '%{$term}%' OR posts.ID LIKE '%{$term}%')";
				}
				
				if($variable_products_query != "" and $variable_products_query != '-1'){
					$sql .= " AND ID NOT IN ({$variable_products_query})";
				}
				
				$sql .= " AND LENGTH(post_title) > 0";
				
				if($type == 'limit_row'){
					$p 			 = $this->get_request("p",1,true);
					
					$limit 		 = $this->get_request("limit",$limit,true);
					
					$start = (($p-1) * $limit);
					
					$sql .= " GROUP BY ID";				
					$sql .= " ORDER BY post_title ASC";
					$sql .= " LIMIT {$start}, {$limit} ";		
					$results = $wpdb->get_results($sql);
				}
				
				if($type == 'count'){
					$results = $wpdb->get_var($sql);
				}
				
				return $results;
		  }
		  
		  function get_pos_products($type = 'limit_row', $limit = 10){
			  	
				$results 		 = $this->get_pos_product_data($type,$limit);
								
				$post_ids 		= $this->get_items_id_list($results,'post_id','','string');
				
				$postmeta_data = $this->get_post_meta($post_ids);
				
				$placeholder_img_src = wc_placeholder_img_src();
				
				foreach($results as $key => $result){
						$results[$key]->product_id    = 0;
						$results[$key]->variation_id  = 0;
						$results[$key]->price 		 = 0;
						$results[$key]->regular_price = '';
						$results[$key]->sale_price 	= '';
						$results[$key]->sku 		   = '';
						$results[$key]->stock 		 = '';
						$results[$key]->stock_status  = '';
						$results[$key]->manage_stock  = 'no';
						$results[$key]->backorders    = '';
						$results[$key]->thumbnail_id  = 0;
						$results[$key]->thumbnail_src  = $placeholder_img_src;
						
						$post_id 	 = $result->post_id;
						$post_parent = $result->post_parent;
						$id  = 0;
						
						
						if($post_parent > 0){
							$results[$key]->product_id = $post_parent;
							$results[$key]->variation_id = $post_id;							
						}else{
							$results[$key]->product_id = $post_id;							
						}
						
						if(isset($postmeta_data[$post_id])){
							foreach($postmeta_data[$post_id] as $meta_key => $meta_value){
								$results[$key]->{$meta_key} = $meta_value;
							}
						}
						
						$thumbnail_id = $result->thumbnail_id;
						
						if($thumbnail_id > 0){
							$thumbnail = wp_get_attachment_image_src($thumbnail_id);
							$thumbnail_src = '';
							if (isset($thumbnail[0])){
								$thumbnail_src  = $thumbnail[0];
							}
							
							$results[$key]->thumbnail_src  = $thumbnail_src;
						}
				} 
				
				$results = apply_filters('icwcpos_products',$results);
				
				return $results;
		  }
		  
		  function get_product_grid($pos_products){
			  	$output =  "";
				foreach($pos_products as $key => $product){
						
					$product_id 		= $product->product_id;
					$variation_id 	  = $product->variation_id;
					$post_id 		   = $product->post_id;
					$post_title 		= $product->post_title;
					$thumbnail_src 	 = $product->thumbnail_src;
					$price 			 = $product->price;
					$regular_price 	 = $product->regular_price;						
					$stock 			 = $product->stock;
					$manage_stock	  = $product->manage_stock;
					
					$tag_attributes = array();
					$tag_attributes[] = "class=\"add_to_cart\"";
					$tag_attributes[] = "href=\"#\"";
					//$tag_attributes[] = "data-total_qty=\"1\"";
					$tag_attributes[] = "data-price=\"{$price}\"";
					$tag_attributes[] = "data-regular_price=\"{$regular_price}\"";
					//$tag_attributes[] = "data-total_qty_price=\"{$price}\"";
					$tag_attributes[] = "data-post_id=\"{$post_id}\"";
					$tag_attributes[] = "data-product_id=\"{$product_id}\"";
					$tag_attributes[] = "data-variation_id=\"{$variation_id}\"";
					$tag_attributes[] = "data-post_title=\"{$post_title}\"";
					
					
					//$tag_attributes[] = "data-total_qty=\"1\"";
					//$tag_attributes[] = "data-total_qty_price=\"{$price}\"";
					//$tag_attributes[] = "data-total_qty_regular_price=\"{$regular_price}\"";
					
					
					/*
					foreach($product as $product_key => $product_value){
						$tag_attributes[] = "data-{$product_key}=\"$product_value\"";
					}
					*/
					
					$n_in_stock = __('%s in stock','icwcpos');						
					
					$tagattributes = implode(" ",$tag_attributes);
					
					$output .= "<tr class=\"product_row post_id_{$post_id}\">";
						$output .= "<td>";
							$output .= "<img src=\"{$thumbnail_src}\" alt=\"{$post_title}\" />";
						$output .= "</td>";
						
						$output .= "<td>";
							$output .= '<h4>';
							$output .= $post_title;
							$output .= '</h4>';
							if($manage_stock == 'yes'){
								
								$output .= "<span class=\"stock stock_post_id_{$post_id}\" data-stock=\"{$stock}\">";
								$output .= sprintf($n_in_stock,$stock);
								$output .= "</span>";
								/*
								if($stock_status == 'instock'){
									$output .= "{$stock} in stock";
								} else if($stock_status == 'outofstock'){
									$output .= "{$stock} in out of stock";
								}else{
									$output .= "{$stock} in stock";
								}
								*/
							}
						$output .= "</td>";
						
						$output .= "<td>";							
							if($regular_price > $price and $price != 0){
								$output .= "<span class=\"line_through\">";
								$output .= wc_price($regular_price);
								$output .= "</span>";
								$output .= " ";
								$output .= wc_price($price);
							}else{
								$output .= wc_price($price);
							}
						$output .= "</td>";
						
						$output .= "<td>";							
							$output .= "<a {$tagattributes}>";
							$output .= '<i class="fa fa-plus-square" aria-hidden="true"></i>';
							$output .= '</a>';
						$output .= "</td>";
						
					$output .= "</tr>";
				}
				
				return $output;
		  }
		  
		  function get_product_list_content($settings = array()){
			  	$limit = isset($settings['products_per_page']) ? $settings['products_per_page'] : 10;
				$data_type = isset($settings['data_type']) ? $settings['data_type'] : 'limit_row';
				$output = "<table class=\"product_list\">";
				$output .= "<tbody>";
				//$pos_products = $this->get_pos_products($data_type,$limit);
				//$output .= $this->get_product_grid($pos_products);
				$output .= "</tbody>";
				$output .= "</table>";	 
				return $output;
		  }
		  
		  function get_post_meta($product_ids = ''){
			  global $wpdb;
			  $meta_keys = array();
			  $meta_keys[] = '_thumbnail_id';
			  $meta_keys[] = '_price';
			  $meta_keys[] = '_regular_price';
			  $meta_keys[] = '_manage_stock';
			  $meta_keys[] = '_stock';
			  //$meta_keys[] = '_sku';
			  //$meta_keys[] = '_stock_status';
			  //$meta_keys[] = '_backorders';
			  //$meta_keys[] = '_sale_price';			  
			  
			  $meta_keys = apply_filters('icwcpos_product_meta_keys',$meta_keys);
			  
			  $sql = "SELECT post_id,TRIM(LEADING '_' FROM meta_key) AS meta_key, meta_value FROM $wpdb->postmeta WHERE 1*1";
			  
			  if(count($meta_keys) > 0){
				$meta_keys = implode("','",$meta_keys);
				$sql .= " AND meta_key  IN ('{$meta_keys}')";
			  }
			  
			   if($product_ids != ''){
				$sql .= " AND post_id  IN ({$product_ids})";
			  }
			  
			  $results = $wpdb->get_results($sql);
			  $list = array();
			  foreach($results as $key => $result){
				$post_id 	   = $result->post_id;
				$meta_key 	  = $result->meta_key;
				$meta_value 	= $result->meta_value;
				$list[$post_id][$meta_key] = $meta_value;
			  }
			 return $list;
		  }
		  
		  
		public function tax_classes() {
			$classes = array(
				'' => __( 'Standard', 'icwcpos')
			);
			
			// get_tax_classes method introduced in WC 2.3
			if( method_exists( 'WC_Tax','get_tax_classes' ) ){
				$labels = WC_Tax::get_tax_classes();
			} else {
				$labels = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
			}
			
			foreach( $labels as $label ){
				$classes[ sanitize_title($label) ] = $label;
			}
			
			return $classes;
		}
		  
		public function tax_rates() {
			$rates = array();			
			foreach($this->tax_classes() as $class => $label ) {
				if( $rate = WC_Tax::get_base_tax_rates( $class ) ){
					$rates[$class] = $rate;
				}
			}			
			return $rates;
		}
		
		function get_tax__rates(){
			global $wpdb;
			
			$sql = "SELECT tax_rate_id,location_type,location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations";			
			$results = $wpdb->get_results($sql);
			$locations = array();
			$location_types = array();
			foreach($results as $key => $result){
				$locations[$result->tax_rate_id]["tax_rate_".$result->location_type] = $result->location_code;
				$location_types["tax_rate_".$result->location_type] = $result->location_type;
			}
			
			//$sql = "SELECT tax_rate_id,tax_rate_country,tax_rate_state FROM {$wpdb->prefix}woocommerce_tax_rates";			
			$sql = "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates";			
			$results = $wpdb->get_results($sql);
			
			foreach($results as $key => $result){
				$tax_rate_id = $result->tax_rate_id;
				foreach($location_types as $location_type => $location_code){
					$results[$key]->{$location_type} = '';
				}
				
				$location = isset($locations[$result->tax_rate_id]) ? $locations[$result->tax_rate_id] : array();
				foreach($location as $location_type => $location_code){
					$results[$key]->{$location_type} = strtolower($location_code);
				}
				
				$results[$key]->label = $result->tax_rate_name;
				$results[$key]->compound = $result->tax_rate_compound == 1 ? 'yes' : 'no';
				$results[$key]->shipping = $result->tax_rate_shipping == 1 ? 'yes' : 'no';
				$results[$key]->rate	 = $result->tax_rate;
				
				$results[$key]->tax_rate_country	 = strtolower($result->tax_rate_country);
				$results[$key]->tax_rate_state	 = strtolower($result->tax_rate_state);
			}
			
			return $results;
			
		}
		
		private function get_settings() {
			
			$tax_based_on = get_option( 'woocommerce_tax_based_on' );
			$permalink    = get_option('wc_permalink_point_of_sale', 'point-of-sale');
			$permalink    = empty($permalink) ? 'point-of-sale' : $permalink;
			$site_url 	 = get_site_url();
			$post_url     = $site_url.'/'.$permalink;
			
			$tax = array();
			$tax['calc_taxes']            = get_option('woocommerce_calc_taxes');
			$tax['prices_include_tax']    = get_option('woocommerce_prices_include_tax');
			$tax['tax_round_at_subtotal'] = get_option('woocommerce_tax_round_at_subtotal');
			$tax['tax_display_cart']      = get_option('woocommerce_tax_display_cart');
			$tax['tax_total_display']     = get_option('woocommerce_tax_total_display');
			$tax['site_url']     		  = $site_url;
			$tax['permalink']     		 = $permalink;
			$tax['post_url']     		  = $post_url;
			
			$tax['currency_pos'] 		  = get_option( 'woocommerce_currency_pos' );
			$tax['currency']     		  = get_woocommerce_currency();
			$tax['currency_symbol']       = get_woocommerce_currency_symbol($tax['currency']);
			
			
			$tax['tax_based_on']     	  = $tax_based_on;
						
			$country  = WC()->countries->get_base_country();
			$state    = WC()->countries->get_base_state();
			$city     = WC()->countries->get_base_city();
			$postcode = WC()->countries->get_base_postcode();
			
			$country  = strtolower(trim($country));
			$state    = strtolower(trim($state));
			$city     = strtolower(trim($city));
			$postcode = strtolower(trim($postcode));
			
			$tax['base_country']  = $country;
			$tax['base_state'] 	= $state;					
			$tax['base_city'] 	 = $city;
			$tax['base_postcode'] = $postcode;
			
			$tax['tax_label']             = WC()->countries->tax_or_vat();
			$tax['ship_to_countries']     = get_option( 'woocommerce_ship_to_countries' );
			$tax['enable_order_note']     = apply_filters( 'woocommerce_enable_order_notes_field', ('yes' == get_option( 'woocommerce_enable_order_comments', 'yes' )));
			$tax['products_per_page']     = 10;
			return $tax;
		}
		
		public function get_shipping_methods() {
			$labels = array();
		
			if( version_compare( WC()->version, '3', '<' ) ) {
				$labels[''] = __('N/A', 'icwcpos');
			}
		
			$shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
		
			foreach( $shipping_methods as $method ){
			  $labels[$method->id] = method_exists($method, 'get_method_title') ? $method->get_method_title() : $method->get_title();
			}
		
			$labels['other'] = __( 'Other', 'icwcpos');
		
			return $labels;
		 }
		  
		 function get_payment_gateways(){
			$payment_gateways = WC()->payment_gateways->payment_gateways();
			$gateways = array();
			foreach($payment_gateways as $method_id => $payment_gateway){
				$enabled = $payment_gateway->enabled;
				if($enabled == 'yes'){
					$gateways[$method_id] = $payment_gateway->method_title;
				}
			}
			return $gateways;
		}
		
		
		
		function get_employee(){
			global $wpdb;
			//$sql = "SELECT id, CONCAT(first_name,' ',last_name) AS employee_name  FROM {$wpdb->prefix}ic_pos_employee AS employee ORDER BY employee_name ASC";
			//$results = $wpdb->get_results($sql);
			
			$query = "SELECT ID AS id, display_name AS employee_name";
			$query .= " FROM {$wpdb->users} AS user "; 
			$query .= " LEFT JOIN {$wpdb->usermeta} as allow_login ON allow_login.user_id = user.ID ";
			$query .= " WHERE 1=1 ";
			$query .= " AND  allow_login.meta_key='allow_login' ";
			$query .= " ORDER BY employee_name ASC";
			$results = $wpdb->get_results($query);
			
			//error_log(print_r($wpdb,true));
			
			return $results;
		}
		  
		
		
		function get_labels(){
			$i18n = array();
			$i18n['please_wait'] 			 = __('Please Wait!','icwcpos');
			$i18n['employee'] 				= __('Attendee','icwcpos');
			$i18n['select_employee'] 		 = __('Select Attendee','icwcpos');
			$i18n['return_to_sale'] 		  = __('Return To Sale','icwcpos');
			$i18n['add_new_customer'] 		= __('Add New Customer','icwcpos');
			$i18n['update_customer'] 	 	 = __('Update Customer','icwcpos');
			$i18n['required_text_field'] 	 = __('Please enter %s.','icwcpos');
			$i18n['required_select_field']   = __('Please select %s.','icwcpos');
			$i18n['required_email_field']    = __('Please enter valid billing email.','icwcpos');
			
			$i18n['customer_selection']      = __('Enter Customer Email Or Name.','icwcpos');
			$i18n['customer_details']        = __('Details','icwcpos');
			$i18n['btn_clear_customer']      = __('Clear','icwcpos');
			$i18n['select_customer']         = __('Please select customer.','icwcpos');
			$i18n['guest_customer']              = __('Guest Customer','icwcpos');
			
			
			$i18n['column_quanity']      = __('Qty.','icwcpos');
			$i18n['column_product']      = __('Product','icwcpos');
			$i18n['column_employee']      = __('Attendee','icwcpos');
			$i18n['column_price']      = __('Price','icwcpos');
			$i18n['column_total']      = __('Total','icwcpos');
			
			$i18n['more']      = __('More','icwcpos');
			$i18n['add_meta']      = __('Add Meta','icwcpos');
			$i18n['remove_meta']      = __('Remove Meta','icwcpos');
			
			$i18n['shipping']      = __('Shipping','icwcpos');
			$i18n['shipping_method']      = __('Shipping Method','icwcpos');
			$i18n['fee']      = __('Fee','icwcpos');
			
			$i18n['subtotal']      = __('Subtotal','icwcpos');
			$i18n['discount']      = __('Discount','icwcpos');
			$i18n['order_total']      = __('Order Total','icwcpos');
			
			$i18n['btn_clear_cart']     = __('Clear Cart','icwcpos');
			$i18n['btn_new_customer']   = __('New Customer','icwcpos');
			$i18n['btn_order_note']     = __('Order Note','icwcpos');
			$i18n['btn_fee']      		= __('Fee','icwcpos');
			$i18n['btn_shipping']       = __('Shipping','icwcpos');
			$i18n['btn_chekout']        = __('Checkout','icwcpos');
			
			
			$i18n['btn_return_to_sale']       = __('Return To Sale','icwcpos');
			$i18n['btn_place_order']        = __('Place Order','icwcpos');
			$i18n['btn_update_customer']  	 = __('Update Customer','icwcpos');
			$i18n['btn_add_new_customer'] 	= __('Add New Customer','icwcpos');
			$i18n['btn_add_guest_customer']  = __('Add Guest Customer','icwcpos');
			
			$i18n['your_cart_empty']       = __('Your cart is currently empty.','icwcpos');			
			$i18n['to_pay']        		= __('To Pay: ','icwcpos');
			$i18n['n_in_stock']        	= __('%s in stock','icwcpos');
			return $i18n;
		}
		
		
		
		function get_all_settings(){
			$return  = array();
			$return['assets_url'] 			= $this->constants['assets_url'];			
			$return['tax_rates']	  		 = $this->tax_rates();
			$return['tax__rates']	  		  = $this->get_tax__rates();
			$return['settings']	   		  = $this->get_settings();
			$return['shipping_methods']	  = $this->get_shipping_methods();
			$return['payment_gateways'] 	  = $this->get_payment_gateways();			
			$return['employee']		  	  = $this->get_employee();
			$return['customer_fields']  	   = $this->get_customer_meta_fields();
			$return['i18n']  	  			  = $this->get_labels();
			
			return $return;
		}
		
		function ajax(){
			$form_action  = isset($_REQUEST['form_action']) ? $_REQUEST['form_action'] : '';
			$limit 		= isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 10;
			$data_type 	= isset($_REQUEST['data_type']) ? $_REQUEST['data_type'] : 'limit_row';
			
			if($form_action == 'search_product'){
				$pos_products = $this->get_pos_products($data_type,$limit);
				$results = $this->get_product_grid($pos_products);
				echo $results;
			}
			
			if($form_action == 'settings'){
				$results = $this->get_all_settings();
				echo json_encode($results);
			}			
			die;
		}
		
	}/*End Class*/
}
