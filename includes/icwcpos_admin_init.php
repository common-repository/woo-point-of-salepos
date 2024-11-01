<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_ADMIN_INIT')){
	/*
	 * Class Name ICWCPOS_ADMIN_INIT
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_ADMIN_INIT extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {			
			$this->constants = $constants;
			//add_filter('pre_get_posts', array($this, 'pre_get_posts'));
		}
		
		function pre_get_posts($query) {
			global $pagenow;
		 
			if( 'edit.php' != $pagenow || !$query->is_admin )
				return $query;
		 
			if(current_user_can( 'edit_others_posts' ) ) {
				 global $user_ID;
				 
				 $location_id = $this->get_location_id();
				 //$location_id = 25;
				 if($location_id > 0){
					 //$query->set('author', $user_ID );
					 
					 $meta_query = $query->get('meta_query',array());
					 
					 $meta_query[] = array(							
										'key' 	 => '_pos_location_id',
										'value'   => $location_id,
										'compare' => '=',
										'type' 	=> 'numeric'
									);
				
					 $query->set('meta_query',$meta_query);
				 }
			}
			return $query;
		}
	}/*End Class*/
}
