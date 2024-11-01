<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_ADMIN_DASHBOARD')){
	/*
	 * Class Name ICWCPOS_ADMIN_DASHBOARD
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_ADMIN_DASHBOARD extends ICWCPOS_FUNCTION {
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			
			
		}
		
		function init(){
			?>
            <style>
            .text_align_right { text-align:right  !important;}
            </style>
            <?php
			$location_id = $this->get_location_id();
			//$location_id =0;
			$location_collection = array();
			include_once("icwcpos_admin_report.php");
			$admin_report 			= new ICWCPOS_ADMIN_REPORT();
			//$location_collection 	 = $admin_report->get_location_report_group_by_location_name(NULL,NULL,$location_id);
			//$customer_rating	 	 = $admin_report->get_customer_rating_report(NULL,NULL,$location_id);	
			//$employee_name	   	   = $admin_report->get_employee_report_group_by_employee_name(NULL,NULL,$location_id,0);
			
			//$expense_category 		= $admin_report->get_expense_group_by_category(NULL,NULL,$location_id);
			//$expense_location 		= $admin_report->get_expense_group_by_location(NULL,NULL,$location_id);
			$payment_method 	 	  = $admin_report->get_payment_method_report(NULL,NULL,$location_id);
			//$location_payment_method = $admin_report->get_payment_method_report_group_by_location_payment_method(NULL,NULL,$location_id);
			$total_collection 		= $admin_report->get_gross_total(NULL,NULL,$location_id);
			//$total_expanse 		   = $admin_report->get_expanse_total(NULL,NULL,$location_id);
			$last_30_days_sales 	  = $admin_report->get_last_30_days_sales($location_id);
			$top_products			= $admin_report->get_top_product(NULL,NULL,$location_id);
			$net_total 			   = $admin_report->get_net_total(NULL,NULL,$location_id);
			$new_customer		    = $admin_report->get_new_customer(date_i18n("Y-m-d"),date_i18n("Y-m-d"),$location_id);
			$repeat_customer		 = $admin_report->get_repeat_customer(date_i18n("Y-m-d"),date_i18n("Y-m-d"),$location_id);
			//$booking_report		  = $admin_report->get_booking_report(NULL,NULL,$location_id);
			//echo $admin_report->get_random_color();
			
			//$this->print_array($last_30_days_sales);
			$bar_chart_collection = array();
			foreach($location_collection as $key=>$value ){
				$location_collection[$key]->color =  $admin_report->get_random_color();
			}
			
			?>
            <script type="text/javascript">
            var last_30_days_sales = <?php echo json_encode(array_reverse($last_30_days_sales)); ?>;
			var location_collection = <?php echo json_encode(array_reverse($location_collection)); ?>;
			//alert(JSON.stringify(location_collection));
            </script>
            <?php
			
			
			echo "<div class=\"wrap\">";
			echo "<h1 class=\"wp-heading-inline\">". __('Dashboard','icwcpos')."</h1>";
			?>
            
            <div class="row ic_summary_box">
            <div class="col-xs-3">
                <div class="ic_block ic_block-orange">
                    <div class="ic_block-content">
                        <h2><?php _e("Gross Collection","icwcpos"); ?></h2>
                        <div class="ic_stat_content">
                            <div class="ic_block-img"><img src="<?php echo plugins_url( 'assets/images/customer-icon.png', dirname(__FILE__) );  ?>" alt=""></div>
                            <p class="ic_stat">
                                <span class="woocommerce-Price-amount amount"><?php echo $this->get_woo_price( $total_collection);  ?></span>
                                <span class="ic_count"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xs-3">
                <div class="ic_block ic_block-orange">
                    <div class="ic_block-content">
                        <h2><?php _e("Net Collection","icwcpos"); ?></h2>
                        <div class="ic_stat_content">
                            <div class="ic_block-img"><img src="<?php echo plugins_url( 'assets/images/customer-icon.png', dirname(__FILE__) );  ?>" alt=""></div>
                            <p class="ic_stat">
                                <span class="woocommerce-Price-amount amount"><?php echo $this->get_woo_price( $net_total);  ?></span>
                                <span class="ic_count"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
  
            <div class="col-xs-3">
                <div class="ic_block ic_block-grey">
                    <div class="ic_block-content">
                        <h2><?php _e("New Customer","icwcpos"); ?></h2>
                        <div class="ic_stat_content">
                            <div class="ic_block-img"><img src="<?php echo plugins_url( 'assets/images/customer-icon.png', dirname(__FILE__) );  ?>" alt=""></div>
                            <p class="ic_stat">
                                 <span class="woocommerce-Price-amount amount"><?php echo  $new_customer;  ?> </span>
                                <span class="ic_count"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xs-3">
                <div class="ic_block ic_block-grey">
                    <div class="ic_block-content">
                        <h2><?php _e("Repeat Customer","icwcpos"); ?></h2>
                        <div class="ic_stat_content">
                            <div class="ic_block-img"><img src="<?php echo plugins_url( 'assets/images/customer-icon.png', dirname(__FILE__) );  ?>" alt=""></div>
                            <p class="ic_stat">
                                 <span class="woocommerce-Price-amount amount"><?php echo $repeat_customer  ?> </span>
                                <span class="ic_count"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        	
            <div class="row">
                
                <div class="col-md-12">
                    <div class="icpostbox">
                        <h3><?php _e("Last 30 days collection","icwcpos"); ?></h3>
                        <div class="table-responsive">
                           <div id="_last_30_days_sales" style="width:99%;height:400px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="icpostbox">
                        <h3><?php _e("Payment Method","icwcpos"); ?></h3>
                        <div class="table-responsive">
                            <table style="width:100%" class="widefat table-striped">
                                <thead>
                                	<tr>
                                        <th><?php _e("Payment Method Title","icwcpos"); ?></th>
                                        <th class="text_align_right"><?php _e("Order Count","icwcpos"); ?></th>
                                        <th class="text_align_right"><?php _e("Order Total","icwcpos"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php foreach($payment_method as $key=>$value): ?>
                                    	<tr>
                                        	<td><?php echo $value->payment_method_title; ?></td>
                                            <td class="text_align_right"><?php echo $value->order_count; ?></td>
                                            <td class="text_align_right"><?php echo $this->get_woo_price($value->order_total); ?></td>
                                        </tr>
									<?php endforeach;?>
                                </tbody>
                            </table>			
                        </div>
                    </div>
                </div>
               
                <div class="col-md-6">
                    <div class="icpostbox">
                        <h3><?php _e("Top product","icwcpos"); ?></h3>
                        <div class="table-responsive">
                            <table style="width:100%" class="widefat table-striped">
                                <thead>
                                    <tr>
                                        <th><?php _e("Product Name","icwcpos"); ?></th>
                                        <th  class="text_align_right"><?php _e("Line Total","icwcpos"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($top_products as $key=>$value) :?>
                                <tr>
                                	<td><?php echo $value->product_name; ?></td>
                                    <td  class="text_align_right"><?php echo $this->get_woo_price($value->line_total); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>			
                        </div>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="icpostbox">
                        <h3>Table Heading</h3>
                        <div class="table-responsive">
                            <table style="width:100%" class="widefat table-striped">
                                <thead>
                                    <tr>
                                        <th>Heading 01</th>
                                        <th>Heading 02</th>
                                        <th>Heading 03</th>
                                        <th>Heading 04</th>
                                        <th>Heading 05</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                </tbody>
                            </table>			
                        </div>
                    </div>
                </div>
                <div class="col-md-6" style="display:none">
                    <div class="icpostbox">
                        <h3>Table Heading</h3>
                        <div class="table-responsive">
                            <table style="width:100%" class="widefat table-striped">
                                <thead>
                                    <tr>
                                        <th>Heading 01</th>
                                        <th>Heading 02</th>
                                        <th>Heading 03</th>
                                        <th>Heading 04</th>
                                        <th>Heading 05</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                    <tr>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                        <td>text</td>
                                    </tr>
                                </tbody>
                            </table>			
                        </div>
                    </div>
                </div>
            </div>
            
			<?php
			echo "</div>";
			
		}
		function get_star_icon($number= 0){
			$star = "";
			for($i=0; $i<$number; $i++){
				$star .= '<i class="fa fa-star" style="color:#fd7e14;"></i>';
			}
			return 	$star;
			
		}
	}/*End Class*/
}
