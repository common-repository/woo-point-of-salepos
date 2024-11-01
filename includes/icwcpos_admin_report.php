<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_ADMIN_REPORT')){
	/*
	 * Class Name ICWCPOS_ADMIN_LOCATION
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_ADMIN_REPORT extends ICWCPOS_FUNCTION {
		/* variable declaration constants*/
		public $constants 				= array();	
		/**
		*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
		}
		function get_last_30_days_sales($location_id = 0){
			global $wpdb;
			$last_30_days 	 = array();
			$new_data 	 	 = array();
			$formated_row 	 = array();
			$query = "";
			$query .= " SELECT  ";
			$query .= " date_format(posts.post_date , '%Y-%m-%d') as order_date ";
			$query .= " ,SUM(ROUND( order_total.meta_value,2))   as order_total";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as order_total  ON order_total.post_id = posts.ID ";
			if ($location_id >0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			$query .= " WHERE 1=1 ";
			
			$query .= " AND order_total.meta_key ='_order_total'";
			if ($location_id >0){
				$query .= " AND pos_location_id.meta_key ='_pos_location_id'";
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}
			$query .= " GROUP BY date_format(posts.post_date , '%Y-%m-%d') ";
			
			$rows = $wpdb->get_results($query);
			
			foreach($rows  as $key=>$value ){
				$formated_row[$value->order_date] = $value->order_total;
			}
			//$this->print_array($formated_row);
			//$this->print_array($rows);
			for($i=0;$i<=30;$i++){
				$new_date = date_i18n('Y-m-d', strtotime('today - '.$i.' days'));
				$last_30_days[$new_date] = $new_date;
			}
			$i=0 ; 
			foreach($last_30_days as $key=>$value){
		  		if (isset($formated_row[$value])){
					$new_data[$i]["order_date"] = $value;
					$new_data[$i]["order_total"] = $formated_row[$value];
				}else{
					$new_data[$i]["order_date"] = $value;
					$new_data[$i]["order_total"] = 0;
				}
				$i++;		
			}
			//$this->print_array($new_data);
			//$this->print_array($last_30_days);
			return $new_data; 
		}
		function get_gross_total($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			$query .= " SUM(ROUND( order_total.meta_value,2))  ";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as order_total  ON order_total.post_id = posts.ID ";
			/*Location*/
			if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			
			$query .= " WHERE 1=1 ";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND order_total.meta_key ='_order_total'";
			/*Location*/
			if ($location_id>0){
				$query .= " AND pos_location_id.meta_key ='_pos_location_id'";
			}
			
			
			
			//$query .= " ORDER BY posts.post_date  ";
			
			$rows = $wpdb->get_var($query);
			//$this->print_array($rows);
			return $rows; 
		}
		function get_net_total($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			$query .= " SUM(ROUND( line_total.meta_value,2))  ";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items  ON order_items.order_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as line_total  ON line_total.order_item_id = order_items.order_item_id ";
			
			$query .= " WHERE 1=1 ";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			if ($location_id>0){
				$query .= " AND pos_location_id.meta_key ='_pos_location_id'";
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}
			$query .= " AND line_total.meta_key ='_line_total'";
			
			//$query .= " ORDER BY posts.post_date  ";
			
			$rows = $wpdb->get_var($query);
			//$this->print_array($rows);
			return $rows; 
		}
		function get_expanse_total($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			$query .= " SUM(ROUND(amount,0)) as total_amount ";
			$query .= " FROM {$wpdb->prefix}ic_pos_expense_details as expense";
			
			$query .= " WHERE 1=1 ";
			if ($start_date && $end_date) {
					$query .= " AND expense_date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			if ($location_id>0){
				$query .= " AND expense.location_id ='{$location_id}'";
			}

			$rows = $wpdb->get_var($query);
			//$this->print_array($rows);
			return $rows; 
		}
		function get_expense_report($start_date,$end_date){
			global $wpdb;
			$query = "";
			$query .= " SELECT * ";
			$query .= " FROM ";
			$query .= " {$wpdb->prefix}ic_pos_expense_details as expense";
			$query .= " WHERE 1=1 ";
			
			if ($start_date && $end_date) {
				$query .= " AND expense_date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " ORDER BY expense_date ";
			$rows = $wpdb->get_results($query);
			$this->print_array($rows);
			return $rows; 
		}
		function get_expense_amount_by_date($start_date =NULL,$end_date=NULL){
			global $wpdb;
			$query = "";
			$query .= " SELECT ";
			$query .= " SUM(ROUND(amount,0)) as total_amount ";
			$query .= " FROM ";
			$query .= " {$wpdb->prefix}ic_pos_expense_details as expense";
			$query .= " WHERE 1=1 ";
			
			if ($start_date && $end_date) {
				$query .= " AND expense_date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$rows = $wpdb->get_var($query);
			$this->print_array($rows);
			return $rows; 
		}
		function get_expense_group_by_category($start_date =NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT ";
			
			$query .= " SUM(ROUND(amount,0)) as total_amount ";
			$query .= ", category.category_name as category_name";
			
			//$query .= " ,mlocation.master_title as location_name ";
			//$query .= " ,mlocation.master_id as master_id ";
			
			$query .= " FROM ";
			$query .= " {$wpdb->prefix}ic_pos_expense_details as expense";
			
			$query .= " LEFT JOIN {$wpdb->prefix}ic_pos_category_expense as category  ON category.id = expense.category_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}ic_pos_master as mlocation  ON mlocation.master_id = expense.location_id ";
			
			
			
			$query .= " WHERE 1=1 ";
			
			if ($start_date && $end_date) {
				$query .= " AND expense_date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			/*Location*/
			$query .= " AND mlocation.master_type='pos_location'";
			if ($location_id>0){
				$query .= " AND  mlocation.master_id ='{$location_id}'";
			}
			
			$query .= " GROUP BY category_id ";
			$query .= " ORDER BY category.category_name ASC ";
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 
		}
		function get_expense_group_by_location($start_date =NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT ";
			$query .= " SUM(ROUND(amount,0)) as total_amount ";
			//$query .= ", category.category_name as category_name";
			
			$query .= " ,mlocation.master_title as location_name ";
			$query .= " ,mlocation.master_id as master_id ";
			
			$query .= " FROM ";
			$query .= " {$wpdb->prefix}ic_pos_expense_details as expense";
			
			//$query .= " LEFT JOIN {$wpdb->prefix}ic_pos_category_expense as category  ON category.id = expense.category_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}ic_pos_master as mlocation  ON mlocation.master_id = expense.location_id ";
			
			$query .= " WHERE 1=1 ";
			
			if ($start_date && $end_date) {
				$query .= " AND expense_date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND mlocation.master_type='pos_location'";
			if ($location_id>0){
				$query .= " AND  mlocation.master_id ='{$location_id}'";
			}
			$query .= " GROUP BY expense.location_id ";
			$query .= " ORDER BY mlocation.master_title ASC ";
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 
		}
		/*Employee*/
		function get_employee_report($start_date=NULL,$end_date=NULL, $employee_id = 0, $location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			$query .= " date_format(posts.post_date , '%Y-%m-%d') as order_date ";
			$query .= ", line_total.meta_value as line_total";
			$query .= ", employee_id.meta_value as employee_id";
			$query .= ", employee_name.meta_value as employee_name";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items  ON order_items.order_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as employee_name  ON employee_name.order_item_id = order_items.order_item_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as employee_id  ON employee_id.order_item_id = order_items.order_item_id ";
			
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as line_total  ON line_total.order_item_id = order_items.order_item_id ";
			

			$query .= " WHERE 1=1 ";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND order_items.order_item_type='line_item'";
			
			$query .= " AND employee_id.meta_key='employee_id'";
			$query .= " AND employee_name.meta_key='employee_name'";
			$query .= " AND line_total.meta_key='_line_total'";
			
			$query .= " ORDER BY  posts.post_date DESC";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 
		}
		/*Employee*/
		function get_employee_report_group_by_employee_name($start_date=NULL,$end_date=NULL, $location_id = 0, $employee_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			//$query .= " date_format(posts.post_date , '%Y-%m-%d') as order_date ";
			$query .= " SUM(ROUND(line_total.meta_value,2)) as line_total";
			$query .= ", employee_id.meta_value as employee_id";
			
			$query .= ", pos_location_name.meta_value as location_name";
			
			$query .= ", employee_name.meta_value as employee_name";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items  ON order_items.order_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as employee_name  ON employee_name.order_item_id = order_items.order_item_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as employee_id  ON employee_id.order_item_id = order_items.order_item_id ";
			
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as line_total  ON line_total.order_item_id = order_items.order_item_id ";
			
			/*Location*/
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_name  ON pos_location_name.post_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";

			

			$query .= " WHERE 1=1 ";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND order_items.order_item_type='line_item'";
			
			$query .= " AND employee_id.meta_key='employee_id'";
			$query .= " AND employee_name.meta_key='employee_name'";
			$query .= " AND line_total.meta_key='_line_total'";
			
			/*Location*/
			$query .= " AND pos_location_name.meta_key ='_pos_location_name'";
			$query .= " AND pos_location_id.meta_key ='_pos_location_id'";
			if ($location_id>0){
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}
			
							
			$query .= " GROUP BY employee_id.meta_value,  pos_location_name.meta_value ";
			$query .= " ORDER BY  posts.post_date DESC";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 
		}
		/*Rating Report*/
		function get_customer_rating_report($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			$query .= " rating.rating ";
			$query .= ", rating.rating_date as rating_date ";
			//$query .= " ,rating.location_id as rl";
			//$query .= " ,mlocation.master_id as ml";
			$query .= " ,rating.location_id as  location_id";
			$query .= " ,mlocation.master_title as location_name ";
			$query .= " ,first_name.meta_value as first_name ";
			$query .= " ,last_name.meta_value as last_name ";
			$query .= " FROM {$wpdb->prefix}ic_pos_customer_rating as rating ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}users as users  ON users.ID = rating.customer_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}usermeta as first_name  ON users.ID = first_name.user_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}usermeta as last_name  ON users.ID = last_name.user_id ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}ic_pos_master as mlocation  ON mlocation.master_id = rating.location_id ";
			
			$query .= " WHERE 1= 1";
			if ($start_date && $end_date) {
				$query .= " AND date_format(rating.rating_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			$query .= " AND first_name.meta_key='first_name'";
			$query .= " AND last_name.meta_key='last_name'";
			$query .= " AND mlocation.master_type='pos_location'";
			if ($location_id>0){
				$query .= " AND rating.location_id IN('{$location_id}')";
			}
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 	
		}
		function get_location_report($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";	
			$query .= " date_format(posts.post_date , '%Y-%m-%d') as order_date ";
			$query .= " ,pos_location_name.meta_value as  location_name ";	
			$query .= " ,order_total.meta_value as  order_total ";	
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_name  ON postmeta.post_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as order_total  ON order_total.post_id = posts.ID ";
			
			$query .= " WHERE 1= 1";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND pos_location_name.meta_key ='_pos_location_name'";
			$query .= " AND order_total.meta_key ='_order_total'";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 	
		}
		function get_location_report_group_by_location_name($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";	
			$query .= " COUNT(*) order_count";
			$query .= " ,SUM(ROUND(order_total.meta_value,2)) order_total";
			$query .= " ,pos_location_name.meta_value as  location_name ";	
			$query .= " ,order_total.meta_value as  order_total ";	
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_name  ON pos_location_name.post_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as order_total  ON order_total.post_id = posts.ID ";
			
			$query .= " WHERE 1= 1";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND  pos_location_name.meta_key ='_pos_location_name'";
			
			//$location_id
			if ($location_id>0){
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}
			$query .= " AND  pos_location_id.meta_key ='_pos_location_id'";
			
			$query .= " AND  order_total.meta_key ='_order_total'";
			$query .= " GROUP BY pos_location_id.meta_value";
			$query .= " Order By pos_location_name.meta_value ASC";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 	
		}
		/*Payment Method*/
		function get_payment_method_report($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";	
			$query .= " COUNT(*) order_count";
			$query .= " ,SUM(ROUND(order_total.meta_value,2)) order_total";
			//$query .= " ,pos_location_name.meta_value as  location_name ";	<br />
		
			$query .= " ,payment_method_title.meta_value as  payment_method_title ";	
			
			$query .= " ,order_total.meta_value as  order_total ";	
			
			$query .= " ,payment_method_title.meta_value as  payment_method_title ";	
		
			
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			/*Location*/
			if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_name  ON pos_location_name.post_id = posts.ID ";
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as order_total  ON order_total.post_id = posts.ID ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as payment_method  ON payment_method.post_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as payment_method_title  ON payment_method_title.post_id = posts.ID ";
			
			
			$query .= " WHERE 1= 1";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			/*Location*/
			if ($location_id>0){
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
				$query .= " AND  pos_location_name.meta_key ='_pos_location_name'";
				$query .= " AND  pos_location_id.meta_key ='_pos_location_id'";
			}
			
			
			$query .= " AND  order_total.meta_key ='_order_total'";
			
			$query .= " AND  payment_method.meta_key ='_payment_method'";
			$query .= " AND  payment_method_title.meta_key ='_payment_method_title'";
			
			
			$query .= " GROUP BY payment_method.meta_value ";
			$query .= " Order By payment_method.meta_value ASC";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 	
		}
		/*Payment Method*/
		function get_payment_method_report_group_by_location_payment_method($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";	
			$query .= " COUNT(*) order_count";
			$query .= " ,SUM(ROUND(order_total.meta_value,2)) order_total";
			$query .= " ,pos_location_name.meta_value as  location_name ";
		
			$query .= " ,payment_method_title.meta_value as  payment_method_title ";	
			
			$query .= " ,order_total.meta_value as  order_total ";	
			
			$query .= " ,payment_method_title.meta_value as  payment_method_title ";	
		
			
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			/*Location*/
			//if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_name  ON pos_location_name.post_id = posts.ID ";
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			//}
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as order_total  ON order_total.post_id = posts.ID ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as payment_method  ON payment_method.post_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as payment_method_title  ON payment_method_title.post_id = posts.ID ";
			
			
			$query .= " WHERE 1= 1";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			/*Location*/
			//if ($location_id>0){
				$query .= " AND  pos_location_name.meta_key ='_pos_location_name'";
				$query .= " AND  pos_location_id.meta_key ='_pos_location_id'";
				//$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			//}
			if ($location_id>0){
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}
			$query .= " AND  order_total.meta_key ='_order_total'";
			
			$query .= " AND  payment_method.meta_key ='_payment_method'";
			$query .= " AND  payment_method_title.meta_key ='_payment_method_title'";
			
			$query .= " GROUP BY payment_method.meta_value,pos_location_id.meta_value    ";
			$query .= " Order By payment_method.meta_value ASC";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 	
		}
		function get_random_color(){
			 return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
		}
		function get_top_product($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT  ";
			//$query .= " date_format(posts.post_date , '%Y-%m-%d') as order_date ";
			$query .= " SUM(ROUND(line_total.meta_value,2)) as line_total";
			$query .= ", order_items.order_item_name as  product_name ";
			
			if ($location_id>0){
				$query .= ", pos_location_name.meta_value as location_name";
			}
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items  ON order_items.order_id = posts.ID ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as line_total  ON line_total.order_item_id = order_items.order_item_id ";
			
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as variation_id  ON variation_id.order_item_id = order_items.order_item_id ";
			$query .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as product_id  ON product_id.order_item_id = order_items.order_item_id ";
			
			/*Location*/
			if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_name  ON pos_location_name.post_id = posts.ID ";
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			
			$query .= " WHERE 1=1 ";
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND order_items.order_item_type='line_item'";
			$query .= " AND line_total.meta_key='_line_total'";
			
			/*Location*/
			if ($location_id>0){
				$query .= " AND pos_location_name.meta_key ='_pos_location_name'";
				$query .= " AND  pos_location_id.meta_key ='_pos_location_id'";
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}

			
			
			$query .= " AND variation_id.meta_key='_variation_id'";
			$query .= " AND product_id.meta_key='_product_id'";
			
			$query .= " GROUP BY  product_id.meta_value, variation_id.meta_value";
			
			$query .= " ORDER BY  line_total DESC";
			$query .= " LIMIT 5";
			
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 
		}
		function get_repeat_customer($start_date=NULL,$end_date=NULL,$location_id = 0){
		global $wpdb;
			$query_2 ="";

			$query_2 = "";
			$query_2 .= " SELECT  ";
			$query_2 .= " billing_email.meta_value  as billing_email";
			$query_2 .= " FROM {$wpdb->prefix}posts as posts ";
			$query_2 .= " LEFT JOIN {$wpdb->prefix}postmeta as billing_email  ON billing_email.post_id = posts.ID ";
			
			if ($location_id>0){
				$query_2 .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			$query_2 .= " WHERE 1=1 ";
			if ($location_id>0){
				$query_2 .= " AND  pos_location_id.meta_value ='{$location_id}'";
				$query_2 .= " AND  pos_location_id.meta_key ='_pos_location_id'";
			}
			if ($start_date ) {
				$query_2 .= " AND date_format(posts.post_date , '%Y-%m-%d') > '{$start_date}' ";
			}
			$query_2 .= " AND billing_email.meta_key='_billing_email'";			
			//echo $query_2;

			$query = "";
			$query .= " SELECT  ";
			//$query .= " billing_email.meta_value  as billing_email";
			$query .= " COUNT(*) as count";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as billing_email  ON billing_email.post_id = posts.ID ";
			
			if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			
			$query .= " WHERE 1=1 ";
			$query .= " AND billing_email.meta_key='_billing_email'";
			if ($location_id>0){
				$query .= " AND  pos_location_id.meta_key ='_pos_location_id'";
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}

			
			
			if ($start_date && $end_date) {
				$query .= " AND billing_email.meta_value  IN ({$query_2})";
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$rows = $wpdb->get_var($query);
			//$this->print_array($rows);
			return $rows; 
		}
		function get_new_customer($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query_2 ="";

			$query_2 = "";
			$query_2 .= " SELECT  ";
			$query_2 .= " billing_email.meta_value  as billing_email";
			$query_2 .= " FROM {$wpdb->prefix}posts as posts ";
			$query_2 .= " LEFT JOIN {$wpdb->prefix}postmeta as billing_email  ON billing_email.post_id = posts.ID ";
			
			if ($location_id>0){
				$query_2 .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			$query_2 .= " WHERE 1=1 ";
			if ($location_id>0){
				$query_2 .= " AND  pos_location_id.meta_value ='{$location_id}'";
				$query_2 .= " AND  pos_location_id.meta_key ='_pos_location_id'";
			}
			if ($start_date ) {
				$query_2 .= " AND date_format(posts.post_date , '%Y-%m-%d') > '{$start_date}' ";
			}
			$query_2 .= " AND billing_email.meta_key='_billing_email'";			
			//echo $query_2;

			$query = "";
			$query .= " SELECT  ";
			//$query .= " billing_email.meta_value  as billing_email";
			$query .= " COUNT(*) as count";
			$query .= " FROM {$wpdb->prefix}posts as posts ";
			$query .= " LEFT JOIN {$wpdb->prefix}postmeta as billing_email  ON billing_email.post_id = posts.ID ";
			
			if ($location_id>0){
				$query .= " LEFT JOIN {$wpdb->prefix}postmeta as pos_location_id  ON pos_location_id.post_id = posts.ID ";
			}
			
			$query .= " WHERE 1=1 ";
			$query .= " AND billing_email.meta_key='_billing_email'";
			if ($location_id>0){
				$query .= " AND  pos_location_id.meta_key ='_pos_location_id'";
				$query .= " AND  pos_location_id.meta_value ='{$location_id}'";
			}

			
			
			if ($start_date && $end_date) {
				$query .= " AND billing_email.meta_value NOT IN ({$query_2})";
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$rows = $wpdb->get_var($query);
			//$this->print_array($rows);
			return $rows; 
			
		}
		function get_booking_report($start_date=NULL,$end_date=NULL,$location_id = 0){
			global $wpdb;
			$query = "";
			$query .= " SELECT ";
			$query .= " booking.booking_date as booking_date ";
			$query .= " ,booking.first_name as first_name ";
			$query .= " ,booking.last_name as last_name ";
			$query .= " ,booking.email_address as email_address ";
			$query .= " ,booking.mobile_no as mobile_no ";
			$query .= " ,mlocation.master_title as location_name ";
			$query .= " FROM {$wpdb->prefix}ic_pos_booking as booking ";
			$query .= " LEFT JOIN {$wpdb->prefix}ic_pos_master as mlocation  ON mlocation.master_id = booking.location_id ";

			
			$query .= " WHERE 1=1 ";
			if ($location_id>0){
				$query .= " AND  booking.location_id ='{$location_id}'";
			}
			if ($start_date && $end_date) {
				$query .= " AND date_format(posts.post_date , '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			$query .= " AND mlocation.master_type='pos_location'";
			$query .= " ORDER BY booking.booking_date ASC";
			$rows = $wpdb->get_results($query);
			//$this->print_array($rows);
			return $rows; 
			
		}
		function create_table($rows= array(),$columns= array(), $report_name){
		}
		
	}
}

?>