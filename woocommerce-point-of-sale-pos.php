<?php
/*
Plugin Name:  WooCommerce Point of Sale(POS)
Description: WooCommerce Point Of Sale(POS) is a easy interface for taking orders at the Point of Sale using WooCommerce store. it also syncs inventory in woocommerce which helps admin to maintain stock.
Version: 1.4
Author: Infosoft Consultants
Author URI: http://plugins.infosofttech.com
Plugin URI: https://wordpress.org/plugins/woo-point-of-salepos/

Tested Wordpress Version: 6.1.x
WC requires at least: 3.5.x
WC tested up to: 7.4.x
Requires at least: 5.7
Requires PHP: 5.6

Text Domain: icwcpos
Domain Path: /languages/
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!function_exists('print_array')){
	function print_array($list = array(), $display = true){
		
		$output = "<pre>";
		$output .= print_r($list,true);
		$output .= "</pre>";
		
		if($display){
			echo $output;
		}else{
			return $output;
		}
	}
}

if(!function_exists('clear_error_log')){
	function clear_error_log(){
		$f = ini_get('error_log');
		file_put_contents($f, "");
	}
}

if(!class_exists('IC_WC_Point_of_Sale')){
	/*
	 * Class Name IC_WooPro_Point_of_Sale_Pro
	 *
	 * Class is used for load plugin
	 *	 
	*/
	class IC_WC_Point_of_Sale{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct() {
			add_filter( 'plugin_action_links_woo-point-of-salepos/woocommerce-point-of-sale-pos.php', array( $this, 'plugin_action_links' ), 9, 2 );
			add_action('plugins_loaded', array($this, 'plugins_loaded'),102);
			add_action( 'init', array( $this, 'load_plugin_textdomain' ));
		}
		
		/**
		* Function name plugins_loaded
		*/
		function plugins_loaded(){
			$this->constants = array();
			
			if(defined('ICWCPOSPRO_PATH')){ return false;}
			
			define('ICWCPOS_PATH', dirname(__FILE__) . '/');
			
			$constants['plugin_key'] 				= 'icwcpos';
			$constants['plugin_role'] 			   = apply_filters('ic_icwcpos_plugin_role','manage_woocommerce');
			$constants['is_admin'] 				  = is_admin();
			$constants['admin_page']			  	= isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
			$constants['plugin_dir'] 			    = $this->myplugin_plugin_path();
			$constants['parent_slug']			   = $constants['plugin_key'].'_page';
			$constants['plugin_file']			   = __FILE__;
			$constants['assets_url'] 				= plugins_url("assets/",__FILE__);
			$constants['ajax_action'] 				= 'point_of_sale';
			
			$this->constants = $constants;
			
			require_once('includes/icwcpos_function.php');
			require_once('includes/icwcpos_init.php');
			$ICWCPOS_INIT = new ICWCPOS_INIT($this->constants);
			
			add_action('admin_init', array($this, 'admin_init'));
			
			//add_filter( 'gettext', array($this, 'get_text'),20,3);
		}
		
		function plugin_action_links($plugin_links, $file = ''){
			$plugin_links[] = '<a target="_blank" href="'.admin_url('admin.php?page=icwcpos_page').'">' . esc_html__( 'Dashboard', 'icwcpos' ) . '</a>';
			return $plugin_links;
		}
		
		function load_plugin_textdomain(){
			load_plugin_textdomain( 'icwcpos', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
		}
		
		function admin_init(){
			require_once('includes/icwcpos_admin_init.php');
			$ICWCPOS_ADMIN_INIT = new ICWCPOS_ADMIN_INIT($this->constants);
		}
		
		function myplugin_plugin_path() {
		  return untrailingslashit(plugin_dir_path( __FILE__ ));	 
		}
		
		function get_text($translated_text, $text, $domain){
			if($domain == 'icwcpos'){
				return '['.$translated_text.']';
			}		
			return $translated_text;
		}
		
	}/*End Class*/
	$IC_WooPro_Point_of_Sale_Pro = new IC_WC_Point_of_Sale();
}