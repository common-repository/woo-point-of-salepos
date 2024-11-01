<?php
if(!class_exists('ICWCPOS_FRONT_PRODUCT_PRICE')){ 
	class ICWCPOS_FRONT_PRODUCT_PRICE extends ICWCPOS_FUNCTION{
		
		var $constants = array();
		
		public function __construct($constants =array()) {
			$this->constants = $constants;			
			add_filter('woocommerce_product_get_price', 				array( $this, 'woocommerce_get_price' ), 101, 2 );			
			add_filter('woocommerce_get_price_including_tax', array( $this, 'woocommerce_get_price_including_tax' ), 101, 3 );
			add_filter('woocommerce_get_price_excluding_tax', array( $this, 'woocommerce_get_price_excluding_tax' ), 101, 3 );			
			add_filter('icwcpos_products', 				  array( $this, 'icwcpos_products' ), 10 );
		}
		
		function init(){}
		
		function woocommerce_get_price($price, $product = NULL){
			 global $post, $woocommerce;
			 $product_id = $product->get_id();			 
			 $price = $this->get_product_location_price($price,1,$product_id);			
			 return $price;
		}
		
		function icwcpos_products($products = array()){
			
			$calc_taxes            = get_option( 'woocommerce_calc_taxes' );
			if($calc_taxes == 'yes'){
				$prices_include_tax    = get_option( 'woocommerce_prices_include_tax' );
				//error_log("calc_taxes: {$calc_taxes}");
				//error_log("prices_include_tax: {$prices_include_tax}");
				
				if($prices_include_tax == 'yes'){
					//error_log("prices_include_tax:yes - {$prices_include_tax}");
					foreach($products as $key => $product){
							    $location_price 	= $this->get_product_location_price(0,1,$product->post_id);							
								
								if($location_price <=0 ){
									$location_price 	= $products[$key]->sale_price;	
								}
								$args = array(
									'qty'   => 1,
									'price' => $location_price
								);								
								$wc_product = wc_get_product($product->post_id);
								$location_price = $this->wc_get_price_excluding_tax($wc_product,$args);								
								$products[$key]->sale_price 	= $location_price;
								$products[$key]->price 		 = $location_price;
								$products[$key]->regular_price = $location_price;
							
					}
				}else{
					foreach($products as $key => $product){
							$location_price 	= $this->get_product_location_price(0,1,$product->post_id);
							if($location_price > 0){
								$args = array(
									'qty'   => 1,
									'price' => $location_price
								);
								$wc_product = wc_get_product($product->post_id);
								$location_price = $this->wc_get_price_including_tax($wc_product,$args);
								$products[$key]->sale_price 	= $location_price;
								$products[$key]->price 		 = $location_price;
								$products[$key]->regular_price = $location_price;
							}
					}
				}
			}else{
				foreach($products as $key => $product){
					$location_price 	= $this->get_product_location_price($products[$key]->sale_price,1,$product->post_id);
					if($location_price > 0){
						$products[$key]->sale_price 	= $location_price;
						$products[$key]->price 		 = $location_price;
						$products[$key]->regular_price = $location_price;
					}
				}
			}
			
			return $products;
		}
		
		function woocommerce_get_price_including_tax($price,$qty = 0, $product = NULL){
			global $post, $woocommerce;
			$product_id = $product->get_id();			 
			 
			$location_price = $this->get_product_location_price(0,1,$product_id);
			if($location_price > 0){
				$args = array(
					'qty'   => $qty,
					'price' => $location_price
				);
				//error_log("--woocommerce_get_price_including_tax--");
				//error_log(print_r($args,true));
				$price = $this->wc_get_price_including_tax($product,$args);		
			}
			 return $price;
		}
		
		function woocommerce_get_price_excluding_tax($price,$qty = 0, $product = NULL){
			 global $post, $woocommerce;
			 $product_id = $product->get_id();			 
			 
			 $location_price = $this->get_product_location_price(0,1,$product_id);
			 if($location_price > 0){
				 $args = array(
					'qty'   => $qty,
					'price' => $location_price
				 );
				//error_log("--woocommerce_get_price_excluding_tax--");
				//error_log(print_r($args,true));
				$price = $this->wc_get_price_excluding_tax($product,$args);
			 }
			 return $price;
		}
		
		
		
		function get_product_location_price($price = 0, $qty = 1, $product_id = 0){
			 global $wpdb;
			 /*
			 $location_id = $this->get_location_id();
			 
			 if($location_id <= 0 || $location_id == ""){
				 return $price;
			 }
			 
			 $sql = "SELECT TRIM(price) FROM `{$wpdb->prefix}ic_pos_location_product_price`";
			 $sql .= "  WHERE 1*1";
			 $sql .= "  AND product_id IN ($product_id)";
			 $sql .= "  AND location_id IN ($location_id)";
			 $sql .= "  AND LENGTH(TRIM(price)) > 0";
			 
			 $location_price = $wpdb->get_var($sql);
			 if($location_price > 0){
				$price = $location_price*$qty;
			 }
			*/
			return $price;
		}
		
		function wc_get_price_excluding_tax( $product, $args = array() ) {
			$args = wp_parse_args(
				$args, array(
					'qty'   => '',
					'price' => '',
				)
			);
		
			$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
			$qty   = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;
		
			if ( '' === $price ) {
				return '';
			} elseif ( empty( $qty ) ) {
				return 0.0;
			}
		
			$line_price = $price * $qty;
		
			if ( $product->is_taxable() && wc_prices_include_tax() ) {
				$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
				$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
				$remove_taxes   = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
				$return_price   = $line_price - array_sum( $remove_taxes );
			} else {
				$return_price = $line_price;
			}
		
			return $return_price;
		}
		
		function wc_get_price_including_tax( $product, $args = array() ) {
			$args = wp_parse_args(
				$args, array(
					'qty'   => '',
					'price' => '',
				)
			);
		
			$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
			$qty   = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;
		
			if ( '' === $price ) {
				return '';
			} elseif ( empty( $qty ) ) {
				return 0.0;
			}
		
			$line_price   = $price * $qty;
			$return_price = $line_price;
		
			if ( $product->is_taxable() ) {
				if ( ! wc_prices_include_tax() ) {
					$tax_rates    = WC_Tax::get_rates( $product->get_tax_class() );
					$taxes        = WC_Tax::calc_tax( $line_price, $tax_rates, false );
					$tax_amount   = WC_Tax::get_tax_total( $taxes );
					$return_price = round( $line_price + $tax_amount, wc_get_price_decimals() );
				} else {
					$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
					$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
		
					/**
					 * If the customer is excempt from VAT, remove the taxes here.
					 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
					 */
					if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
						$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
						$remove_tax   = array_sum( $remove_taxes );
						$return_price = round( $line_price - $remove_tax, wc_get_price_decimals() );
		
						/**
					 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
					 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
					 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
					 */
					} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
						$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
						$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );
						$return_price = round( $line_price - array_sum( $base_taxes ) + wc_round_tax_total( array_sum( $modded_taxes ), wc_get_price_decimals() ), wc_get_price_decimals() );
					}
				}
			}
			return $return_price;
		}
	}
}