<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('ICWCPOS_FRONT_THANK_YOU_PAGE')){
	/*
	 * Class Name ICWCPOS_FRONT_THANK_YOU_PAGE
	 *
	 * Class is used for initialize plugin
	 *	 
	*/
	class ICWCPOS_FRONT_THANK_YOU_PAGE extends ICWCPOS_FUNCTION{
		
		/* variable declaration constants*/
		public $constants 				= array();	
		
		/**
			*Constructor
		*/
		public function __construct($constants = array()) {
			//clear_error_log();
			//error_log(print_r($_POST,true));
			
			remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
		}
		
		function create_thank_you_page($order){
			//wc_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
			
			
			
			?>
            	<div class="woocommerce-order">

					<?php if ( $order ) : ?>
                
                        <?php if ( $order->has_status( 'failed' ) ) : ?>
                
                            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'icwcpos' ); ?></p>
                
                            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
                                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'icwcpos' ) ?></a>
                                <?php if ( is_user_logged_in() ) : ?>
                                    <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My account', 'icwcpos' ); ?></a>
                                <?php endif; ?>
                            </p>
                
                        <?php else : ?>
                
                            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'icwcpos' ), $order ); ?></p>
                
                            <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
                
                                <li class="woocommerce-order-overview__order order">
                                    <?php _e( 'Order number:', 'icwcpos' ); ?>
                                    <strong><?php echo $order->get_order_number(); ?></strong>
                                </li>
                
                                <li class="woocommerce-order-overview__date date">
                                    <?php _e( 'Date:', 'icwcpos' ); ?>
                                    <strong><?php echo wc_format_datetime( $order->get_date_created() ); ?></strong>
                                </li>
                
                                <?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
                                    <li class="woocommerce-order-overview__email email">
                                        <?php _e( 'Email:', 'icwcpos' ); ?>
                                        <strong><?php echo $order->get_billing_email(); ?></strong>
                                    </li>
                                <?php endif; ?>
                
                                <li class="woocommerce-order-overview__total total">
                                    <?php _e( 'Total:', 'icwcpos' ); ?>
                                    <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                                </li>
                
                                <?php if ( $order->get_payment_method_title() ) : ?>
                                    <li class="woocommerce-order-overview__payment-method method">
                                        <?php _e( 'Payment method:', 'icwcpos' ); ?>
                                        <strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
                                    </li>
                                <?php endif; ?>
                
                            </ul>
                
                        <?php endif; ?>
                
                        <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
                        <?php //do_action( 'woocommerce_thankyou', $order->get_id() ); 
							$this->get_order_items($order);
						?>
                
                    <?php else : ?>
                
                        <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'icwcpos' ), null ); ?></p>
                
                    <?php endif; ?>
                
                </div>
            <?php
		}
		
		function get_order_items($order){
			$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
			$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
			//$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
			$downloads             = $order->get_downloadable_items();
			$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();
			
			$show_customer_details = true;
			
			if ( $show_downloads ) {
				wc_get_template( 'order/order-downloads.php', array( 'downloads' => $downloads, 'show_title' => true ) );
			}
			?>
			<section class="woocommerce-order-details">
				<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>
			
				<h2 class="woocommerce-order-details__title"><?php _e( 'Order details', 'icwcpos' ); ?></h2>
			
				<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
			
					<thead>
						<tr>
							<th class="woocommerce-table__product-name product-name"><?php _e( 'Product', 'icwcpos' ); ?></th>
							<th class="woocommerce-table__product-table product-total"><?php _e( 'Total', 'icwcpos' ); ?></th>
						</tr>
					</thead>
			
					<tbody>
						<?php
						do_action( 'woocommerce_order_details_before_order_table_items', $order );
			
						foreach ( $order_items as $item_id => $item ) {
							$product = $item->get_product();
			
							$this->get_item(array(
								'order'			     => $order,
								'item_id'		     => $item_id,
								'item'			     => $item,
								'show_purchase_note' => $show_purchase_note,
								'purchase_note'	     => $product ? $product->get_purchase_note() : '',
								'product'	         => $product,
							));
						}
						
						do_action( 'woocommerce_order_details_after_order_table_items', $order );
						?>
                     
					</tbody>
			
					<tfoot>
						<?php
							foreach ( $order->get_order_item_totals() as $key => $total ) {
								?>
								<tr>
									<th scope="row"><?php echo $total['label']; ?></th>
									<td><?php echo $total['value']; ?></td>
								</tr>
								<?php
							}
						?>
						<?php if ( $order->get_customer_note() ) : ?>
							<tr>
								<th><?php _e( 'Note:', 'icwcpos' ); ?></th>
								<td><?php echo wptexturize( $order->get_customer_note() ); ?></td>
							</tr>
						<?php endif; ?>
					</tfoot>
				</table>
			
				<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
			</section>
            <?php
			
			if ( $show_customer_details ) {
				$this->get_customer_details($order);
			}
			
			?>
            	<div class="bottom_receipt_buttons">
                	<button type="button" class="ic_button close_print_window" id="close_print_window"><?php _e( 'Close', 'icwcpos' ); ?></button>
                    <button type="button" class="ic_button add_new_order" id="add_new_order"><?php _e( 'Add New Order', 'icwcpos' ); ?></button>
	                <button type="button" class="ic_button print_receipt" id="print_receipt"><?php _e( 'Print', 'icwcpos' ); ?></button>
                </div>
                <style>
                	table tfoot{display:table-row-group;}
                </style>
            <?php
		}
		
		function get_item($data = array()){
			extract($data);
			?>
            	<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">

                    <td class="woocommerce-table__product-name product-name">
                        <?php
                            $is_visible        = $product && $product->is_visible();
                            //$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
							
							$product_permalink = '';
                
                            echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible );
                            echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item->get_quantity() ) . '</strong>', $item );
                
                            do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );
                
                            wc_display_item_meta( $item );
                
                            do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
                        ?>
                    </td>
                
                    <td class="woocommerce-table__product-total product-total">
                        <?php echo $order->get_formatted_line_subtotal( $item ); ?>
                    </td>
                
                </tr>
                
                <?php if ( $show_purchase_note && $purchase_note ) : ?>
                
                <tr class="woocommerce-table__product-purchase-note product-purchase-note">
                
                    <td colspan="2"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
                
                </tr>
                
                <?php endif; ?>
            <?php
		}
		
		function get_customer_details($order){
			
            	//$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
				$show_shipping = true;
            ?>
            <section class="woocommerce-customer-details">
            
                <?php if ( $show_shipping ) : ?>
            
                <section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
                    <div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
            
                <?php endif; ?>
            
                <h2 class="woocommerce-column__title"><?php esc_html_e( 'Billing address', 'icwcpos' ); ?></h2>
            
                <address>
                    <?php echo wp_kses_post( $order->get_formatted_billing_address( __( 'N/A', 'icwcpos' ) ) ); ?>
            
                    <?php if ( $order->get_billing_phone() ) : ?>
                        <p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
                    <?php endif; ?>
            
                    <?php if ( $order->get_billing_email() ) : ?>
                        <p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
                    <?php endif; ?>
                </address>
            
                <?php if ( $show_shipping ) : ?>
            
                    </div><!-- /.col-1 -->
            
                    <div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
                        <h2 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'icwcpos' ); ?></h2>
                        <address>
                            <?php echo wp_kses_post( $order->get_formatted_shipping_address( __( 'N/A', 'icwcpos' ) ) ); ?>
                        </address>
                    </div><!-- /.col-2 -->
            
                </section><!-- /.col2-set -->
            
                <?php endif; ?>
            
                <?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>
            
            </section>
            <?php
		}
	}/*End Class*/
}
