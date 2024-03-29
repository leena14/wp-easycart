<?php

class ec_order{
	protected $mysqli;													// ec_db structure
	
	private $cart;														// ec_cart structure
	private $user;														// ec_user structure
	private $shipping;													// ec_shipping structure
	private $tax;														// ec_tax structure
	private $discount;													// ec_discounts structure
	private $order_totals;												// ec_order_totals structure
	
	public $payment;													// ec_payment structure
	
	public $order_customer_notes;										// BLOB
	public $process_result;												// INT
	public $order_status;												// INT
	
	public $order_id;													// INT
	
	private $store_page;												// VARCHAR
	private $cart_page;													// VARCHAR
	private $account_page;												// VARCHAR
	private $permalink_divider;											// CHAR
	
	const SUCCESS 		= 0;											// INT			
	const ORDERERROR 	= 1;											// INT
	const GATEWAYERROR 	= 2;											// INT
	
	function __construct( $cart, $user, $shipping, $tax, $discount, $order_totals, $payment ){
		$this->mysqli = new ec_db( );
		
		$this->cart = $cart;
		$this->user = $user;
		$this->shipping = $shipping;	
		$this->tax = $tax;
		$this->discount = $discount;
		$this->order_totals = $order_totals;
		$this->payment = $payment;
		
		$store_page_id = get_option('ec_option_storepage');
		$cart_page_id = get_option('ec_option_cartpage');
		$account_page_id = get_option('ec_option_accountpage');
		
		if( function_exists( 'icl_object_id' ) ){
			$store_page_id = icl_object_id( $store_page_id, 'page', true, ICL_LANGUAGE_CODE );
			$cart_page_id = icl_object_id( $cart_page_id, 'page', true, ICL_LANGUAGE_CODE );
			$account_page_id = icl_object_id( $account_page_id, 'page', true, ICL_LANGUAGE_CODE );
		}
		
		$this->store_page = get_permalink( $store_page_id );
		$this->cart_page = get_permalink( $cart_page_id );
		$this->account_page = get_permalink( $account_page_id );
		
		if( class_exists( "WordPressHTTPS" ) && isset( $_SERVER['HTTPS'] ) ){
			$https_class = new WordPressHTTPS( );
			$this->store_page = $https_class->makeUrlHttps( $this->store_page );
			$this->cart_page = $https_class->makeUrlHttps( $this->cart_page );
			$this->account_page = $https_class->makeUrlHttps( $this->account_page );
		}
		
		if( substr_count( $this->cart_page, '?' ) )					$this->permalink_divider = "&";
		else														$this->permalink_divider = "?";
	}
	
	public function submit_order( $payment_type ){
		
		// Get payment gateway used (if live) and if we support refunds with this method.
		$order_gateway = "";
		if( $payment_type == "affirm" )
			$order_gateway = "affirm";
		else if( $payment_type == "credit_card" && get_option( 'ec_option_payment_process_method' ) == "stripe" )
			$order_gateway = "stripe";
		// End order gateway section
		
		if( $payment_type == "credit_card" || $payment_type == "affirm" )
			$payment_type = $this->payment->credit_card->payment_method;
		
		$this->order_customer_notes = "";
		if( isset( $_POST['ec_order_notes'] ) )
			$this->order_customer_notes = $_POST['ec_order_notes'];
		else if( isset( $_SESSION['ec_order_notes'] ) )
			$this->order_customer_notes = $_SESSION['ec_order_notes'];
		
		$this->order_id = $this->mysqli->insert_order( $this->cart, $this->user, $this->shipping, $this->tax, $this->discount, $this->order_totals, $this->payment, $payment_type, "5", $this->order_customer_notes, $order_gateway );
		
		if($this->order_id != 0){
			
			if( $this->order_totals->grand_total <= 0 ){
				$this->mysqli->update_order_status( $this->order_id, "3" );
				$this->process_result = "1";
				$this->order_status = "3";
				
			}else if( $payment_type == "manual_bill" ){
				$this->mysqli->update_order_status( $this->order_id, "14" );
				$this->process_result = "1";
				$this->order_status = "14";
			
			}else if( $payment_type == "third_party" ){
				$this->mysqli->update_order_status( $this->order_id, "8" );
				$this->process_result = "1";
				$this->order_status = "8";
			
			}else{
				$this->process_result = $this->payment->process_payment( $this->cart, $this->user, $this->shipping, $this->tax, $this->discount, $this->order_totals, $this->order_id );
				if( $this->process_result == "1" && !$this->payment->is_3d_auth ){
					$this->mysqli->update_order_status( $this->order_id, "6" );
					$this->order_status = "6";
				}
				
			}
			
			if( $this->process_result == "1" ){
				$this->insert_details();
				$this->update_user_addresses();
				
				if( $this->shipping->shipping_method == "fraktjakt" ){
					// Insert order for shipping method
					$ship_order_info = $this->shipping->submit_fraktjakt_shipping_order( );
					$this->mysqli->update_order_fraktjakt_info( $this->order_id, $ship_order_info );
				}
				
				// Deconetwork if used
				if( ( $payment_type != "third_party" && !$this->payment->is_3d_auth ) || $payment_type == "affirm" )
					$this->process_deconetwork_complete( "true" );
				else
					$this->process_deconetwork_complete( "false" );
				
				// Run Necessary Processes Based on Payment Type
				if( !$this->payment->is_3d_auth ){
					if ($payment_type != 'third_party') {
						// Quickbooks Hook
						if( file_exists( WP_PLUGIN_DIR . "/" . EC_QB_PLUGIN_DIRECTORY . "/ec_quickbooks.php" ) ){
							$quickbooks = new ec_quickbooks( );
							$quickbooks->add_order( $this->order_id );
						}
						
						$this->send_email_receipt(); //leave it to third party to send email
					}
					
					if( $payment_type == 'third_party' && get_option( 'ec_option_payment_third_party' ) == "paymentexpress_thirdparty" ){
						// Try not clearing session for this provider.
					}else{
						$this->mysqli->clear_tempcart( $_SESSION['ec_cart_id'] );
						$this->clear_session( );
					}
					
					if( $this->discount->giftcard_code )
						$this->mysqli->update_giftcard_total( $this->discount->giftcard_code, $this->discount->giftcard_discount );
				}
			}else
				$this->mysqli->remove_order( $this->order_id );
			
			
			return $this->process_result;
			
		}else{
			error_log( "error in submit_order(), couldn't insert order." );
			return "Error Inserting Order";
		}
	}
	
	private function insert_details(){
		
		for( $i = 0; $i < count( $this->cart->cart ); $i++ ){
			$this->insert_details_helper( $this->cart->cart[$i] );
		}
	}
	
	private function insert_details_helper( &$cart_item ){
		
		if( $cart_item->is_giftcard || $cart_item->is_download ){
			$num_times = $cart_item->quantity;
			$cart_item->quantity = 1;
			$cart_item->total_price = $cart_item->unit_price;
			
			for( $i=0; $i<$num_times; $i++ ){										
														$giftcard_id = 0;
				if( $cart_item->is_giftcard) 			$giftcard_id = $this->mysqli->insert_new_giftcard( $cart_item->unit_price, $cart_item->gift_card_message );
														$cart_item->giftcard_id = $giftcard_id;
																
				if( $cart_item->is_giftcard )			$this->send_gift_card_email( $cart_item, $giftcard_id );
																
														$download_id = 0;
				if( $cart_item->is_download )			$download_id = $this->mysqli->insert_new_download( 	$this->order_id, $cart_item->download_file_name, $cart_item->product_id, $cart_item->is_amazon_download, $cart_item->amazon_key );
														$cart_item->download_id = $download_id;
				
				$orderdetail_id = $this->mysqli->insert_order_detail( $this->order_id, $giftcard_id, $download_id, $cart_item );
			}
		
		}else{
			$orderdetail_id = $this->mysqli->insert_order_detail( $this->order_id, 0, 0, $cart_item );
		}
		
		$cart_item->orderdetail_id = $orderdetail_id;
		
		if($cart_item->use_optionitem_quantity_tracking)	
			$this->mysqli->update_quantity_value( 		$cart_item->quantity, 
														$cart_item->product_id, 
														$cart_item->optionitem1_id, 
														$cart_item->optionitem2_id, 
												  		$cart_item->optionitem3_id, 
														$cart_item->optionitem4_id, 
														$cart_item->optionitem5_id 
												);
	
														$this->mysqli->update_product_stock( $cart_item->product_id, $cart_item->quantity );
	}
	
	private function update_user_addresses( ){
		$this->mysqli->update_user_address( $this->user->billing_id, $this->user->billing->first_name, $this->user->billing->last_name, $this->user->billing->address_line_1, $this->user->billing->address_line_2, $this->user->billing->city, $this->user->billing->state, $this->user->billing->zip, $this->user->billing->country, $this->user->billing->phone, $this->user->billing->company_name );
		
		$this->mysqli->update_user_address( $this->user->shipping_id, $this->user->shipping->first_name, $this->user->shipping->last_name, $this->user->shipping->address_line_1, $this->user->shipping->address_line_2, $this->user->shipping->city, $this->user->shipping->state, $this->user->shipping->zip, $this->user->shipping->country, $this->user->shipping->phone, $this->user->shipping->company_name );
	}
	
	private function send_email_receipt(){
		
		$tax_struct = new ec_tax( 0,0,0, "", "");
		$total = $GLOBALS['currency']->get_currency_display( $this->order_totals->grand_total );
		$subtotal = $GLOBALS['currency']->get_currency_display( $this->order_totals->sub_total );
		$tax = $GLOBALS['currency']->get_currency_display( $this->order_totals->tax_total );
		if( $this->order_totals->duty_total > 0 ){ $has_duty = true; }else{ $has_duty = false; }
		$duty = $GLOBALS['currency']->get_currency_display( $this->order_totals->duty_total );
		$vat = $GLOBALS['currency']->get_currency_display( $this->order_totals->vat_total );
		$vat_rate = number_format( $this->tax->vat_rate, 0, '', '' );
		$shipping = $GLOBALS['currency']->get_currency_display( $this->order_totals->shipping_total );
		$discount = $GLOBALS['currency']->get_currency_display( $this->order_totals->discount_total );
		
		$email_logo_url = get_option( 'ec_option_email_logo' ) . "' alt='" . get_bloginfo( "name" );
	 	
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=utf-8";
		$headers[] = "From: " . get_option( 'ec_option_order_from_email' );
		$headers[] = "Reply-To: " . get_option( 'ec_option_order_from_email' );
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		ob_start();
        if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_cart_email_receipt.php' ) )	
			include WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_cart_email_receipt.php';
		else
			include WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_cart_email_receipt.php';
			
        $message = ob_get_clean();
		
		if( get_option( 'ec_option_use_wp_mail' ) ){
			wp_mail( $this->user->email, $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
			wp_mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
		}else{
			mail( $this->user->email, $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
			mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
		}
		
	}
	
	public function send_gift_card_email( $cart_item, $giftcard_id ){
		
		$email_logo_url = get_option( 'ec_option_email_logo' ) . "' alt='" . get_bloginfo( "name" );
	 	
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=utf-8";
		$headers[] = "From: " . get_option( 'ec_option_order_from_email' );
		$headers[] = "Reply-To: " . get_option( 'ec_option_order_from_email' );
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		ob_start();
        if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_cart_email_giftcard.php' ) )	
			include WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_cart_email_giftcard.php';
		else
			include WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_cart_email_giftcard.php';
			
        $message = ob_get_clean();
		
		if( get_option( 'ec_option_use_wp_mail' ) ){
			wp_mail( $cart_item->gift_card_email, $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_title" ), $message, implode("\r\n", $headers) );
			wp_mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_title" ), $message, implode("\r\n", $headers) );
		}else{
			mail( $cart_item->gift_card_email, $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_title" ), $message, implode("\r\n", $headers) );
			mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_title" ), $message, implode("\r\n", $headers) );
		}
		
	}
	
	public function clear_session(){
		
		unset( $_SESSION['ec_billing_first_name'] );
		unset( $_SESSION['ec_billing_last_name'] );
		unset( $_SESSION['ec_billing_address'] );
		unset( $_SESSION['ec_billing_address2'] );
		unset( $_SESSION['ec_billing_city'] );
		unset( $_SESSION['ec_billing_state'] );
		unset( $_SESSION['ec_billing_zip'] );
		unset( $_SESSION['ec_billing_country'] );
		unset( $_SESSION['ec_billing_phone'] );
		
		unset( $_SESSION['ec_shipping_selector'] );
		unset( $_SESSION['ec_shipping_first_name'] );
		unset( $_SESSION['ec_shipping_last_name'] );
		unset( $_SESSION['ec_shipping_address'] );
		unset( $_SESSION['ec_shipping_address2'] );
		unset( $_SESSION['ec_shipping_city'] );
		unset( $_SESSION['ec_shipping_state'] );
		unset( $_SESSION['ec_shipping_zip'] );
		unset( $_SESSION['ec_shipping_country'] );
		unset( $_SESSION['ec_shipping_phone'] );
		
		unset( $_SESSION['ec_use_shipping'] );
		unset( $_SESSION['ec_shipping_method'] );
		unset( $_SESSION['ec_expedited_shipping'] );
		
		if( !isset( $_SESSION['ec_user_id'] ) ){
			unset( $_SESSION['ec_email'] );
			unset( $_SESSION['ec_first_name'] );
			unset( $_SESSION['ec_last_name'] );
		}
		
		unset( $_SESSION['ec_create_account'] );
		unset( $_SESSION['ec_couponcode'] );
		unset( $_SESSION['ec_giftcard'] );
		unset( $_SESSION['ec_order_notes'] );
		unset( $_SESSION['ec_cart_id'] );
		unset( $_COOKIE['ec_cart_id'] );
		$vals = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' );
		$_SESSION['ec_cart_id'] = $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)] . $vals[rand(0, 25)];
		setcookie( 'ec_cart_id', $_SESSION['ec_cart_id'], time( ) + ( 3600 * 24 * 30 ) );
	
	}
	
	private function process_deconetwork_complete( $is_paid ){
		
		if( get_option( 'ec_option_deconetwork_url' ) != "" ){
		
			// Get DecoNetwork IDs if available
			$deconetwork_ids = array( );
			for( $i=0; $i<count( $this->cart->cart ); $i++ ){
				if( $this->cart->cart[$i]->is_deconetwork ){
					$deconetwork_ids[] = $this->cart->cart[$i]->deconetwork_id;
				}
			}
			
			// If IDS, Process the DecoNetwork Order as Paid
			if( count( $deconetwork_ids ) > 0 ){
				
				$data = array( 	"ids" 						=> implode( ",", $deconetwork_ids ),
								"auth" 						=> get_option( 'ec_option_deconetwork_password' ),
								"mark_as_paid"				=> $is_paid,
								"invoice_id"				=> $this->order_id,
								"oid"						=> $_SESSION['ec_cart_id'],
								"billing_country_code"		=> $this->user->billing->country,
								"billing_email"				=> $this->user->email,
								"billing_firstname"			=> $this->user->billing->first_name,
								"billing_lastname"			=> $this->user->billing->last_name,
								"billing_zip"				=> $this->user->billing->zip,
								"billing_ph_number"			=> $this->user->billing->phone,
								"billing_street"			=> $this->user->billing->address_line_1 . " " . $this->user->billing->address_line_2,
								"billing_city"				=> $this->user->billing->city,
								"billing_state"				=> $this->user->billing->state,
								"separate_shipping_details"	=> "true",
								"shipping_country_code"		=> $this->user->shipping->country,
								"shipping_firstname"		=> $this->user->shipping->first_name,
								"shipping_lastname"			=> $this->user->shipping->last_name,
								"shipping_zip"				=> $this->user->shipping->zip,
								"shipping_ph_number"		=> $this->user->shipping->phone,
								"shipping_street"			=> $this->user->shipping->address_line_1 . " " . $this->user->shipping->address_line_2,
								"shipping_city"				=> $this->user->shipping->city,
								"shipping_state"			=> $this->user->shipping->state );
				
				$url = "https://" . get_option( 'ec_option_deconetwork_url' ) . "/external/commit_order?" . http_build_query( $data );
				
				// Setup and run CURL
				$ch = curl_init( );
				curl_setopt($ch, CURLOPT_URL, $url );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET" ); 
				curl_setopt($ch, CURLOPT_TIMEOUT, (int)30);
				$response = curl_exec($ch);
				curl_close ($ch);
				
				error_log( "Order " . $this->order_id . " DecoNetwork Response: " . $response );
				
			}
			
		}
	
	}
	
	public function get_shipping_method_name( ){
		return $this->mysqli->get_shipping_method_name( $_SESSION['ec_shipping_method'] );
	}
	
}

?>