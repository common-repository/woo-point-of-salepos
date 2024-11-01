<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_PRODUCT_IMAGE')){
	/*
	 * Class Name ICWCPOS_INIT
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_INIT{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			$this->constants = $constants;
		}
		
		function get_image( $size = 'woocommerce_thumbnail', $attr = array(), $placeholder = true ) {
			if ( has_post_thumbnail( $this->get_id() ) ) {
				$image = get_the_post_thumbnail( $this->get_id(), $size, $attr );
			} elseif ( ( $parent_id = wp_get_post_parent_id( $this->get_id() ) ) && has_post_thumbnail( $parent_id ) ) { // @phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
				$image = get_the_post_thumbnail( $parent_id, $size, $attr );
			} elseif ( $placeholder ) {
				$image = wc_placeholder_img( $size );
			} else {
				$image = '';
			}
	
			return apply_filters( 'woocommerce_product_get_image', $image, $this, $size, $attr, $placeholder, $image );
		}
		
	}/*End Class*/
}
