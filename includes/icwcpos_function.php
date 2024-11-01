<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_FUNCTION')){
	/*
	 * Class Name ICWCPOS_FUNCTION
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
		}
		
		function get_woo_price($price = 0){
			$new_price = 0;
			if ($price){
				$new_price = wc_price($price);
			}else{
				$new_price = wc_price($new_price);
			}
			return $new_price;
		}
		
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest = implode(",", $newRequest);
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
		}
		
		function print_array($ar = NULL,$display = true){
			if($ar){
				$output = "<pre>";
				$output .= print_r($ar,true);
				$output .= "</pre>";
				
				if($display){
					echo $output;
				}else{
					return $output;
				}
			}
		}
		
		function array_error_log($ar = NULL){
			error_log(print_r($ar,true));
		}
		
		function get_pagination($total_pages = 50,$limit = 10,$adjacents = 3,$targetpage = "admin.php?page=RegisterDetail",$request = array()){		
				
				if(count($request)>0){
					unset($request['p']);
					//$new_request = array_map(create_function('$key, $value', 'return $key."=".$value;'), array_keys($request), array_values($request));
					//$new_request = implode("&",$new_request);
					//$targetpage = $targetpage."&".$new_request;
				}
				
				
				/* Setup vars for query. */
				//$targetpage = "admin.php?page=RegisterDetail"; 	//your file name  (the name of this file)										
				/* Setup page vars for display. */
				if(isset($_REQUEST['p'])){
					$page = $_REQUEST['p'];
					$_GET['p'] = $page;
					$start = ($page - 1) * $limit; 			//first item to display on this page
				}else{
					$page = false;
					$start = 0;	
					$page = 1;
				}
				
				if ($page == 0) $page = 1;					//if no page var is given, default to 1.
				$prev = $page - 1;							//previous page is page - 1
				$next = $page + 1;							//next page is page + 1
				$lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
				$lpm1 = $lastpage - 1;						//last page minus 1
				
				
				
				$label_previous = __('previous', 'icwcpos');
				$label_next = __('next', 'icwcpos');
				
				/* 
					Now we apply our rules and draw the pagination object. 
					We're actually saving the code to a variable in case we want to draw it more than once.
				*/
				$pagination = "";
				if($lastpage > 1)
				{	
					$pagination .= "<div class=\"pagination\">";
					//previous button
					if ($page > 1) 
						$pagination.= "<a href=\"$targetpage&p=$prev\" data-p=\"$prev\">{$label_previous}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_previous}</span>\n";	
					
					//pages	
					if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
					{	
						for ($counter = 1; $counter <= $lastpage; $counter++)
						{
							if ($counter == $page)
								$pagination.= "<span class=\"current\">$counter</span>\n";
							else
								$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
						}
					}
					elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
					{
						//close to beginning; only hide later pages
						if($page < 1 + ($adjacents * 2))		
						{
							for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//in middle; hide some front and some back
						elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
							$pagination.= "...";
							$pagination.= "<a href=\"$targetpage&p=$lpm1\" data-p=\"$lpm1\">$lpm1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=$lastpage\" data-p=\"$lastpage\">$lastpage</a>\n";		
						}
						//close to end; only hide early pages
						else
						{
							$pagination.= "<a href=\"$targetpage&p=1\" data-p=\"1\">1</a>\n";
							$pagination.= "<a href=\"$targetpage&p=2\" data-p=\"2\">2</a>\n";
							$pagination.= "...";
							for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
							{
								if ($counter == $page)
									$pagination.= "<span class=\"current\">$counter</span>\n";
								else
									$pagination.= "<a href=\"$targetpage&p=$counter\" data-p=\"$counter\">$counter</a>\n";					
							}
						}
					}
					
					//next button
					if ($page < $counter - 1) 
						$pagination.= "<a href=\"$targetpage&p=$next\" data-p=\"$next\">{$label_next}</a>\n";
					else
						$pagination.= "<span class=\"disabled\">{$label_next}</span>\n";
					$pagination.= "</div>\n";		
				}
				return $pagination;
			
		}//End Get Pagination
		
		function country_states($country_key = 'billing_country', $state_key = 'billing_state',$billing_country = '',$billing_state = '', $maxlength = 100){
			$billing_country =  $this->get_request ($country_key,$billing_country);
			$states = $this->get_wc_states($billing_country);
			$output = "";
			if(is_array($states) and count($states) > 0){
				$options = isset($field_value['options']) ? $field_value['options'] : array();
				$output .= "<select name=\"{$state_key}\" id=\"{$state_key}\">";
				foreach($states as $option_value => $option_label){
					if($billing_state == $option_value){
						$output .= "<option value=\"{$option_value}\" selected=\"selected\">{$option_label}</option>";
					}else{
						$output .= "<option value=\"{$option_value}\">{$option_label}</option>";
					}
				}
				$output .= "</select>";
			}else{
				$output .= "<input type=\"text\" id=\"{$state_key}\" name=\"{$state_key}\ maxlength=\"{$maxlength}\">";
			}			
			$output .= '<br/><span class="description">'.__('State / County or state code','icwcpos').'</span>';
			return $output;
		}
		
		
		
		function plugin_submenu_list($parent_slug = '', $admin_pages = array()){
			global $submenu;
			
			$submenu_list = isset($submenu[$parent_slug]) ? $submenu[$parent_slug] : array();
			
			foreach($submenu_list as $key => $menu_list){
				$admin_pages[] = isset($menu_list[2]) ? $menu_list[2] : '';
			}
						
			$this->constants['plugin_submenu'] = $admin_pages;
			
			return $admin_pages;
		}
		
		function get_plugin_url(){
			if(!isset($this->constants['plugins_url'])){
				$plugin_file 	= $this->constants['plugin_file'];
				$plugins_url  	= plugins_url('/', $plugin_file);
				$this->constants['plugins_url'] = $plugins_url;
			}
			return $this->constants['plugins_url'];
		}
		
		/**
		* get_wc_states
		* This function is used to Get WC States.
		* @param string $country_code
		*/		
		function get_wc_states($country_code){
			global $woocommerce;
			return isset($woocommerce) ? $woocommerce->countries->get_states($country_code) : array();
		}
		
		
		
		function set_screen_option_filter($admin_page = ''){
			global $plugin_page,$the_parent,$page_hook;
			$page_hook = get_plugin_page_hook($plugin_page, $the_parent);
			add_action( "load-$page_hook", array($this, 'add_screen_option'));
		}
		
		function add_screen_option() {
			global $ic_option_per_page_key;
			
			$admin_page = $this->constants['admin_page'];
			$list_per_page  = isset($this->constants['list_per_page']) ? $this->constants['list_per_page'] : 10;
			
			$ic_option_per_page_key = str_replace("-","_",'list_per_page_'.$admin_page);
			
			$option = 'per_page';
			
			$args = array(
				'label' => __('Par Page','icwcpos'),
				'default' => $list_per_page,
				'option' => $ic_option_per_page_key
			);
			add_screen_option( $option, $args );
					
		}		
		
		function set_screen_option($status= '', $option= '', $value= '') {
		  return $value;
		}
		
		function get_user_option($default_per_page = 10){
			global $ic_option_per_page_key;						
			$per_page = (int) get_user_option($ic_option_per_page_key);
			$per_page = ($per_page == "" || $per_page == 0) ? $default_per_page : $per_page;
			return $per_page;
		}
		
		function get_login_user_id(){
			return get_current_user_id();
		}
		
		function get_user_name_by_id($id = 0){
			$user_info = get_userdata($id);
			return $userloginname = $user_info->user_login;
		}
		
		function get_location_id(){
			$location_id = isset($_SESSION['current_location_id'])?$_SESSION['current_location_id']:0;
			return $location_id;
		}
		
		function create_hidden_fields($request = array(), $type = "hidden"){
			$output_fields = "";
			//$this->print_array($request); return '';
			foreach($request as $key => $value):
				if(is_array($value)){
					foreach($value as $akey => $avalue):
						if(is_array($avalue)){
							$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"".implode(",",$avalue)."\" />";
						}else{
							$output_fields .=  "<input type=\"{$type}\" name=\"{$key}[{$akey}]\" value=\"{$avalue}\" />";
						}
					endforeach;
				}else{
					$output_fields .=  "\n<input type=\"{$type}\" name=\"{$key}\" value=\"{$value}\" />";
				}
			endforeach;
			return $output_fields;
		}
		
		function get_popup($title='popup title',$popup_id = 'popup_id'){
			?>
			<div id="<?php echo $popup_id;?>" class="ic_popup_box <?php echo $popup_id;?>">
				<a class="popup_close" title="Close popup"></a>
				<h4><?php echo $title;?></h4>
				
				<div class="popup_content">
					<?php echo do_action('ic_popup_content_'.$popup_id);?>
					<div style=" text-align:right;" class="buttons buttons_<?php echo $popup_id;?>">                        
						<?php echo do_action('ic_popup_buttons_'.$popup_id);?>
					</div>
					<div class="clear"></div>
				</div>
			 </div>
             <?php
		}
		
		function get_popup_mask(){
			?><div class="ic_popup_mask"></div><?php
		}
		
		/**
		 * Get Address Fields for the edit user pages.
		 *
		 * @return array Fields to display which are filtered through woocommerce_customer_meta_fields before being returned
		 */
		public function get_customer_meta_fields() {
				$show_fields = array();
				$show_billing_fields = array(
					'title' => __( 'Customer billing address', 'icwcpos'),
					'fields' => array(
						
						
						'billing_first_name' => array(
							'label'       => __( 'First name', 'icwcpos'),
							'description' => '',
							'required'    => true
						),
						'billing_last_name' => array(
							'label'       => __( 'Last name', 'icwcpos'),
							'description' => '',
							'required'    => true
						),
						
						'billing_company' => array(
							'label'       => __( 'Company', 'icwcpos'),
							'description' => '',
							'required'    => false
						),
						
						'billing_address_1' => array(
							'label'       => __( 'Address line 1', 'icwcpos'),
							'description' => '',
							'required'    => true,
						),
						'billing_address_2' => array(
							'label'       => __( 'Address line 2', 'icwcpos'),
							'description' => '',
							'required'    => false
						),
						'billing_city' => array(
							'label'       => __( 'City', 'icwcpos'),
							'description' => '',
							'required'    => false
						),
						'billing_postcode' => array(
							'label'       => __( 'Postcode / ZIP', 'icwcpos'),
							'description' => '',
							'required'    => true
						),
						'billing_country' => array(
							'label'       => __( 'Country', 'icwcpos'),
							'description' => '',
							'class'       => 'js_field-country',
							'type'        => 'select',
							'options'     => array( '' => __( 'Select a country&hellip;', 'icwcpos') ) + WC()->countries->get_allowed_countries(),
							'required'    => true
						),
						'billing_state' => array(
							'label'       => __( 'State / County', 'icwcpos'),
							'description' => __( 'State / County or state code', 'icwcpos'),
							'class'       => '',
							'required'    => true
						),
						'billing_phone' => array(
							'label'       => __( 'Phone', 'icwcpos'),
							'description' => '',
							'required'    => true
						),
						'billing_email' => array(
							'label'       => __( 'Email address', 'icwcpos'),
							'description' => '',
							'required'    => true
						)
					)
				);
				$show_fields['billing'] = $show_billing_fields;
				
				
				$ship_to_countries  = get_option( 'woocommerce_ship_to_countries' );
				if($ship_to_countries != 'disabled'){
					$show_shipping_fields = array(
						'title' => __( 'Ship to a different address?', 'icwcpos'),
						'fields' => array(
							/*'copy_billing' => array(
								'label'       => __( 'Copy from billing address', 'icwcpos'),
								'description' => '',
								'class'       => 'js_copy-billing',
								'type'        => 'button',
								'text'        => __( 'Copy', 'icwcpos'),
							),*/
							'shipping_first_name' => array(
								'label'       => __( 'First name', 'icwcpos'),
								'description' => '',
								'required'    => true
							),
							'shipping_last_name' => array(
								'label'       => __( 'Last name', 'icwcpos'),
								'description' => '',
								'required'    => true
							),
							'shipping_company' => array(
								'label'       => __( 'Company', 'icwcpos'),
								'description' => '',
							),
							'shipping_address_1' => array(
								'label'       => __( 'Address line 1', 'icwcpos'),
								'description' => '',
								'required'    => true
							),
							'shipping_address_2' => array(
								'label'       => __( 'Address line 2', 'icwcpos'),
								'description' => '',
							),
							'shipping_city' => array(
								'label'       => __( 'City', 'icwcpos'),
								'description' => '',
								'required'    => true
							),
							'shipping_postcode' => array(
								'label'       => __( 'Postcode / ZIP', 'icwcpos'),
								'description' => '',
								'required'    => true
							),
							'shipping_country' => array(
								'label'       => __( 'Country', 'icwcpos'),
								'description' => '',
								'class'       => 'js_field-country',
								'type'        => 'select',
								'options'     => array( '' => __( 'Select a country&hellip;', 'icwcpos') ) + WC()->countries->get_shipping_countries(),
								'required'    => true
							),
							'shipping_state' => array(
								'label'       => __( 'State / County', 'icwcpos'),
								'description' => __( 'State / County or state code', 'icwcpos'),
								'class'       => 'js_field-state',
								'required'    => true
							),
						)
					);
					$show_fields['shipping'] = $show_shipping_fields;
				}
			
			foreach ( $show_fields as $fieldset_key => $fieldset ) :
				foreach ( $fieldset['fields'] as $meta_key => $field ) :
					$field['type'] = isset($field['type']) ? $field['type'] : 'text';
					$field['class'] = isset($field['class']) ? $field['class'] : '';
					$field['label'] = isset($field['label']) ? $field['label'] : $fieldset_key;
					$field['required'] = isset($field['required']) ? $field['required'] : false;
					$field['description'] = isset($field['description']) ? $field['description'] : '';
					$field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
					
					$show_fields[$fieldset_key]['fields'][$meta_key] = $field;
				endforeach;				
			endforeach;
			
			$show_fields = apply_filters('woocommerce_customer_meta_fields',$show_fields);
			
			$show_fields = apply_filters('woocommerce_checkout_fields',$show_fields);
			
			return $show_fields;
		}
		
		/*Expanse function*/
		function get_category_expense($id = 0, $rtype="default"){
			global $wpdb;
			$data  = array();
			$query = "";
			$query = "SELECT ";
			if ($rtype =="name"){
				$query .= " category_name ";		
			}
			else{
				$query .= " * ";	
			}
			$query .= " FROM ";
			$query .= " {$wpdb->prefix}ic_pos_category_expense ";
			$query .= " WHERE 1 = 1 ";
			
			if ($id>0){
				$query .= " AND id = " . $id;
			}
			
			/*Category Name*/
			if ($rtype =="name"){
				$rows = $wpdb->get_var($query );
			}else{
				$rows = $wpdb->get_results($query );	
			}
			/*Default*/
			if ($rtype =="name"){
				$data = $rows;
			}else if ($rtype =="DDL"){
				/*Drop Dowm*/
				foreach($rows  as $key=>$value){
					$data[$value->id] = $value->category_name;
				}
			}
			else{
				$data = $rows;
				
			}
			return $data;
		}
		
		
		
		function ExportToCsv($filename = 'export.csv',$rows,$columns,$format="csv"){				
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			if($format=="xls"){
				$csv_terminated = "\r\n";
				$csv_separator = "\t";
			}
				
			foreach($columns as $key => $value):
				$l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $csv_enclosed;
				$schema_insert .= $l;
				$schema_insert .= $csv_separator;
			endforeach;// end for
		 
		   $out = trim(substr($schema_insert, 0, -1));
		   $out .= $csv_terminated;
			
			//printArray($rows);
			
			for($i =0;$i<count($rows);$i++){
				
				//printArray($rows[$i]);
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						
						
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $csv_enclosed;
							}
						 }else{
							$schema_insert .= '';
						 }
						
						
						
						if ($j < $fields_cnt - 1)
						{
							$schema_insert .= $csv_separator;
						}
						$j++;
				}
				$out .= $schema_insert;
				$out .= $csv_terminated;
			}
			
			if($format=="csv"){
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));	
				header("Content-type: text/x-csv");
				header("Content-type: text/csv");
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=$filename");
			}elseif($format=="xls"){
				
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				header("Pragma: no-cache");
				header("Expires: 0");
			}
			
			echo $out;
			exit;
		 
		}
		
		function get_new_sequential_number(){
			global $wpdb;
			$location_id   = $this->get_location_id();
			$new_number 	= "";
			$prefix 		= "";
			$suffix 		= "";
			$start_number  = "";
			$number_length = "";
			$str_zero 	  = "";
			
			$query = "";
			$query .= " SELECT * FROM {$wpdb->prefix}ic_pos_sequential_invoice_number ";
			$query .= " WHERE 1=1 ";
			$query .= " AND  location_id='{$location_id}'";
			
			
			
			$row = $wpdb->get_row($query);
			if($row){
				$prefix		 =  isset($row->prefix)?$row->prefix:'';
				$suffix 		 =  isset($row->suffix)?$row->suffix:'';
				$start_number   =  isset($row->start_number)?$row->start_number:1;
				$number_length  =  isset($row->number_length)?$row->number_length:1;
				
				if (strlen($start_number)  < $number_length ){
					$new_length = $number_length  - strlen($start_number);
					for($i= 0; $i<$new_length;$i++){
						 $str_zero .=  "0"; 
						//echo  $i; 
					}
				}
				
				$new_number  = $prefix. $str_zero . $start_number .$suffix ;
				return $new_number;
			}
			
			return 0;
			
		}
		
		function get_update_sequential_number(){
			global $wpdb;
			$location_id   = $this->get_location_id();
			$start_number = 0;
			
			$query = "";
			$query .= " SELECT * FROM {$wpdb->prefix}ic_pos_sequential_invoice_number ";
			$query .= " WHERE 1=1 ";
			$query .= " AND  location_id='{$location_id}'";
			$row = $wpdb->get_row($query);
			
			$start_number   =  isset($row->start_number)?$row->start_number:0;
			
			$last_number = $start_number+1;
			
			$query = "";
			$query .= " UPDATE  {$wpdb->prefix}ic_pos_sequential_invoice_number SET start_number={$last_number}";
			$query .= " WHERE 1=1 ";
			$query .= " AND  location_id='{$location_id}'";
			
			$row = $wpdb->query($query);
		}
		
		
		
		/*End Expanse function*/
		/*Sequnatial*/
		function get_pos_location(){
			global $wpdb;
			$rows   = array();
			$query  = "";
			$query  .= "SELECT * FROM {$wpdb->prefix}ic_pos_master as master";
			$query  .= " WHERE 1=1 ";
			$query  .= " AND master.master_type = 'pos_location'";
			$query  .= " ORDER BY master_title asc";
			
			$rows = $wpdb->get_results($query);
			
			//$this->print_array($rows);
			
			return $rows ;
				
		}
		
		function get_locations($id=NULL){
			global $wpdb;
			
			$ic_pos_master = $wpdb->prefix.'ic_pos_master';
			if($wpdb->get_var("SHOW TABLES LIKE '$ic_pos_master'") != $ic_pos_master) {
				return array();
			}
		
			$query = " SELECT master_id,master_title FROM  {$wpdb->prefix}ic_pos_master WHERE master_type ='pos_location' ";
			
			if ($id and $id > 0){
				$query .= " AND master_id = {$id}";
			}
			
			$results = $wpdb->get_results( $query);	
			
			$warehouse_list = array();
			
			foreach($results as $k=>$v) {
				$warehouse_list[$v->master_id] =$v->master_title;
			}
			
			if($id and $id > 0){
				return isset($warehouse_list[$id]) ? $warehouse_list[$id] : '';
			}else{
				return $warehouse_list;
			}
		}
		
		/*
		 * get_items_id_list
		 * 
		 * @param array $order_items 
		 * @param array  $field_key 
		 * @param integer  $return_default  
		 * @param string $return_formate
		 * @return string
		 */
		function get_items_id_list($order_items = array(),$field_key = 'order_id', $return_default = '-1' , $return_formate = 'string'){
			$list 	= array();
			$string = $return_default;
			if(count($order_items) > 0){
				foreach ($order_items as $key => $order_item) {
					if(isset($order_item->$field_key)){
						if(!empty($order_item->$field_key))
							$list[] = $order_item->$field_key;
					}
				}
				
				$list = array_unique($list);
				
				if($return_formate == "string"){
					$string = implode(",",$list);
				}else{
					$string = $list;
				}
			}
			return $string;
		}
		
		function print_amchart_language(){
			$admin_page  = $this->constants['admin_page'];
			if ($admin_page =="icwcpos_page"){
				echo $this->get_amchart_language();
			}
		}
		
		function get_amchart_language(){
			
			$months = array();
			$months[1] = __("January",'icwcpos');
			$months[2] = __("February",'icwcpos');
			$months[3] = __("March",'icwcpos');
			$months[4] = __("April",'icwcpos');
			$months[5] = __("May",'icwcpos');
			$months[6] = __("June",'icwcpos');
			$months[7] = __("July",'icwcpos');
			$months[8] = __("August",'icwcpos');
			$months[9] = __("September",'icwcpos');
			$months[10] = __("October",'icwcpos');
			$months[11] = __("November",'icwcpos');
			$months[12] = __("December",'icwcpos');
			
			$shortMonthNames = array();
			$shortMonthNames[1] = __("Jan.",'icwcpos');
			$shortMonthNames[2] = __("Feb.",'icwcpos');
			$shortMonthNames[3] = __("March",'icwcpos');
			$shortMonthNames[4] = __("April",'icwcpos');
			$shortMonthNames[5] = __("May.",'icwcpos');
			$shortMonthNames[6] = __("Jun.",'icwcpos');
			$shortMonthNames[7] = __("Jul.",'icwcpos');
			$shortMonthNames[8] = __("Aug.",'icwcpos');
			$shortMonthNames[9] = __("Sep.",'icwcpos');
			$shortMonthNames[10] = __("Oct.",'icwcpos');
			$shortMonthNames[11] = __("Nov.",'icwcpos');
			$shortMonthNames[12] = __("Dec.",'icwcpos');
			
			$dayNames = array();
			$dayNames[] = __("Monday",'icwcpos');
			$dayNames[] = __("Tuesday",'icwcpos');
			$dayNames[] = __("Wednesday",'icwcpos');
			$dayNames[] = __("Thursday",'icwcpos');
			$dayNames[] = __("Friday",'icwcpos');
			$dayNames[] = __("Saturday",'icwcpos');
			$dayNames[] = __("Sunday",'icwcpos');
			
			$shortDayNames = array();
			$shortDayNames[] = __("Mond.",'icwcpos');
			$shortDayNames[] = __("Tue.",'icwcpos');
			$shortDayNames[] = __("Wed.",'icwcpos');
			$shortDayNames[] = __("Thu.",'icwcpos');
			$shortDayNames[] = __("Fri.",'icwcpos');
			$shortDayNames[] = __("Sat.",'icwcpos');
			$shortDayNames[] = __("Sun.",'icwcpos');
			
			$locale = get_locale();
			$locales = explode("_",$locale);
			$language	= isset($locales[0]) ? $locales[0] : 'en';
			
			$output = "<script type=\"text/javascript\">";
			$output .= "\n";
			$output .= "AmCharts.translations.{$language} = {";
			$output .= '"monthNames":["'.implode('","',$shortMonthNames).'"],';
			$output .= '"shortMonthNames":["'.implode('","',$months).'"],';
			$output .= '"dayNames":["'.implode('","',$dayNames).'"],';
			$output .= '"shortDayNames":["'.implode('","',$shortDayNames).'"],';
			$output .= '"zoomOutText":"'. __("Zoom Out",'icwcpos').'"';
			$output .= '};';
			
			$output .= "\n";
			
			
			$output .= 'if ( !AmCharts.translations[ "export" ] ) {';
				$output .= 'AmCharts.translations[ "export" ] = {}';
			$output .= '};';
			
			$output .= "\n";
			
			$output .= 'AmCharts.translations["export"]["'.$language.'"] = {';
				$output .= '"fallback.save.text": "'.__("CTRL + C to copy the data into the clipboard.",'icwcpos').'"';
				$output .= ',"fallback.save.image": "'.__("Rightclick -> Save picture as... to save the image.",'icwcpos').'"';
		
				$output .= ',"capturing.delayed.menu.label": "'.__("{{duration}}",'icwcpos').'"';
				$output .= ',"capturing.delayed.menu.title": "'.__("Click to cancel",'icwcpos').'"';
		
				$output .= ',"menu.label.print": "'.__("Print",'icwcpos').'"';
				$output .= ',"menu.label.undo": "'.__("Undo",'icwcpos').'"';
				$output .= ',"menu.label.redo": "'.__("Redo",'icwcpos').'"';
				$output .= ',"menu.label.cancel": "'.__("Cancel",'icwcpos').'"';
		
				$output .= ',"menu.label.save.image": "'.__("Download as ...",'icwcpos').'"';
				$output .= ',"menu.label.save.data": "'.__("Save as ...",'icwcpos').'"';
		
				$output .= ',"menu.label.draw": "'.__("Annotate ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.change": "'.__("Change ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.add": "'.__("Add ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.shapes": "'.__("Shape ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.colors": "'.__("Color ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.widths": "'.__("Size ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.opacities": "'.__("Opacity ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.text": "'.__("Text",'icwcpos').'"';
		
				$output .= ',"menu.label.draw.modes": "'.__("Mode ...",'icwcpos').'"';
				$output .= ',"menu.label.draw.modes.pencil": "'.__("Pencil",'icwcpos').'"';
				$output .= ',"menu.label.draw.modes.line": "'.__("Line",'icwcpos').'"';
				$output .= ',"menu.label.draw.modes.arrow": "'.__("Arrow",'icwcpos').'"';
			$output .= '}';
			$output .= '</script>';
			return $output;
		}
		
	}/*End Class*/
}
