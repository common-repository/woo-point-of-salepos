<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_FRONT_CUSTOMER_SEARCH')){
	/*
	 * Class Name ICWCPOS_FRONT_CUSTOMER_SEARCH
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_FRONT_CUSTOMER_SEARCH extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
		}
		
		public function search_customer( $data = '' ) {
			global $wpdb;
			$return = array();
			
			$term = isset($_REQUEST['term']) ? trim($_REQUEST['term']) : '';
			
			if(empty($term)){
				return $return;
			}
			
			$terms = explode(" ", $term);
			
			$sql = " SELECT user_email";
			
			$sql .= ", ID AS customer_id";
			
			$sql .= ", display_name";
			
			$sql .= " FROM {$wpdb->users} AS uses";
			
			$sql .= " LEFT JOIN {$wpdb->usermeta} AS usermeta ON usermeta.user_id = uses.ID";
			
			$sql .= " WHERE 1*1";
			
			if(count($terms) > 0){
				//$sql .= " AND (user_email LIKE '%{$term}%' OR display_name LIKE '%{$term}%')";
				
				$query_user_email = "user_email LIKE '%".implode("%' OR user_email LIKE '%",$terms)."%'";
				
				$query_display_name = "display_name LIKE '%".implode("%' OR display_name LIKE '%",$terms)."%'";
				
				$query_usermeta = "usermeta.meta_value LIKE '%".implode("%' OR usermeta.meta_value LIKE '%",$terms)."%'";
				
				$sql .= " AND ({$query_user_email} OR {$query_display_name}  OR {$query_usermeta})";
			}
			
			$sql .= " GROUP BY uses.ID";
			
			$sql .= " ORDER BY uses.display_name ASC";
			
			$users = $wpdb->get_results($sql);
			
			//error_log($sql);
			
			if(count($users)<=0){
				/*$sql = " SELECT user_email";
				$sql .= ", ID AS customer_id";
				$sql .= ", display_name";
				$sql .= " FROM {$wpdb->users} AS uses";
				$sql .= " LEFT JOIN {$wpdb->usermeta} AS usermeta ON usermeta.user_id = uses.ID";
				$sql .= " WHERE 1*1";
				$sql .= " AND usermeta.meta_value LIKE '%{$term}%'";*/
				
				//$sql .= " AND usermeta.meta_key IN ('billing_first_name','billing_last_name','billing_company','nickname','first_name','last_name')";
				
				//$sql .= " GROUP BY uses.ID";
				//$sql .= " ORDER BY uses.display_name ASC";
				
				//$users = $wpdb->get_results($sql);
				
				
			}
			
			
			
			$user_label = __('%1$s (#%2$s - %3$s)', 'icwcpos');
			
			$tax_based_on = get_option( 'woocommerce_tax_based_on' );
			if ( 'base' === $tax_based_on ) {
				$country  = WC()->countries->get_base_country();
				$state    = WC()->countries->get_base_state();
				$city     = WC()->countries->get_base_city();
				$postcode = WC()->countries->get_base_postcode();
			}
			
			foreach($users as $user){
				$suggestion = array();
				$customer_id   = $user->customer_id;
				$display_name  = $user->display_name;
				$user_email 	= $user->user_email;
				$label 		 = sprintf($user_label,$display_name, $customer_id, $user_email);				
				
				$suggestion['value'] 				= $label;
				$suggestion['label'] 				= $label;
				$suggestion['id'] 				   = $customer_id;	
				
				if ( 'billing' === $tax_based_on || 'shipping' === $tax_based_on) {				
					$user_meta_key = array($tax_based_on.'_country',$tax_based_on.'_state',$tax_based_on.'_city',$tax_based_on.'_postcode');
										
					$user_meta =  $this->get_usermeta($customer_id, $user_meta_key);
					$usermeta =  isset($user_meta[$customer_id]) ? $user_meta[$customer_id] : array();
					
					foreach($user_meta_key as $k => $meta_key){
						$suggestion[$meta_key]	= '';
					}
					
					foreach($usermeta as $meta_key => $meta_value){
						$suggestion[$meta_key]	= $meta_value;
					}
					
					$country  = isset($suggestion[$tax_based_on.'_country'])  ? $suggestion[$tax_based_on.'_country']  : '';
					$state    = isset($suggestion[$tax_based_on.'_state'])    ? $suggestion[$tax_based_on.'_state']    : '';
					$city     = isset($suggestion[$tax_based_on.'_city']) 	 ? $suggestion[$tax_based_on.'_city']     : '';
					$postcode = isset($suggestion[$tax_based_on.'_postcode']) ? $suggestion[$tax_based_on.'_postcode'] : '';
				}
				
				$country  = strtolower(trim($country));
				$state    = strtolower(trim($state));
				$city     = strtolower(trim($city));
				$postcode = strtolower(trim($postcode));
				
				$suggestion['customer_country']  = $country;
				$suggestion['customer_state'] 	= $state;					
				$suggestion['customer_city'] 	 = $city;
				$suggestion['customer_postcode'] = $postcode;
				
				$return[] = $suggestion;
			}
			
			//error_log(print_r($return,true));
			return $return;
		}
		
		function get_usermeta($customer_id = '', $meta_kies = ''){
			global $wpdb;
			
			$sql = "SELECT * FROM $wpdb->usermeta WHERE 1*1 AND user_id IN ($customer_id)";			
			
			if($meta_kies){
				$meta_kies = implode("','",$meta_kies);
				$sql .= " AND meta_key IN ('{$meta_kies}')";
			}
			
			
			$results = $wpdb->get_results($sql);
			$list = array();
			foreach($results as $key => $result){
				$list[$result->user_id][$result->meta_key] = $result->meta_value;
			}
			
			return $list;
		}
		
		function customer_details($customer_id = 0){
			global $wpdb;
			
			$return = array();
			
			$customer_id = isset($_REQUEST['customer_id']) ? trim($_REQUEST['customer_id']) : $customer_id;
			
			$sql = " SELECT user_email";
			
			$sql .= ", ID AS customer_id";
			
			$sql .= ", display_name";
			
			$sql .= " FROM {$wpdb->users} AS uses";
			
			$sql .= " WHERE 1*1";
			
			if($customer_id > 0){
				$sql .= " AND uses.id IN ($customer_id)";
			}
			
			$sql .= " GROUP BY uses.ID";
			
			$sql .= " ORDER BY uses.display_name ASC";
			
			$user = $wpdb->get_row($sql);
			
			$return['customer_details'] 	 = array();			
			$return['field_billing_state']  = '';
			$return['field_shipping_state'] = '';
			
			if($user and count($user) > 0){
				$data = array();
				$user_meta =  get_user_meta($customer_id);
				$save_fields = $this->get_customer_meta_fields();			
				foreach ( $save_fields as $fieldset ) {		
					foreach ( $fieldset['fields'] as $key => $field ) {
						if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
							$data[$key] = '';
						}elseif(isset($user_meta[$key])){
							$meta_value =  (isset($user_meta[$key][0]) ? $user_meta[$key][0] : '');
							$user->$key = $meta_value;
						}
					}
				}
				$return['customer_details'] = $user;
				
				$billing_country = isset($user->billing_country) ? $user->billing_country : '';
				$shipping_country = isset($user->shipping_country) ? $user->shipping_country : '';
				
				$billing_state = isset($user->billing_state) ? $user->billing_state : '';
				$shipping_state = isset($user->shipping_state) ? $user->shipping_state : '';
				
				
				$return['field_billing_state'] = $this->country_states('billing_country','billing_state',$billing_country,$billing_state);
				$return['field_shipping_state'] = $this->country_states('shipping_country','shipping_state',$shipping_country,$shipping_state);
				
				
			}
			return $return;
		}
		
		public function ajax( $data = '' ) {
			$form_action = isset($_REQUEST['form_action']) ? $_REQUEST['form_action'] : '';
			if($form_action == 'customer_details'){
				$return = $this->customer_details();
				echo json_encode($return);
			}elseif($form_action == 'country_state_field'){
				$form_type = isset($_REQUEST['form_action']) ? $_REQUEST['form_action'] : '';
				$country = isset($_REQUEST['country']) ? $_REQUEST['country'] : '';
				if($form_type == 'billing'){
					echo $this->country_states('billing_country','billing_state',$country);
				}else if($form_type == 'shipping'){
					echo $this->country_states('shipping_country','shipping_state',$country);
				}else{
					echo $this->country_states('billing_country','billing_state',$country);
				}
				
			}else{
				$return = $this->search_customer();
				echo json_encode($return);
			}
			
			die;
		}
		
	}/*End Class*/
}
