<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_ADMIN_ORDER_EDIT')){
	/*
	 * Class Name ICWCPOS_ADMIN_ORDER_EDIT
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_ADMIN_ORDER_EDIT{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
			
			add_filter('woocommerce_order_item_get_formatted_meta_data', array($this,'woocommerce_order_item_get_formatted_meta_data'),101,1);
		}
		
		function woocommerce_order_item_get_formatted_meta_data($formatted_meta = array()){
			foreach ( $formatted_meta as $key => $meta_data ) {
				switch($meta_data->display_key){
					case "employee_id":
						$formatted_meta[$key]->display_key = __('Attendee ID','icwcpos');
						break;
					case "employee_name":
						$formatted_meta[$key]->display_key = __('Attendee Name','icwcpos');
						break;
					
				}
			}
			
			return $formatted_meta;
		}
		
	}/*End Class*/
}
