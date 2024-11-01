<?php do_action( 'icwcpos_template_start');?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <title><?php _e('Point of Sale', 'icwcpos') ?> - <?php bloginfo('name') ?></title>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <?php do_action( 'icwcpos_template_head');?>
    <script type="text/javascript">
    	var ajax_url = "<?php echo admin_url('admin-ajax.php');?>";
    </script>
</head>
<body>
<table class="tempalte_table" cellpadding="0" cellspacing="0">
	<tr>
    	<td colspan="2" class="top-header" valign="middle">
        	<table class="header" cellpadding="0" cellspacing="0">
            	<tr>
                	<td class="top-left" valign="top">
                    	<!-- Top Navigation Menu -->
                        <div class="topnav">                          
                          <div id="pos_nav">
                            <a href="<?php echo admin_url("edit.php?post_type=shop_order")?>"><?php _e('Orders','icwcpos');?></a>
                            <a href="<?php echo admin_url("edit.php?post_type=product")?>"><?php _e('Products','icwcpos');?></a>
                            <a href="<?php echo admin_url("edit.php?post_type=shop_coupon")?>"><?php _e('Coupon','icwcpos');?></a>
                          </div>
                          <a href="javascript:void(0);" class="icon nav_button">
                            <i class="fa fa-bars"></i>
                          </a>
                        </div>
                    </td>
                    <td class="top-center">
                    	<?php
							if(!empty($location_name)){
								printf(__('Point of Sale: %s','icwcpos'),$location_name);
							}else{
								_e('Point of Sale','icwcpos');
							}
						?>                    
                    </td>                    
                    <td class="top-right">
                    	<a href="<?php echo wp_logout_url()?>"><i class="fa fa-power-off" aria-hidden="true"></i> <?php _e('Logout','icwcpos');?></a>
                    </td>
                </tr>
            </table>
			
        </td>
    </tr>
    <tr>
    	<td class="pos_left" valign="top">
        	<input type="text" value="" id="search_product" class="search_product" placeholder="<?php _e('Search products','icwcpos');?>" />
        	<div class="left_pages" style="overflow:auto; height:500px;">
            	<div class="loading_products" style="display:none;"><?php _e('Please Wait, loading products','icwcpos');?></div>
                <div class="product_list_box">
            	<?php
					$output = $this->get_product_list_content($settings);
					echo $output;
				?>
                </div>                
            </div>
        </td>        	
        <td class="pos_right" valign="top">
			<div class="right_pages right_pages_overflow">
			<?php $order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;?>            
            <?php if($order_id > 0){?>
                <div class="pages thankyou_page">
                    <?php
                        $order        = wc_get_order( $order_id );						
                        $ICWCPOS_FRONT_THANK_YOU_PAGE = new ICWCPOS_FRONT_THANK_YOU_PAGE($this->constants);
                        $ICWCPOS_FRONT_THANK_YOU_PAGE->create_thank_you_page($order);
                    ?>
                </div>
            <?php }else{?>
                <div class="pages cart_page"></div>
                <div class="pages checkout_page" style="display:none"></div>
                <div class="pages receipt_page" style="display:none"></div>
                <div class="pages customer_page" style="display:none"></div>
                <div class="pages thankyou_page" style="display:none"></div>
                <div class="left_overlay" style="display:none"></div>                
            <?php }?>
            </div>
        </td>
    </tr>
</table>
<div id="user_alert" class="ic_popup_box user_alert">
    <a class="popup_close" title="Close popup"></a>
    <h4><?php _e('Alert','icwcpos')?></h4>    
    <div class="popup_content">
        <div class="alert_msg">
            <p class="ic_popup_notice"><?php _e('Customer is not selected; However if you want to go with Guest customer, Click on "Place Order" button.','icwcpos')?></p>
        </div>
        <div style=" text-align:right;" class="buttons">
            <input type="button" value="<?php _e('PLACE ORDER','icwcpos')?>" name="btnPlaceOrder" id="btnPlaceOrder" class="ic_button ic_close_popup"   />
            <input type="button" value="<?php _e('Close','icwcpos')?>" name="btnOK" id="btnOK" class="ic_button ic_close_popup"   />
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="ic_popup_mask"></div>
<?php do_action('wp_footer'); ?>
</body>
</html>