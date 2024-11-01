<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_END_POINT_SETTINGS')){
	/*
	 * Class Name ICWCPOS_END_POINT_SETTINGS
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_END_POINT_SETTINGS{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
			add_action('admin_init', array($this, 'admin_init'),1000);
			add_action('admin_init', array($this, 'save'));
			
			// Admin bar menus.
			if ( apply_filters( 'woocommerce_show_admin_bar_visit_store', true ) ) {
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menus' ), 31 );
			}
		}
		
		/**
			*Hook into the permalinks setting api
		*/
		function admin_init() {
			add_settings_field('wc_permalink_point_of_sale',_x( 'Point of Sale base', 'Permalink setting, eg: /point-of-sale', 'icwcpos'),array( $this, 'input' ),'permalink','optional',array('label_for' => 'wc_permalink_point_of_sale'));
		}
		
		/**
			*Output the field
		*/
		public function input() {
			$permalink = get_option('wc_permalink_point_of_sale', '');
			if( $permalink === 'pont-of-sale'){
				$permalink = ''; // use placeholder
			}
			echo '<input name="wc_permalink_point_of_sale" id="wc_permalink_point_of_sale" type="text" class="regular-text code" value="'. esc_attr( $permalink ) .'" placeholder="pont-of-sale" />';
		}
		
		/**
			*Save the field
		*/
		public function save() {
			if(isset($_POST['wc_permalink_point_of_sale'])){
				$permalink = $_POST['wc_permalink_point_of_sale'];
				if(!empty($permalink)){
					$permalink = trim(sanitize_text_field($permalink), '/\\' );
					$permalink = esc_attr( $permalink );
					$permalink = strtolower( $permalink );
					$permalink = str_replace(' ','-',$permalink);
				}else{
					$permalink = 'pont-of-sale';
				}
				update_option('wc_permalink_point_of_sale', $permalink );
			}
		}
		
		/**
		 * Add the "Visit Point of Sale" link in admin bar main menu.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
		 */
		public function admin_bar_menus( $wp_admin_bar ) {
			if ( ! is_admin() || ! is_user_logged_in() ) {
				return;
			}
	
			// Show only when the user is a member of this site, or they're a super admin.
			if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
				return;
			}
	
			// Don't display when shop page is the same of the page on front.
			//if ( intval( get_option( 'page_on_front' ) ) === wc_get_page_id( 'shop' ) ) {
				//return;
			//}
			
			//$permalinks = get_option('permalink_structure');
			//if( empty( $permalinks ) ) {}
			
			$permalink = get_option('wc_permalink_point_of_sale', '');
			$permalink = empty($permalink) ? 'point-of-sale' : $permalink;
			
			$site_url = get_site_url();
			$href = $site_url.'/'.$permalink;
			// Add an option to visit the store.
			$wp_admin_bar->add_node(
				array(
					'parent' => 'site-name',
					'id'     => 'view-point-of-sale',
					'title'  => __( 'Visit Point of Sale', 'icwcpos'),
					'href'   => $href,
				)
			);
			
			if(isset($_SESSION['current_location_name'])){
				$pos_location_name = $_SESSION['current_location_name'];
				$wp_admin_bar->add_node(
					array(
						'parent' => 'top-secondary',
						'id'     => 'view-location-name',
						'title'  => sprintf(__( 'Point of Sale Location: %s', 'icwcpos'),$pos_location_name),
						'href'   => false,
					)
				);
			}
		}
		
	}/*End Class*/
}
