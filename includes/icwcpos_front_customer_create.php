<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_FRONT_CUSTOMER_CREATE')){
	/*
	 * Class Name ICWCPOSPRO_FRONT_CUSTOMER_CREATE
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_FRONT_CUSTOMER_CREATE extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
		}
		
		public function create_customer( $data = '' ) {
			//clear_error_log();
			$error_messages 			= array();
			$return = array();
			$return['error'] 		   = false;
			$return['success'] 		 = false;
			$return['error_message']   = '';
			$return['success_message'] = '';
			$return['error_messages'] = $error_messages;
			
			$customer_data = isset($_REQUEST['customer_data']) ? $_REQUEST['customer_data'] : array();
			$customer = array();
			foreach($customer_data as $key => $data){
				$customer[$data['field']] = isset($data['value']) ? $data['value'] : '';
				$_POST[$data['field']] = isset($data['value']) ? $data['value'] : '';
			}
			
			$billing_email       = isset($customer['billing_email']) ? $customer['billing_email'] : '';
			$billing_first_name  = isset($customer['billing_first_name']) ? $customer['billing_first_name'] : '';
			$billing_last_name   = isset($customer['billing_last_name']) ? $customer['billing_last_name'] : '';
			$username 			= isset($customer['username']) ? $customer['username'] : $billing_email;
			
			$return['billing_email'] = $billing_email;
			$return['billing_first_name'] = $billing_first_name;
			$return['billing_last_name'] = $billing_last_name;
			
			$user_label   	  = __('%1$s (#%2$s - %3$s)', 'icwcpos');
			$display_name 	= $billing_last_name." ". $billing_first_name;
					
			$form_action = isset($_REQUEST['form_action']) ? $_REQUEST['form_action'] : 0;
			if($form_action == 'update_customer'){
				$customer_id = isset($_REQUEST['loaded_customer_id']) ? $_REQUEST['loaded_customer_id'] : 0;
				if($customer_id > 0){										
					$this->save_customer_meta_fields($customer_id);					
					$customer_label  			= sprintf($user_label,$display_name, $customer_id, $billing_email);			
					$success_message 		   = __('Customer successfully update.','icwcpos');
					$return['success_message'] = "<p>". $success_message."</p>";
					$return['success'] 		 = true;
					$return['customer_id'] 	 = $customer_id;
					$return['customer_label']  = $customer_label;
					
					$tax_based_on = get_option('woocommerce_tax_based_on');
					if($tax_based_on == "billing" || $tax_based_on == "shipping"){
						$country   = isset($customer[$tax_based_on.'_country'])  ? $customer[$tax_based_on.'_country']  : '';
						$state     = isset($customer[$tax_based_on.'_state'])    ? $customer[$tax_based_on.'_state']    : '';
						$city      = isset($customer[$tax_based_on.'_city'])     ? $customer[$tax_based_on.'_city']     : '';
						$postcode  = isset($customer[$tax_based_on.'_postcode']) ? $customer[$tax_based_on.'_postcode'] : '';
					}
					
					if($tax_based_on == "base"){
						$country  = WC()->countries->get_base_country();
						$state    = WC()->countries->get_base_state();
						$city     = WC()->countries->get_base_city();
						$postcode = WC()->countries->get_base_postcode();
					}
					
					$country  = strtolower(trim($country));
					$state    = strtolower(trim($state));
					$city     = strtolower(trim($city));
					$postcode = strtolower(trim($postcode));
					
					$return['customer_country']  = $country;
					$return['customer_state'] 	= $state;					
					$return['customer_city'] 	 = $city;
					$return['customer_postcode'] = $postcode;
					
					return $return;
				}
			}
			
			$user = get_user_by('email',$billing_email);
			if(NULL == $user){				
				if ( email_exists( $billing_email ) ){					
					 $error_messages[] = sprintf(__('The email(%s) already exists. Please use a different email','icwcpos'),$billing_email);
				}
				
				if(count($error_messages) > 0){
					$return['error_message'] = "<p>" .implode("<p></p>",$error_messages)."</p>";
					$return['error'] = true;
					$return['error_messages'] = $error_messages;
					return $return;
				}
				 
				if ( username_exists( $username ) ){
					 $error_messages[] = sprintf(__('The email(%s) already exists. Please use a different email','icwcpos'),$billing_email);
				}
				
				if(count($error_messages) > 0){
					$return['error_message'] = "<p>" .implode("<p></p>",$error_messages)."</p>";
					$return['error'] = true;
					$return['error_messages'] = $error_messages;
					return $return;
				}
				
				
				
				$return['customer_id'] 	 = 0;
				$return['customer_label']  = '';
				
				
				
				$password    = wp_generate_password( 12, false );
				$customer_id 	 = wp_create_user($billing_email, $password, $billing_email );
				if($customer_id){					
					$success_message = __('Customer created succssfully','icwcpos');
					
					$customer_label 	   		= sprintf($user_label,$display_name, $customer_id, $billing_email);
					
					$return['success_message'] = "<p>". $success_message."</p>";
					$return['success'] 		 = true;
					$return['customer_id'] 	 = $customer_id;
					$return['customer_label']  = $customer_label;
										
					$tax_based_on = get_option('woocommerce_tax_based_on');
					if($tax_based_on == "billing" || $tax_based_on == "shipping"){
						$country   = isset($customer[$tax_based_on.'_country'])  ? $customer[$tax_based_on.'_country']  : '';
						$state     = isset($customer[$tax_based_on.'_state'])    ? $customer[$tax_based_on.'_state']    : '';
						$city      = isset($customer[$tax_based_on.'_city'])     ? $customer[$tax_based_on.'_city']     : '';
						$postcode  = isset($customer[$tax_based_on.'_postcode']) ? $customer[$tax_based_on.'_postcode'] : '';
					}
					
					if($tax_based_on == "base"){
						$country  = WC()->countries->get_base_country();
						$state    = WC()->countries->get_base_state();
						$city     = WC()->countries->get_base_city();
						$postcode = WC()->countries->get_base_postcode();
					}
					
					$country  = strtolower(trim($country));
					$state    = strtolower(trim($state));
					$city     = strtolower(trim($city));
					$postcode = strtolower(trim($postcode));
					
					$return['customer_country']  = $country;
					$return['customer_state'] 	= $state;					
					$return['customer_city'] 	 = $city;
					$return['customer_postcode'] = $postcode;
					
					
					$args 				 = array();					
					$args['ID'] 		   = $customer_id;					
					$args['first_name']   = $billing_first_name;
					$args['last_name']    = $billing_last_name;
					$args['display_name'] = $billing_last_name." ". $billing_first_name;
					
					wp_update_user( $args );
					
					$user = new WP_User( $customer_id );
					$user->set_role('customer');
					
					$this->save_customer_meta_fields($customer_id);
				}
				
			}else{
				$customer_id = isset($user->ID) ? $user->ID : 0;
				$error_messages[] = sprintf(__('The email(%s) already exists. Please use a different email','icwcpos'),$billing_email);
				 				
				$customer_label 	   		= sprintf($user_label,$display_name, $customer_id, $billing_email);
				$return['customer_id'] 	 = $customer_id;
				$return['customer_label']  = $customer_label;
				
				/*
				$user = new WP_User( $customer_id );
				$roles = isset($user->roles) ? $user->roles : array();
				if(in_array('subscriber',$roles)){
					$user->set_role('customer');
				}
				*/
				
				 if(count($error_messages) > 0){
					$return['error_message'] = "<p>" .implode("<p></p>",$error_messages)."</p>";
					$return['error'] = true;
					$return['error_messages'] = $error_messages;
					return $return;
				}
			}
			
			$return['error_messages'] = $error_messages;
			return $return;
		}
		
		public function ajax( $data = '' ) {
			$return = $this->create_customer();
			echo json_encode($return);
			die;
		}
		
		
		/**
		 * Save Address Fields on edit user pages.
		 *
		 * @param int $customer_id User ID of the user being saved
		 */
		public function save_customer_meta_fields( $customer_id ) {
			$save_fields = $this->get_customer_meta_fields();
			
			foreach ( $save_fields as $fieldset ) {
	
				foreach ( $fieldset['fields'] as $key => $field ) {
					if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
						update_user_meta( $customer_id, $key, isset( $_POST[ $key ] ) );
					} elseif ( isset( $_POST[ $key ] ) ) {
						update_user_meta( $customer_id, $key, wc_clean( $_POST[ $key ] ) );
					}
				}
			}
		}
		
	}/*End Class*/
}
