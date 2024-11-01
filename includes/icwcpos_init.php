<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_INIT')){
	/*
	 * Class Name ICWCPOS_INIT
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_INIT extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
			add_action('init', 							 array(&$this,   'init'),21);
			add_action('wp_ajax_point_of_sale',  			array(&$this,	'call_ajax_action'));
			add_action('wp_ajax_nopriv_point_of_sale',  	 array(&$this,	'call_ajax_action'));
			add_action('admin_enqueue_scripts',  			array(&$this,	'admin_enqueue_scripts'));
			add_action('admin_menu', 					   array(&$this,	'admin_menu'));
			add_filter('set-screen-option',				array(&$this,   'set_screen_option'),101,3);			
			add_action('icwcpos_template_start', 		   array($this,    'icwcpos_template_start'));
			
			include_once('icwcpos_front_product_price.php');
			$newobj = new ICWCPOS_FRONT_PRODUCT_PRICE($this->constants);
		}
		
		
		function admin_enqueue_scripts(){			
			$admin_page  = $this->constants['admin_page'];
			$admin_pages = $this->constants['plugin_submenu'];
			$page  		= $this->constants['admin_page'];
			
			if(!in_array($admin_page,$admin_pages)){
				return false;
			}
			
			$locale = get_locale();
			$locales = explode("_",$locale);
			$language	= isset($locales[0]) ? $locales[0] : 'en';
			
			$plugin_key 	= $this->constants['plugin_key'];
			$assets_url  	= $this->get_plugin_url();
			$css_url  	   = $assets_url.'assets/css/';
			$js_url  		= $assets_url.'assets/js/';
			
			$this->constants['assets_url'] = $assets_url;
			$this->constants['css_url'] = $css_url;
			$this->constants['js_url'] = $js_url;
			$this->constants['language'] = $language;
			
			$wp_localize_data = array();			
			$wp_localize_data['ajax_url'] 	= admin_url('admin-ajax.php');
			$wp_localize_data['ajax_action'] = $this->constants['ajax_action'];
			$wp_localize_data['admin_url'] 	  = admin_url('admin.php');
			$wp_localize_data['language'] 	  = $language;
			
			$wp_localize_data['location_collection'] 	  = __('Location Collection','icwcpospro_textdomains');
			$wp_localize_data['success_message'] 	  = __('Successfully Updated.','icwcpos');
			$wp_localize_data['please_wait'] 	  = __('Please Wait!','icwcpos');
			$wp_localize_data['location_collection'] 	  = __('Location Collection','icwcpos');
			
			$wp_localize_data = apply_filters('icwcpos_wp_localize_script',$wp_localize_data, $this->constants);
					
			wp_enqueue_script($plugin_key.'_admin_scripts',  $js_url.'scripts.js',array('jquery'));
			wp_enqueue_script($plugin_key.'_admin_popup_scripts',  $js_url.'popup.js');
			wp_localize_script($plugin_key.'_admin_scripts','ic_ajax_object',$wp_localize_data);
			
			wp_enqueue_style($plugin_key.'_bootstrap',  $css_url.'bootstrap.min.css');
			wp_enqueue_style($plugin_key.'_fontawesome',  $css_url.'font-awesome.min.css');
			wp_enqueue_style($plugin_key.'_admin_style',  $css_url.'admin_style.css');
			
			do_action('icwcpos_admin_enqueue_scripts',$this->constants, $admin_page);
			
			if ($admin_page =="icwcpos_page"){
				wp_enqueue_style($plugin_key.'_mpage', 				'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
				wp_enqueue_style($plugin_key.'_amcharts_export', 	  'https://www.amcharts.com/lib/3/plugins/export/export.css');				
				wp_enqueue_script($plugin_key.'_amcharts',  			'https://www.amcharts.com/lib/3/amcharts.js');
				wp_enqueue_script($plugin_key.'_amcharts_serial',    'https://www.amcharts.com/lib/3/serial.js');			
				wp_enqueue_script($plugin_key.'_amcharts_export',  	 'https://www.amcharts.com/lib/3/plugins/export/export.min.js');
				wp_enqueue_script($plugin_key.'_amcharts_light',  	  'https://www.amcharts.com/lib/3/themes/light.js');
				wp_enqueue_script($plugin_key.'_admin_dashbaord',    $js_url.'icwcpos_admin_dashbaord.js');
				add_action('admin_head', 			   array($this, 'print_amchart_language'));
				
			}
		}
		
		
		/**
		* Function name admin_menu
		*/
		function admin_menu(){
			$plugin_role 	   = $this->constants['plugin_role'];
			$constants 		 = $this->constants;
			$plugin_key 		= $this->constants['plugin_key'];
			$parent_slug 	   = $this->constants['parent_slug'];
			$assets_url  		= $this->get_plugin_url();
			
			$permalink 		= get_option('wc_permalink_point_of_sale', '');
			$permalink 		= empty($permalink) ? 'point-of-sale' : $permalink;
			
			$site_url 		 = get_site_url();
			$href 			 = $site_url.'/'.$permalink;
			
			do_action('icwcpos_admin_menu_before',$this->constants, $parent_slug);
			
			add_menu_page(__('Point of Sale','icwcpos'), __('Point of Sale','icwcpos'), $plugin_role, $parent_slug,  array(&$this,'admin_pages'),  $assets_url.'assets/images/icon-inventory.png','57.28' );
			add_submenu_page($parent_slug, __('Dashboard','icwcpos'), 	   __('Dashboard','icwcpos'),$plugin_role,$plugin_key.'_page',array(&$this,'admin_pages'));
			
			add_submenu_page($parent_slug, __('View POS','icwcpos'), 	   __('View POS','icwcpos'),$plugin_role,$href, '');
			
			
			do_action('icwcpos_admin_menu_after',$this->constants, $parent_slug);
			
			$this->plugin_submenu_list($parent_slug);
			
			$admin_pages = $this->constants['plugin_submenu'];
			$admin_page = $this->constants['admin_page'];
			if(in_array($admin_page,$admin_pages)){
				switch($admin_page){
					case "_icwcpos_location_page":
					case "icwcpos_product_price_page":
						$form_action = $this->get_request('form_action','list');
						if($form_action == 'list'){
							$this->set_screen_option_filter($admin_page);
						}
						break;
				}	
			}
			
			do_action('icwcpos_admin_menu_bottom',$this->constants, $parent_slug);
		}
		
		function admin_pages(){
			$admin_page 	   = $this->constants['admin_page'];
			
			do_action('icwcpos_admin_pages',$this->constants, $admin_page);
			
			switch($admin_page){
				case "icwcpos_page":
					include_once('icwcpos_admin_dashboard.php');
					$obj = new ICWCPOS_ADMIN_DASHBOARD($this->constants);
					$obj->init();
					break;
			}
			
			
		}
		
		/**
		* Function name init
		*/
		function init(){
			
			if (session_status() == PHP_SESSION_NONE) {
				session_start();
			}
			
			do_action('icwcpos_init',$this->constants);
			
			require_once('icwcpos_end_point_settings.php');
			$ICWCPOS_INIT = new ICWCPOS_END_POINT_SETTINGS($this->constants);
			
			require_once('icwcpos_template.php');
			$ICWCPOS_TEMPLATE = new ICWCPOS_TEMPLATE($this->constants);
			
			
		}
		
		function icwcpos_template_start(){
			add_action('wp_footer',  array($this,    'wp_footer'));
		}
				
		function wp_footer(){
			global $wp;
			
			$assets_url = $this->constants['assets_url'];
			
			if( isset( $wp->query_vars['point-of-sale'] ) && $wp->query_vars['point-of-sale'] == 1 ){
				wp_enqueue_style('jquery_ui', "//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css");    
				wp_enqueue_style('front_style_1', $assets_url."css/admin_style.css");    
				wp_enqueue_style('front_style_2', $assets_url."css/front_style.css");    
				wp_enqueue_style('font_awesome', $assets_url."css/font-awesome.min.css");    
				wp_enqueue_style('googleapis_1', "https://fonts.googleapis.com/css?family=Open+Sans:400,600,700");
				
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-core');
				wp_enqueue_script('jquery-migrate');
				wp_enqueue_script('jquery-ui-accordion');
				wp_enqueue_script('jquery-ui-autocomplete');
				
				wp_enqueue_script('jquery_cookie', $assets_url."js/jquery.cookie.js", array('jquery'));
				wp_enqueue_script('custom_popup', $assets_url."js/popup.js");
				wp_enqueue_script('plugin_script', $assets_url."js/front_scripts.js");  
			}
		}
		
		function call_ajax_action(){
			$sub_action = isset($_REQUEST['sub_action']) ? $_REQUEST['sub_action'] : '';
			
			do_action('icwcpos_ajax_action',$this->constants, $sub_action);
			
			//error_log(print_r($_REQUEST,true));
			if($sub_action == 'place_order'){
				require_once('icwcpos_checkout.php');
				$ICWCPOS_CHECKOUT = new ICWCPOS_CHECKOUT($this->constants);
				$ICWCPOS_CHECKOUT->create_order();
			}
			
			if($sub_action == 'receipt_content'){
				require_once('icwcpos_checkout.php');
				$ICWCPOS_CHECKOUT = new ICWCPOS_CHECKOUT($this->constants);
				$ICWCPOS_CHECKOUT->get_receipt_content_by_order_key();
			}
			
			if($sub_action == 'search_user'){
				require_once('icwcpos_front_customer_search.php');
				$ICWCPOS_FRONT_CUSTOMER_SEARCH = new ICWCPOS_FRONT_CUSTOMER_SEARCH($this->constants);
				$ICWCPOS_FRONT_CUSTOMER_SEARCH->ajax();
			}
			
			if($sub_action == 'search_product'){
				require_once('icwcpos_template.php');
				$ICWCPOS_TEMPLATE = new ICWCPOS_TEMPLATE($this->constants);
				$ICWCPOS_TEMPLATE->ajax();
			}
			
			if($sub_action == 'settings'){
				require_once('icwcpos_template.php');
				$ICWCPOS_TEMPLATE = new ICWCPOS_TEMPLATE($this->constants);
				$ICWCPOS_TEMPLATE->ajax();
			}
			
			if($sub_action == 'creater_customer'){
				require_once('icwcpos_front_customer_create.php');
				$ICWCPOS_FRONT_CUSTOMER_CREATE = new ICWCPOS_FRONT_CUSTOMER_CREATE($this->constants);
				$ICWCPOS_FRONT_CUSTOMER_CREATE->ajax();
			}
			die;
			
		}
		
	}/*End Class*/
}
