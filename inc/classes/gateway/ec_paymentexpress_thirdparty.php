<?php
class ec_paymentexpress_thirdparty extends ec_third_party{
	
	private $payment_express_redirect_url;						// STRING
	
	public function display_form_start( ){
		echo "<form action=\"" . $this->payment_express_redirect_url . "\" method=\"post\">";
		
		
	}
	
	public function display_auto_forwarding_form( ){
		$payment_express_username = get_option( 'ec_option_payment_express_thirdparty_username' );
		$payment_express_key = get_option( 'ec_option_payment_express_thirdparty_key' );
		$payment_express_currency = get_option( 'ec_option_payment_express_thirdparty_currency' );
		
		$payment_express_xml = "<GenerateRequest>
									<PxPayUserId>" . $payment_express_username . "</PxPayUserId>
									<PxPayKey>" . $payment_express_key . "</PxPayKey>
									<AmountInput>" . number_format($this->order->grand_total, 2, '.', '' ). "</AmountInput>
									<CurrencyInput>" . $payment_express_currency . "</CurrencyInput>
									<MerchantReference>" . $this->order_id . "</MerchantReference>
									<EmailAddress>" . $this->order->user_email . "</EmailAddress>
									<TxnData1>" . htmlentities( $this->order->billing_first_name . " " . $this->order->billing_last_name ) . "</TxnData1>
									<TxnData2>" . htmlentities( $this->order->billing_phone ) . "</TxnData2>
									<TxnData3>" . htmlentities( $this->order->billing_address_line_1 . ", " . $this->order->billing_city . " " . $this->order->billing_zip ) . "</TxnData3>
									<TxnType>Purchase</TxnType>
									<TxnId>" . $this->order_id . "</TxnId>
									<BillingId></BillingId>
									<EnableAddBillCard>0</EnableAddBillCard>
									<UrlSuccess>" . htmlentities( $this->cart_page . $this->permalink_divider . "ec_action=paymentexpress&ec_page=checkout_success&order_id=" . $this->order_id ) . "</UrlSuccess>
									<UrlFail>". htmlentities( $this->cart_page . $this->permalink_divider . "ec_page=checkout_payment" ) . "</UrlFail>
								</GenerateRequest>";
		
		$response = $this->send_xml_request( $payment_express_xml );
		
		$response_body = $response["body"];
		$xml = new SimpleXMLElement($response_body);
		$this->payment_express_redirect_url = $xml->URI;
		
		echo "<form action=\"" . $this->payment_express_redirect_url . "\" method=\"post\" name=\"ec_paymentexpress_auto_form\">";
		echo "</form>";
		echo "<SCRIPT LANGUAGE=\"Javascript\">document.ec_paymentexpress_auto_form.submit();</SCRIPT>";
	}
	
	private function send_xml_request( $xml ){
		$request = new WP_Http;
		$response = $request->request( "https://sec.paymentexpress.com/pxaccess/pxpay.aspx", array( 'method' => 'POST', 'body' => $xml, 'headers' => "" ) );
		if( is_wp_error( $response ) ){
			$this->error_message = $response->get_error_message();
			return false;
		}else
			return $response;
	}
	
	public function update_order_status( ){
		$payment_express_username = get_option( 'ec_option_payment_express_thirdparty_username' );
		$payment_express_key = get_option( 'ec_option_payment_express_thirdparty_key' );
		$response_val = $_GET['result'];
		
		$xml = "<ProcessResponse>
				  <PxPayUserId>" . $payment_express_username . "</PxPayUserId>
				  <PxPayKey>" . $payment_express_key . "</PxPayKey>
				  <Response>" . $response_val . "</Response>
				</ProcessResponse>";
		$response = $this->send_xml_request( $xml );
		$response_body = $response["body"];
		$this->process_result( $response_body );
	}
	
	private function process_result( $response_body ){
		
		$xml = new SimpleXMLElement( $response_body );
			
		if( isset( $_GET['order_id'] ) ){
			
			$order_id = $_GET['order_id'];
			
			$mysqli = new ec_db( );
			$order_row = $mysqli->get_order_row( $order_id, "guest", "guest" );
			
			if( $_POST['AUTHCODE'] == 'refund' ){ 
					$mysqli->update_order_status( $order_id, "16" );
			
			}else if( $order_row->orderstatus_id != "10" ){
			
				// Insert Response
				$mysqli->insert_response( $order_id, 0, "PaymentExpress Third Party", print_r( $response_body, true ) );
				
				if( $xml->Success == '1' ){ 
					
					$this->clear_session( );
					
					// Fix for PXPay in which the script is called twice very quickly.
					$db_admin = new ec_db_admin( );
					$order_row = $db_admin->get_order_row_admin( $order_id );
					
					if( $order_row->orderstatus_id != "10" ){
						$mysqli->update_order_status( $order_id, "10" );
				
						// send email
						$order_display = new ec_orderdisplay( $order_row, true, true );
						$order_display->send_email_receipt( );
					
						// Quickbooks Hook
						if( file_exists( WP_PLUGIN_DIR . "/" . EC_QB_PLUGIN_DIRECTORY . "/ec_quickbooks.php" ) ){
							$quickbooks = new ec_quickbooks( );
							$quickbooks->add_order( $order_id );
						}
					
					}
					
				}
				
			}
		
		}
	
	}
	
	private function clear_session( ){
		unset( $_SESSION['ec_billing_first_name'] );
		unset( $_SESSION['ec_billing_last_name'] );
		unset( $_SESSION['ec_billing_address'] );
		unset( $_SESSION['ec_billing_address2'] );
		unset( $_SESSION['ec_billing_city'] );
		unset( $_SESSION['ec_billing_state'] );
		unset( $_SESSION['ec_billing_zip'] );
		unset( $_SESSION['ec_billing_country'] );
		unset( $_SESSION['ec_billing_phone'] );
		
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
		
		if( isset( $_SESSION['ec_create_account'] ) ){
			unset( $_SESSION['ec_first_name'] );
			unset( $_SESSION['ec_last_name'] );
		}
		
		unset( $_SESSION['ec_create_account'] );
		unset( $_SESSION['ec_couponcode'] );
		unset( $_SESSION['ec_giftcard'] );
		unset( $_SESSION['ec_order_notes'] );
	}
	
}
?>