<?php
if (!class_exists('icwcpos_create_db_tables')) {
	class icwcpos_create_db_tables{
		
		var $path 		= "";
		var $constants 	= array();
		
		public function __construct($constants = array()){			
			$this->constants = $constants;			
			$this->constants['today_datetime'] = date_i18n("Y-m-d H-i-s");
			$this->constants['current_user_id'] = get_current_user_id();
		}
		
		
		
		//RENAME TABLE `point_of_sale`.`wp_ic_expense_details` TO `point_of_sale`.`wp_ic_pos_expense_details`;
		
		function create_table(){			
			global $wpdb;		
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				
			
			
			
		}
		
	}/*End Class*/
}/*End Class Exits*/