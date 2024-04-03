<?php 
if( !class_exists( 'OrderAPI' ) ){

	class OrderAPI
	{
		public function CreateOrder($data)
		{
			if(WoocommerceAPI::Authentication())
			{
				$data = $data->get_json_params();
				
				$enabled_payment_gateways = $this->get_enabled_payment_gateways();

				
				if(!isset($data['billing']) || empty($data['billing'])){
					$error[] = new WP_Error( 'billing', __( 'billing is required.'));
				}
				else{
					if(!isset($data['billing']['first_name']) || empty($data['billing']['first_name'])){
						$error[] = new WP_Error( 'billing_first_name', __( 'billing first name is required.'));
					}
					if(!isset($data['billing']['last_name']) || empty($data['billing']['last_name'])){
						$error[] = new WP_Error( 'billing_last_name', __( 'billing last name is required.'));
					}
					if(!isset($data['billing']['email']) || empty($data['billing']['email'])){
						$error[] = new WP_Error( 'billing_email', __( 'billing email is required.'));
					}
					if(!isset($data['billing']['phone']) || empty($data['billing']['phone'])){
						$error[] = new WP_Error( 'billing_phone', __( 'billing phone is required.'));
					}
					if(!isset($data['billing']['country']) || empty($data['billing']['country'])){
						$error[] = new WP_Error( 'billing_country', __( 'billing country is required.'));
					}
					if(!isset($data['billing']['postcode']) || empty($data['billing']['postcode'])){
						$error[] = new WP_Error( 'billing_postcode', __( 'billing postcode is required.'));
					}
				}
				if(!isset($data['line_items']) || !is_array($data['line_items'])){
					$error[] = new WP_Error( 'line_items', __( 'product items is required.'));
				}
				if(isset($error)){
					return wp_send_json(
			 			["status"=>"failed","errorMessage"=>$error]
			 		,401);
				}
				
				if($data['create_customer']){
					
					$customerData = get_user_by( 'email', $data['billing']['email']);
					if ( false === $customerData){
						// Create customer.
						$customer_id = wc_create_new_customer( $data['billing']['email'], $data['billing']['email'], 'Password123');
					}
					else{
						$customer_id = $customerData->ID;
					}
					
					// // Get customers WC_Customer instance.
					$customer = new WC_Customer( $customer_id );

					// // Set our customer's meta values using WC's CRUD methods.
					$customer->set_first_name($data['billing']['first_name']);
					$customer->set_last_name($data['billing']['last_name']);
					$customer->set_billing_email( $data['billing']['email']);
					$customer->set_billing_first_name($data['billing']['first_name']);
					$customer->set_billing_last_name($data['billing']['last_name']);
					if($data['billing']['address_1']) $customer->set_billing_address_1($data['billing']['address_1']);
					if($data['billing']['address_2']) $customer->set_billing_address_2($data['billing']['address_2']);
					if($data['billing']['city']) $customer->set_billing_city($data['billing']['city']);
					$customer->set_billing_postcode($data['billing']['postcode']);
					$customer->set_billing_country($data['billing']['country']);
					$customer->set_billing_phone($data['billing']['phone']);

					// Save customer's metadata to the WC Customer instance.
					$customer->save();

					$data['customer_id'] = $customer_id;
				}
				// // if creating order for existing customer
				else if ( ! empty( $data['customer_id'] ) ) {
					// make sure customer exists
					if ( false === get_user_by( 'id', $data['customer_id'] ) ) {
						$error[] = new WP_Error( 'invalid_customer_id', __( 'invalid customer id.'));
					}
				}
				
				$order = wc_create_order();
				
				
				$order->set_address( $data['billing'], 'billing' );

				if(isset($data['shipping']) && !empty($data['shipping'])){
					$order->set_address( $data['shipping'], 'shipping' );
				}
				if (!empty( $data['customer_id'] ) ) {
					$order->set_customer_id($data['customer_id']);
				}
				$lines = array(
					'line_item' => 'line_items',
					'shipping'  => 'shipping_lines',
					'fee'       => 'fee_lines',
					'coupon'    => 'coupon_lines',
				);
				foreach($lines as $line_type=>$line){
					if ( isset( $data[ $line ] ) && is_array( $data[ $line ] ) ) {
						$line_item = "set_{$line_type}";
						foreach ( $data[ $line ] as $item ) {
							$this->$line_item($order,$item);
						}
					}
				}

				$order->set_status( 'wc-completed', 'Order is created programmatically' );

				// if($data['set_paid']){
				// 	// // order status
				// 	$order->set_status( 'wc-completed', 'Order is created programmatically' );

				// 	// // add payment method
				// 	$order->set_payment_method($data['payment_method']);
				// 	$order->set_payment_method_title($data['payment_method_title']);
				// }

				// // calculate and save
				$order->calculate_totals(false);
				$order->save();
				$data = array(
					"id" => $order->get_id(),
					"order_key"=> $order->get_order_key(),
					"status" => $order->get_status(),
					"currency"=> $order->get_order_currency(),
					"date_created"=> $order->get_date_created(),
					"date_modified"=> $order->get_date_modified(),
					"billing"=> array(
						"first_name" => $order->get_billing_first_name(),
						"last_name" => $order->get_billing_last_name(),
						"email" => $order->get_billing_email(),
						"phone" => $order->get_billing_phone(),
						"company" => $order->get_billing_company(),
						"address_1" => $order->get_billing_address_1(),
						"address_2" => $order->get_billing_address_2(),
						"city" => $order->get_billing_city(),
						"state" => $order->get_billing_state(),
						"country" => $order->get_billing_country(),
						"postcode" => $order->get_billing_postcode(),
					),
					"shipping"=> array(
						"first_name" => $order->get_shipping_first_name(),
						"last_name" => $order->get_shipping_last_name(),
						"company" => $order->get_shipping_company(),
						"address_1" => $order->get_shipping_address_1(),
						"address_2" => $order->get_shipping_address_2(),
						"city" => $order->get_shipping_city(),
						"state" => $order->get_shipping_state(),
						"country" => $order->get_shipping_country(),
						"postcode" => $order->get_shipping_postcode(),
					),
					//"payment_url" => wc_get_checkout_url().'order-pay/'.$order->get_id().'/?pay_for_order=true&key='.$order->get_order_key()
				);
				return wp_send_json(
		 			["status"=>"success","errorMessage"=>null,"order"=>$data]
		 		,200);
				
			}
		}
		private function get_enabled_payment_gateways(){

			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			$enabled_gateways = [];

			if( $gateways ) {
			    foreach( $gateways as $key=>$gateway ) {

			        if( $gateway->enabled == 'yes' ) {

			            $enabled_gateways[] = $key;

			        }
			    }
			}

			return $enabled_gateways;
		}
		/*
		* Adding product to order
		*/
		private function set_line_item($order,$item){
			if ( !isset($item['product_id']) || empty($item['product_id'])){
				$error[] = new WP_Error( 'product_id', __( 'product id is required.'));
			}
			else{
				$product_id = $item['product_id'];
			}
			
			
			if(isset($item['variation_id']) && !empty($item['variation_id']) && $item['variation_id']!=0){
				$variation_id = $item['variation_id'];
			}

			$product = wc_get_product( $variation_id ? $variation_id : $product_id );

			if ( ! is_object( $product ) ) {
				$error[] = new WP_Error( 'invalid_product', __( 'Product is invalid.'));
			}

			if(! isset( $item['quantity'] ) ) {
				$error[] = new WP_Error( 'invalid_product_quantity', __( 'Product quantity is required.'));
			}
			else if(isset($item['quantity']) && $item['quantity']<=0){
				$error[] = new WP_Error( 'invalid_product_quantity', __( 'Product quantity must be a positive value.'));
			}
			if(isset($error)){
				//wp_delete_post($order->get_id(),true);
				return wp_send_json(
		 			["status"=>"failed","errorMessage"=>$error]
		 		,401);
			}
		
			$order->add_product( $product, $item['quantity']  );
		}
		/*
		* Adding shiping 
		*/
		private function set_shipping( $order, $shipping) {

			// method ID is required
			if ( ! isset( $shipping['method_id'] ) ) {
				$error = new WP_Error( 'invalid_shipping_item', __( 'Shipping method ID is required.'));
			}
			if(isset($error)){
				return wp_send_json(
		 			["status"=>"failed","errorMessage"=>$error]
		 		,401);
			}

			$rate = new WC_Shipping_Rate( $shipping['method_id'], isset( $shipping['method_title'] ) ? $shipping['method_title'] : '', isset( $shipping['total'] ) ? floatval( $shipping['total'] ) : 0, array(), $shipping['method_id'] );
			$item = new WC_Order_Item_Shipping();
			$item->set_order_id( $order->get_id() );
			$item->set_shipping_rate( $rate );
			$order->add_item( $item );
		}
		/*
		* Adding fee to order 
		*/
		private function set_fee( $order, $fee) {
			if ( ! isset( $fee['title'] ) ) {
				$error = new WP_Error( 'invalid_fee_item', __( 'Fee title is required'));
			}
			if(isset($error)){
				return wp_send_json(
		 			["status"=>"failed","errorMessage"=>$error]
		 		,401);
			}

			$item = new WC_Order_Item_Fee();
			$item->set_order_id( $order->get_id() );
			$item->set_name( wc_clean( $fee['title'] ) );
			$item->set_total( isset( $fee['total'] ) ? floatval( $fee['total'] ) : 0 );

			// if taxable, tax class and total are required
			if ( ! empty( $fee['taxable'] ) ) {
				if ( ! isset( $fee['tax_class'] ) ) {
					$error = new WP_Error( 'invalid_fee_item', __( 'Fee tax class is required when fee is taxable.'));

					return wp_send_json(
			 			["status"=>"failed","errorMessage"=>$error]
			 		,401);
				}

				$item->set_tax_status( 'taxable' );
				$item->set_tax_class( $fee['tax_class'] );

				if ( isset( $fee['total_tax'] ) ) {
					$item->set_total_tax( isset( $fee['total_tax'] ) ? wc_format_refund_total( $fee['total_tax'] ) : 0 );
				}

				if ( isset( $fee['tax_data'] ) ) {
					$item->set_total_tax( wc_format_refund_total( array_sum( $fee['tax_data'] ) ) );
					$item->set_taxes( array_map( 'wc_format_refund_total', $fee['tax_data'] ) );
				}
			}

			$order->add_item( $item );
		}
		/**
	 	* Create an order coupon
	 	*/
		public function set_coupon( $order, $coupon) {

			// coupon amount must be positive float
			if ( isset( $coupon['amount'] ) && floatval( $coupon['amount'] ) < 0 ) {
				$error[] = new WP_Error( 'invalid_coupon_total', __( 'Coupon discount total must be a positive amount.'));
			}
			if ( empty( $coupon['code'] ) ) {
				$error[] = new WP_Error( 'invalid_coupon_total', __( 'Coupon code is required.'));
			}
			if(isset($error)){
				return wp_send_json(
		 			["status"=>"failed","errorMessage"=>$error]
		 		,401);
			}

			
			

	 		$item = new WC_Order_Item_Coupon();
			$item->set_props( array(
				'code'         => $coupon['code'],
				'discount'     => isset( $coupon['amount'] ) ? floatval( $coupon['amount'] ) : 0,
				'discount_tax' => 0,
				'order_id'     => $order->get_id(),
			) );
			$order->add_item( $item );
			
			//$order->calculate_totals( false );
			//$order->apply_coupon( wc_format_coupon_code( wp_unslash( $coupon['code'] ) ) ); 

			// Loop through products and apply the coupon discount
			// foreach($order->get_items() as $order_item){
			//     $product_id = $order_item->get_product_id();

			//     // if($this->coupon_applies_to_product($coupon, $product_id)){
			//         $total = $order_item->get_total();
			//         $order_item->set_subtotal($total);
			//         $order_item->set_total($total - $coupon['amount']);
			//         $order_item->save();
			//     //}
			// }
		}
		
	}
}