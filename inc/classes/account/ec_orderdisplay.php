<?php

class ec_orderdisplay{
	
	protected $mysqli;							// ec_db structure
	
	public $order_id; 							// INT
	public $order_date; 						// TIMESTAMP
	public $orderstatus_id;						// INT
	public $order_status; 						// VARCHAR 50
	public $order_weight; 						// FLOAT 9,2
	public $is_approved;						// BOOL
	
	public $sub_total;							// FLOAT 15,3
	public $shipping_total; 					// FLOAT 15,3
	public $tax_total; 							// FLOAT 15,3
	public $duty_total; 						// FLOAT 15,3
	public $vat_total; 							// FLOAT 15,3
	public $discount_total;						// FLOAT 15,3
	public $grand_total;  						// FLOAT 15,3
	public $refund_total;						// FLOAT 15,3
	
	public $promo_code;  						// VARCHAR 255
	public $giftcard_id;  						// VARCHAR 20
		
	public $use_expedited_shipping; 			// BOOL
	public $shipping_method;  					// VARCHAR 255
	public $shipping_carrier;  					// VARCHAR 64
	public $tracking_number;  					// VARCHAR 100
	
	public $user_email;  						// VARCHAR 255
	public $user_level;  						// VARCHAR 255
	
	public $billing_first_name;  				// VARCHAR 255
	public $billing_last_name;  				// VARCHAR 255
	public $billing_company_name;  				// VARCHAR 255
	public $billing_address_line_1; 			// VARCHAR 255 
	public $billing_address_line_2;  			// VARCHAR 255
	public $billing_city;  						// VARCHAR 255
	public $billing_state;  					// VARCHAR 255
	public $billing_zip;  						// VARCHAR 32
	public $billing_country; 					// VARCHAR 255 
	public $billing_country_name; 				// VARCHAR 255 
	public $billing_phone;  					// VARCHAR 32
	
	public $shipping_first_name;  				// VARCHAR 255
	public $shipping_last_name;  				// VARCHAR 255
	public $shipping_company_name;  			// VARCHAR 255
	public $shipping_address_line_1;  			// VARCHAR 255
	public $shipping_address_line_2;  			// VARCHAR 255
	public $shipping_city;  					// VARCHAR 255
	public $shipping_state;  					// VARCHAR 255
	public $shipping_zip;  						// VARCHAR 32
	public $shipping_country;  					// VARCHAR 255
	public $shipping_country_name;  			// VARCHAR 255
	public $shipping_phone;  					// VARCHAR 32
	
	public $order_customer_notes;				// BLOB
	public $creditcard_digits;					// VARCHAR 4
				
	public $fraktjakt_order_id;					// VARCHAR
	public $fraktjakt_shipment_id;				// VARCHAR
	public $subscription_id;					// VARCHAR
	
	public $user;								// ec_user class
	
	public $payment_method; 					// VARCHAR 64
	
	public $paypal_email_id; 					// VARCHAR 255
	public $paypal_payer_id;					// VARCHAR 255
	
	public $orderdetails = array();				// array of ec_orderdetail items
	public $cart;
	
	private $account_page;						// VARCHAR
	private $permalink_divider;					// CHAR
	
	private $currency;							// ec_currency structure
	
	private $membership_page;					// VARCHAR 512
	
	function __construct( $order_row, $is_order_details = false, $is_admin = false ){
		$this->mysqli = new ec_db( );
		
		$this->order_id = $order_row->order_id; 
		$this->order_date = $order_row->order_date; 
		$this->orderstatus_id = $order_row->orderstatus_id;
		$this->order_status = $order_row->order_status; 
		$this->order_weight = $order_row->order_weight; 
		$this->is_approved = $order_row->is_approved;
		
		$this->sub_total = $order_row->sub_total;
		$this->shipping_total = $order_row->shipping_total;
		$this->tax_total = $order_row->tax_total;
		$this->discount_total = $order_row->discount_total;
		$this->duty_total = $order_row->duty_total;
		$this->vat_total = $order_row->vat_total;
		$this->grand_total = $order_row->grand_total; 
		$this->refund_total = $order_row->refund_total; 
		
		$this->promo_code = $order_row->promo_code; 
		$this->giftcard_id = $order_row->giftcard_id; 
		
		$this->use_expedited_shipping = $order_row->use_expedited_shipping;
		$this->shipping_method = $GLOBALS['language']->convert_text( $order_row->shipping_method );
		$this->shipping_carrier = $order_row->shipping_carrier; 
		$this->tracking_number = $order_row->tracking_number; 
		
		$this->user_email = $order_row->user_email; 
		$this->user_level = $order_row->user_level; 
		
		$this->billing_first_name = $order_row->billing_first_name; 
		$this->billing_last_name = $order_row->billing_last_name; 
		$this->billing_company_name = $order_row->billing_company_name; 
		$this->billing_address_line_1 = $order_row->billing_address_line_1; 
		$this->billing_address_line_2 = $order_row->billing_address_line_2; 
		$this->billing_city = $order_row->billing_city; 
		$this->billing_state = $order_row->billing_state; 
		$this->billing_zip = $order_row->billing_zip; 
		$this->billing_country = $order_row->billing_country; 
		$this->billing_country_name = $order_row->billing_country_name; 
		$this->billing_phone = $order_row->billing_phone; 
		
		$this->shipping_first_name = $order_row->shipping_first_name; 
		$this->shipping_last_name = $order_row->shipping_last_name; 
		$this->shipping_company_name = $order_row->shipping_company_name;
		$this->shipping_address_line_1 = $order_row->shipping_address_line_1; 
		$this->shipping_address_line_2 = $order_row->shipping_address_line_2; 
		$this->shipping_city = $order_row->shipping_city; 
		$this->shipping_state = $order_row->shipping_state; 
		$this->shipping_zip = $order_row->shipping_zip; 
		$this->shipping_country = $order_row->shipping_country; 
		$this->shipping_country_name = $order_row->shipping_country_name; 
		$this->shipping_phone = $order_row->shipping_phone; 
		
		$this->order_customer_notes = $order_row->order_customer_notes;
		$this->creditcard_digits = $order_row->creditcard_digits;
				
		$this->fraktjakt_order_id = $order_row->fraktjakt_order_id;
		$this->fraktjakt_shipment_id = $order_row->fraktjakt_shipment_id;
		$this->subscription_id = $order_row->subscription_id;
		
		$this->user = new ec_user( $this->user_email );
		$this->user->setup_billing_info_data( $this->billing_first_name, $this->billing_last_name, $this->billing_address_line_1, $this->billing_address_line_2, $this->billing_city, $this->billing_state, $this->billing_country, $this->billing_zip, $this->billing_phone, $this->billing_company_name );
		$this->user->setup_shipping_info_data( $this->shipping_first_name, $this->shipping_last_name, $this->shipping_address_line_1, $this->shipping_address_line_2, $this->shipping_city, $this->shipping_state, $this->shipping_country, $this->shipping_zip, $this->shipping_phone, $this->shipping_company_name );
		
		$this->payment_method = $order_row->payment_method; 
		
		$this->paypal_email_id = $order_row->paypal_email_id; 
		$this->paypal_payer_id = $order_row->paypal_payer_id;
		
		if( $this->subscription_id != 0 ){
			$this->membership_page = $this->mysqli->get_membership_link( $this->subscription_id );
		}else{
			$this->membership_page = "";
		}
		
		if( $is_order_details ){
			$this->cart =(object) array('cart' => array( ) );
			if( $is_admin ){
				$db_admin = new ec_db_admin( );
				$result = $db_admin->get_order_details_admin( $this->order_id );
			}else if( isset( $_SESSION['ec_is_guest'] ) && $_SESSION['ec_is_guest'] )
				$result = $this->mysqli->get_guest_order_details( $this->order_id, $_SESSION['ec_guest_key'] );
			else if( isset( $_GET['ec_guest_key'] ) )
				$result = $this->mysqli->get_guest_order_details( $this->order_id, $_GET['ec_guest_key'] );
			else
				$result = $this->mysqli->get_order_details( $this->order_id, $_SESSION['ec_email'], $_SESSION['ec_password'] );
				
			foreach( $result as $item ){
				array_push( $this->cart->cart, (object) array( "orderdetail_id"=>$item->orderdetail_id, "unit_price"=>$item->unit_price, "total_price"=>$item->total_price, "title"=>$item->title, "quantity"=>$item->quantity, "image1"=>$item->image1, "optionitem1_name"=>$item->optionitem_name_1, "optionitem2_name"=>$item->optionitem_name_2, "optionitem3_name"=>$item->optionitem_name_3, "optionitem4_name"=>$item->optionitem_name_4, "optionitem5_name"=>$item->optionitem_name_5, "optionitem1_label"=>$item->optionitem_label_1, "optionitem2_label"=>$item->optionitem_label_2, "optionitem3_label"=>$item->optionitem_label_3, "optionitem4_label"=>$item->optionitem_label_4, "optionitem5_label"=>$item->optionitem_label_5, "optionitem1_price"=>$item->optionitem_price_1, "optionitem2_price"=>$item->optionitem_price_2, "optionitem3_price"=>$item->optionitem_price_3, "optionitem4_price"=>$item->optionitem_price_4, "optionitem5_price"=>$item->optionitem_price_5, "use_advanced_optionset"=>$item->use_advanced_optionset, "is_download"=>$item->is_download, "model_number"=>$item->model_number, "giftcard_id"=>$item->giftcard_id, "gift_card_message"=>$item->gift_card_message, "gift_card_from_name"=>$item->gift_card_from_name, "gift_card_to_name"=>$item->gift_card_to_name, "is_giftcard"=>$item->is_giftcard ) );
				array_push( $this->orderdetails, new ec_orderdetail( $item ) );
				
			}
		}
		
		$accountpageid = get_option('ec_option_accountpage');
		
		if( function_exists( 'icl_object_id' ) ){
			$accountpageid = icl_object_id( $accountpageid, 'page', true, ICL_LANGUAGE_CODE );
		}
		
		$this->account_page = get_permalink( $accountpageid );
		
		if( class_exists( "WordPressHTTPS" ) && isset( $_SERVER['HTTPS'] ) ){
			$https_class = new WordPressHTTPS( );
			$this->account_page = $https_class->makeUrlHttps( $this->account_page );
		}
		
		if( substr_count( $this->account_page, '?' ) )				$this->permalink_divider = "&";
		else														$this->permalink_divider = "?";
		
		$this->currency = new ec_currency( );
	}
	
	public function display_order_detail_product_list( ){
		
		for( $i=0; $i < count( $this->orderdetails ); $i++ ){
			$order_item = $this->orderdetails[$i];
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_order_details_item_display.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_order_details_item_display.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_order_details_item_display.php' );	
		}
	}
	
	public function display_sub_total( ){
		echo $this->currency->get_currency_display( $this->sub_total );
	}
	
	public function display_shipping_total( ){
		echo $this->currency->get_currency_display( $this->shipping_total );
	}
	
	public function display_tax_total( ){
		echo $this->currency->get_currency_display( $this->tax_total );
	}
	
	public function has_duty( ){
		if( $this->duty_total != "0" )
			return true;
		else
			return false;	
	}
	
	public function display_duty_total( ){
		echo $this->currency->get_currency_display( $this->duty_total );
	}
	
	public function has_vat( ){
		if( $this->vat_total != 0 )
			return true;
		else
			return false;	
	}
	
	public function display_vat_total( ){
		echo $this->currency->get_currency_display( $this->vat_total );
	}
	
	public function has_refund( ){
		if( $this->refund_total != 0 )
			return true;
		else
			return false;	
	}
	
	public function display_refund_total( ){
		echo $this->currency->get_currency_display( $this->refund_total );
	}
	
	public function display_discount_total( ){
		echo $this->currency->get_currency_display( $this->discount_total );
	}
	
	public function display_grand_total( ){
		echo $this->currency->get_currency_display( $this->grand_total );
	}
	
	public function display_order_date( $date_format ){
		echo date( $date_format, strtotime( $this->order_date ) );	
	}
	
	public function display_order_id( ){
		echo $this->order_id; 
	}
	
	public function display_order_status( ){
		echo ucwords( strtolower( $this->order_status ) );	
	}
	
	public function display_order_shipping_method( ){
		echo $this->shipping_method;
		
		if( $this->fraktjakt_shipment_id ){
			$fraktjakt = new ec_fraktjakt( );
			$status = $fraktjakt->get_shipping_status( $this->fraktjakt_shipment_id );
			
			if( $status != "" )
				echo "<br><b>Leveransstatus:</b> " . $status;
		}
	}
	
	public function display_order_promocode( ){
		echo $this->promo_code;
	}
	
	public function display_order_giftcard( ){
		echo $this->giftcard_id;	
	}
	
	public function has_tracking_number( ){
		if( $this->tracking_number )
			return true;
		else
			return false;
	}
	
	public function display_order_tracking_number( ){
		echo $this->tracking_number;
	}
	
	public function display_order_billing_first_name( ){
		echo $this->billing_first_name;
	}
	
	public function display_order_billing_last_name( ){
		echo $this->billing_last_name;
	}
	
	public function display_order_billing_address_line_1( ){
		echo $this->billing_address_line_1;
	}
	
	public function display_order_billing_city( ){
		echo $this->billing_city;
	}
	
	public function display_order_billing_state( ){
		echo $this->billing_state;
	}
	
	public function display_order_billing_zip( ){
		echo $this->billing_zip;
	}
	
	public function display_order_billing_country( ){
		echo $this->billing_country_name;
	}
	
	public function display_order_billing_phone( ){
		echo $this->billing_phone;
	}
	
	public function display_order_shipping_first_name( ){
		echo $this->shipping_first_name;
	}
	
	public function display_order_shipping_last_name( ){
		echo $this->shipping_last_name;
	}
	
	public function display_order_shipping_address_line_1( ){
		echo $this->shipping_address_line_1;
	}
	
	public function display_order_shipping_city( ){
		echo $this->shipping_city;
	}
	
	public function display_order_shipping_state( ){
		echo $this->shipping_state;
	}
	
	public function display_order_shipping_zip( ){
		echo $this->shipping_zip;
	}
	
	public function display_order_shipping_country( ){
		echo $this->shipping_country_name;
	}
	
	public function display_order_shipping_phone( ){
		echo $this->shipping_phone;
	}
	
	public function display_payment_method( ){
		echo ucwords( $this->payment_method );
	}
	
	public function display_order_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=order_details&amp;order_id=". $this->order_id ."\">" . $link_text . "</a>";
	}
	
	public function send_email_receipt(){
		
		$tax_struct = new ec_tax( 0,0,0, "", "");
		$total = $GLOBALS['currency']->get_currency_display( $this->grand_total );
		$subtotal = $GLOBALS['currency']->get_currency_display( $this->sub_total );
		$tax = $GLOBALS['currency']->get_currency_display( $this->tax_total );
		if( $this->duty_total > 0 ){ $has_duty = true; }else{ $has_duty = false; }
		$duty = $GLOBALS['currency']->get_currency_display( $this->duty_total );
		$vat = $GLOBALS['currency']->get_currency_display( $this->vat_total );
		$shipping = $GLOBALS['currency']->get_currency_display( $this->shipping_total );
		if( ( $this->grand_total - $this->vat_total ) > 0 )
			$vat_rate = number_format( ( $this->vat_total / ( $this->grand_total - $this->vat_total ) ) * 100, 0, '', '' );
		else
			$vat_rate = number_format( 0, 0, '', '' );
			
		$discount = $GLOBALS['currency']->get_currency_display( $this->discount_total );
		
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
			wp_mail( $this->user_email, $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
			wp_mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
		}else{
			mail( $this->user_email, $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
			mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $this->order_id, $message, implode("\r\n", $headers) );
		}
		
	}
	
	public function send_failed_payment( ){
		
		$subscription = $this->mysqli->get_subscription_row( $this->subscription_id );
		$email_logo_url = get_option( 'ec_option_email_logo' ) . "' alt='" . get_bloginfo( "name" );
	 	
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=utf-8";
		$headers[] = "From: " . get_option( 'ec_option_order_from_email' );
		$headers[] = "Reply-To: " . get_option( 'ec_option_order_from_email' );
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		ob_start();
        if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_cart_payment_failed.php' ) )	
			include WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_cart_payment_failed.php';	
		else
			include WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_cart_payment_failed.php';
        $message = ob_get_clean();
		
		
		if( get_option( 'ec_option_use_wp_mail' ) ){
			wp_mail( $this->user_email, $GLOBALS['language']->get_text( "ec_errors", "subscription_payment_failed_title" ), $message, implode("\r\n", $headers) );
			wp_mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "ec_errors", "subscription_payment_failed_title" ), $message, implode("\r\n", $headers) );
		}else{
			mail( $this->user_email, $GLOBALS['language']->get_text( "ec_errors", "subscription_payment_failed_title" ), $message, implode("\r\n", $headers) );
			mail( get_option( 'ec_option_bcc_email_addresses' ), $GLOBALS['language']->get_text( "ec_errors", "subscription_payment_failed_title" ), $message, implode("\r\n", $headers) );
		}
	}
	
	public function display_subscription_link( $text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&amp;subscription_id=". $this->subscription_id ."\">" . $text . "</a>";
	}
	
	public function has_membership_page( ){
		if( $this->membership_page != "" )
			return true;
		else
			return false;
	}
	
	public function get_membership_page_link( ){
		return $this->membership_page;
	}
	
}