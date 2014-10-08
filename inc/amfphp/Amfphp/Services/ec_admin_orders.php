<?php
/*
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//All Code and Design is copyrighted by Level Four Development, LLC
//
//Level Four Development, LLC provides this code "as is" without warranty of any kind, either express or implied,     
//including but not limited to the implied warranties of merchantability and/or fitness for a particular purpose.         
//
//Only licnesed users may use this code and storfront for live purposes. All other use is prohibited and may be 
//subject to copyright violation laws. If you have any questions regarding proper use of this code, please
//contact Level Four Development, llc and EasyCart prior to use.
//
//All use of this storefront is subject to our terms of agreement found on Level Four Development, LLC's  website.
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
*/

class ec_admin_orders{		
		
	private $db;
	
	function ec_admin_orders( ){
		
		global $wpdb;
		$this->db = $wpdb;
		
	}//ec_admin_orders
	
	public function _getMethodRoles( $methodName ){
	   if( $methodName == 'updateorderaddresses' ) 					return array( 'admin' );	
	   else if( $methodName == 'getorders' ) 						return array( 'admin' );
	   else if( $methodName == 'getorderdetailsadvancedoptions' ) 	return array( 'admin' );
	   else if( $methodName == 'getorderdetails' ) 					return array( 'admin' );
	   else if( $methodName == 'getorderstatus' ) 					return array( 'admin' );
	   else if( $methodName == 'updateorderstatus' ) 				return array( 'admin' );
	   else if( $methodName == 'updateorderviewed' ) 				return array( 'admin' );
	   else if( $methodName == 'deleteorder' ) 						return array( 'admin' );
	   else if( $methodName == 'updateshippingstatus' ) 			return array( 'admin' );
	   else if( $methodName == 'updatefraktjaktshipping' ) 			return array( 'admin' );
	   else if( $methodName == 'refundorder' ) 						return array( 'admin' );
	   else if( $methodName == 'resendgiftcardemail' ) 				return array( 'admin' );
	   else  														return null;
	   
	}//_getMethodRoles


	function updateorderaddresses($orderid, $addressinfo) {
		//convert object to array
		$addressinfo = (array)$addressinfo;
		
		//get previous order notes
		$sql = "SELECT ec_order.order_notes FROM ec_order WHERE ec_order.order_id = %d";
		$results = $this->db->get_results( $this->db->prepare( $sql, $orderid ) );
		
		$old_order_notes = (string)$results[0]->order_notes;
		$new_order_notes = $old_order_notes .  PHP_EOL .  PHP_EOL . "***** Address Information Updated:  " . date("M d, Y") . " *****". PHP_EOL;
			  
		//first, edit the data
		$editedsql = "UPDATE ec_order SET ec_order.billing_first_name = %s, ec_order.billing_last_name = %s, ec_order.billing_company_name = %s, ec_order.billing_address_line_1 = %s, ec_order.billing_address_line_2 = %s, ec_order.billing_city = %s, ec_order.billing_state = %s, ec_order.billing_country = %s,  ec_order.billing_zip = %s, ec_order.billing_phone = %s, ec_order.shipping_first_name = %s, ec_order.shipping_last_name = %s, ec_order.shipping_company_name = %s, ec_order.shipping_address_line_1 = %s, ec_order.shipping_address_line_2 = %s, ec_order.shipping_city = %s, ec_order.shipping_state = %s, ec_order.shipping_country = %s,  ec_order.shipping_zip = %s, ec_order.shipping_phone = %s, ec_order.user_email = %s, ec_order.order_notes = %s  WHERE ec_order.order_id = %d";
		
		$this->db->query( $this->db->prepare( $editedsql, $addressinfo['billing_first_name'], $addressinfo['billing_last_name'], $addressinfo['billing_company_name'], $addressinfo['billing_address_line_1'], $addressinfo['billing_address_line_2'], $addressinfo['billing_city'], $addressinfo['billing_state'], $addressinfo['billing_country'], $addressinfo['billing_zip'], $addressinfo['billing_phone'], $addressinfo['shipping_first_name'], $addressinfo['shipping_last_name'], $addressinfo['shipping_company_name'], $addressinfo['shipping_address_line_1'], $addressinfo['shipping_address_line_2'], $addressinfo['shipping_city'], $addressinfo['shipping_state'], $addressinfo['shipping_country'], $addressinfo['shipping_zip'], $addressinfo['shipping_phone'], $addressinfo['user_email'], $new_order_notes, $orderid ) );
		

		//then, just get the new order information and return
		$sql = "SELECT ec_orderdetail.*, ec_order.*, ec_orderstatus.order_status, UNIX_TIMESTAMP(ec_order.order_date) AS order_date,  ec_download.date_created, ec_download.download_count FROM (((ec_order LEFT JOIN ec_orderdetail ON ec_orderdetail.order_id = ec_order.order_id) LEFT JOIN ec_download ON ec_orderdetail.download_key = ec_download.download_id) LEFT JOIN ec_orderstatus ON ec_order.orderstatus_id = ec_orderstatus.status_id) WHERE ec_order.order_id = %d ORDER BY ec_orderdetail.product_id";
		  
		$results = $this->db->get_results( $this->db->prepare( $sql, $orderid ) );
		
		if( count( $results ) > 0 ){
			 return($results);
		}else{
			return array( "error" );
		}
	}

	function getorderdetailsadvancedoptions( $orderdetails_id ){
		
		  $sql = "SELECT ec_order_option.* FROM ec_order_option WHERE ec_order_option.orderdetail_id = %s";
		  $results = $this->db->get_results( $this->db->prepare( $sql, $orderdetails_id ) );
		  
		  // If product is deconetwork, lets add the data into the system
		$order_detail_row = $this->db->get_row( $this->db->prepare( "SELECT 
															ec_orderdetail.is_deconetwork,
															ec_orderdetail.deconetwork_id,
															ec_orderdetail.deconetwork_name,
															ec_orderdetail.deconetwork_product_code,
															ec_orderdetail.deconetwork_options,
															ec_orderdetail.deconetwork_color_code,
															ec_orderdetail.product_id,
															ec_orderdetail.deconetwork_image_link
														FROM ec_orderdetail 
														WHERE ec_orderdetail.orderdetail_id = %d", $orderdetails_id ) );
		if( $order_detail_row->is_deconetwork ){
			$deconetwork1 = new stdClass( );
			$deconetwork1->orderdetail_id = $orderdetails_id;
			$deconetwork1->option_name = "DecoNetwork ID: ";
			$deconetwork1->optionitem_name = "";
			$deconetwork1->option_type = "text";
			$deconetwork1->option_value = $order_detail_row->deconetwork_id;
			$deconetwork1->option_price_change = "";
			$results[] = $deconetwork1;
			$deconetwork2 = new stdClass( );
			$deconetwork2->option_name = "DecoNetwork Name: ";
			$deconetwork2->optionitem_name = "";
			$deconetwork2->option_type = "text";
			$deconetwork2->option_value =  $order_detail_row->deconetwork_name;
			$deconetwork2->option_price_change = "";
			$results[] = $deconetwork2;
			$deconetwork3 = new stdClass( );
			$deconetwork3->option_name = "DecoNetwork Product Code: ";
			$deconetwork3->optionitem_name = "";
			$deconetwork3->option_type = "text";
			$deconetwork3->option_value = $order_detail_row->deconetwork_product_code;
			$deconetwork3->option_price_change = "";
			$results[] = $deconetwork3;
			$deconetwork4 = new stdClass( );
			$deconetwork4->option_name = "DecoNetwork Options: ";
			$deconetwork4->optionitem_name = "";
			$deconetwork4->option_type = "text";
			$deconetwork4->option_value = $order_detail_row->deconetwork_options;
			$deconetwork4->option_price_change = "";
			$results[] = $deconetwork4;
			$deconetwork5 = new stdClass( );
			$deconetwork5->option_name = "DecoNetwork Color Code: ";
			$deconetwork5->optionitem_name = "";
			$deconetwork5->option_type = "text";
			$deconetwork5->option_value = $order_detail_row->deconetwork_color_code;
			$deconetwork5->option_price_change = "";
			$results[] = $deconetwork5;
			$deconetwork6 = new stdClass( );
			$deconetwork6->option_name = "DecoNetwork Image Link: ";
			$deconetwork6->optionitem_name = "";
			$deconetwork6->option_type = "text";
			$deconetwork6->option_value = $order_detail_row->deconetwork_image_link;
			$deconetwork6->option_price_change = "";
			$results[] = $deconetwork6;
		}
		// END deconetwork check
		  
		  if( count( $results ) > 0 ){
			  return( $results );
		  }else{
			  return array( "noresults" );
		  }
		  
	}//getorderdetailsadvancedoptions
	
	function getorders($startrecord, $limit, $orderby, $ordertype, $filter) {
		  
		  $sql = "SELECT SQL_CALC_FOUND_ROWS 
		  			ec_order.billing_first_name, 
					ec_order.billing_last_name, 
					ec_order.order_viewed, 
					ec_order.grand_total, 
					UNIX_TIMESTAMP(ec_order.order_date) AS order_date, 
					ec_order.user_email, ec_order.user_id,  
					ec_order.order_id, ec_order.orderstatus_id, ec_orderstatus.order_status FROM ec_order LEFT JOIN ec_orderstatus ON ec_order.orderstatus_id = ec_orderstatus.status_id WHERE ec_order.user_id != -1 " . $filter . " ORDER BY " . $orderby . " " . $ordertype . " LIMIT " . $startrecord . ", " . $limit;
		  $results = $this->db->get_results( $sql );

		  $totalquery = $this->db->get_var( "SELECT FOUND_ROWS( )" );
		  
		  if( count( $results ) > 0 ){
			  $results[0]->totalrows = $totalquery;
			  return( $results ); 
		  }else {
			  return array( "noresults" );
		  }
		  
	}//getorders
	
	function getorderdetails( $orderid ){
		  
		  $sql = "SELECT 
		  			ec_orderdetail.*, 
					ec_order.*, 
					( ec_orderdetail.use_advanced_optionset OR ec_orderdetail.is_deconetwork ) AS use_advanced_optionset,
					ec_orderstatus.order_status, 
					ec_orderstatus.is_approved,
					ec_orderdetail.giftcard_id as product_giftcard_id, 
					UNIX_TIMESTAMP(ec_order.order_date) AS order_date, 
					ec_download.date_created, 
					ec_download.download_count 
					FROM 
					(((ec_order LEFT JOIN ec_orderdetail ON ec_orderdetail.order_id = ec_order.order_id) LEFT JOIN ec_download ON ec_orderdetail.download_key = ec_download.download_id) LEFT JOIN ec_orderstatus ON ec_order.orderstatus_id = ec_orderstatus.status_id) WHERE ec_order.order_id = %d ORDER BY ec_orderdetail.product_id";
		  
		  $results = $this->db->get_results( $this->db->prepare( $sql, $orderid ) );
		  
		  if( count( $results ) > 0 ){
			 return($results);
		  }else{
			  return array( "noresults" );
		  }
		  
	}//getorderdetail
	
	function getorderstatus( ){
		  
		  $sql = "SELECT ec_orderstatus.* FROM ec_orderstatus ORDER BY ec_orderstatus.status_id ASC";
		  $results = $this->db->get_results( $sql );

		  if( count( $results ) > 0 ){
			 return( $results );
		  }else{
			  return array( "noresults" );
		  }
		  
	}//getorderstatus
	
	function updateorderstatus( $orderid, $status ){
		
		$sql = "UPDATE ec_order SET ec_order.orderstatus_id = %s WHERE ec_order.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $status, $orderid ) );
		
		if( !mysql_error( ) ){
			return array( "success" );
		}else{
			return array( "error" );
		}
	
	}//updateorderstatus
	
	function updateordernotes( $orderid, $notes ){
		
		$sql = "UPDATE ec_order SET ec_order.order_notes = %s WHERE ec_order.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $notes, $orderid ) );
		
		if( !mysql_error( ) ){
			return array( "success" );	
		}else{
			return array( "error" );
		}
	
	}//updateordernotes
	
	function updateorderviewed( $orderid ){
		
		$sql = "UPDATE ec_order SET order_viewed = 1 WHERE ec_order.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $orderid ) );
		
		if( !mysql_error( ) ){
			return array( "success" );	
		}else{
			return array( "error" );
		}
		
	}	//updateorderviewed
	
	function deleteorder( $orderid ){
		
		$sql = "DELETE FROM ec_order WHERE ec_order.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $orderid ) );
		
		$sql = "DELETE FROM ec_orderdetail WHERE ec_orderdetail.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $orderid ) );
		
		$sql = "DELETE FROM ec_download WHERE ec_download.orderid = %d";
		$this->db->query( $this->db->prepare( $sql, $orderid ) );
		
		if( !mysql_error( ) ){
			return array( "success" );
		}else{
			return array( "error" );
		}
		
	}//deleteorder
	
	function updatefraktjaktshipping( $orderid, $fraktjaktorderid, $fraktjaktshippingid){
		
		$sql = "UPDATE ec_order SET ec_order.fraktjakt_order_id = %s, ec_order.fraktjakt_shipment_id = %s WHERE ec_order.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $fraktjaktorderid, $fraktjaktshippingid, $orderid ) );
		
		if( !mysql_error( ) ){
			return array( "success" );
		}else{
			return array( "error" );
		}
		
	}
	
	function updateshippingstatus( $orderid, $shipcarrier, $shiptrackingcode, $sendemail, $clientemail ){
		
		$sql = "UPDATE ec_order SET ec_order.shipping_carrier = %s, ec_order.tracking_number = %s WHERE ec_order.order_id = %d";
		$this->db->query( $this->db->prepare( $sql, $shipcarrier, $shiptrackingcode, $orderid ) );
		
		if( $sendemail == 1 ){
			
			$sql = "SELECT ec_order.* FROM ec_order WHERE ec_order.order_id = %d";
			$order = $this->db->get_results( $this->db->prepare( $sql, $orderid ) );
			
			$trackingnumber = $order[0]->tracking_number;
			$shipcarrier = $order[0]->shipping_carrier;
			
			$orderfromemail = get_option( 'ec_option_order_from_email' );
			
			$message = "<html><head><title>Shipping Confirmation - Order Number " . $order_id . "</title><style type='text/css'><!--.style20 {font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px; }.style22 {font-family: Arial, Helvetica, sans-serif; font-size: 12px; }--></style></head><body> <table width='539' border='0' align='center'>   <tr><td colspan='4' align='left' class='style22'></td></tr><tr><td colspan='4' align='left' class='style22'><p><br> Dear " . $order[0]->billing_first_name . " " . $order[0]->billing_last_name . ": </p><p>Your recent order  with the number <strong>" . $order[0]->order_id . "</strong> has been shipped! You should be receiving it within a short time period.<br>";
			
			if( $trackingnumber != '0' && $trackingnumber != 'Null' && $trackingnumber != 'NULL'&& $trackingnumber != 'null' && $trackingnumber != NULL && $trackingnumber != '' ){
				$message .= "<br>  You may check the status of your order by visiting your carriers website and using the following tracking number.</p><p>Package Carrier: " . $shipcarrier . "<br>Package Tracking Number: " . $trackingnumber . "</p>";
			}
			
			$message .= "<p><br></p></td></tr><tr><td colspan='4' align='left' class='style20'><table width='100%' border='0' align='center' cellpadding='0' cellspacing='0'><tr><td width='47%' bgcolor='#F3F1ED' class='style20'>Billing Address</td><td width='3%'>&nbsp;</td><td width='50%' bgcolor='#F3F1ED' class='style20'>Shipping Address</td></tr><tr>   <td><span class='style22'>     " . $order[0]->billing_first_name . " " . $order[0]->billing_last_name . "    </span></td>    <td>&nbsp;</td>   <td><span class='style22'>" . $order[0]->shipping_first_name . " " . $order[0]->shipping_last_name . "</span></td> </tr><tr><td><span class='style22'> " . $order[0]->billing_address_line_1 . "<br>" . $order[0]->billing_address_line_2 . "</span></td>   <td>&nbsp;</td><td><span class='style22'>" . $order[0]->shipping_address_line_1 . " <br>
			" . $order[0]->shipping_address_line_2 . " </span></td> </tr><tr><td><span class='style22'>" . $order[0]->billing_city . ", " . $order[0]->billing_state . " " . $order[0]->billing_zip . "</span></td>   <td>&nbsp;</td>   <td><span class='style22'>" . $order[0]->shipping_city . ", " . $order[0]->shipping_state . " " . $order[0]->shipping_zip . "</span></td> </tr><tr><td><span class='style22'>Phone: " . $order[0]->billing_phone . "</span></td><td>&nbsp;</td><td><span class='style22'>Phone: " . $order[0]->shipping_phone . "</span></td> </tr></table></td></tr><tr><td width='269' align='left'>&nbsp;</td><td width='80' align='center'>&nbsp;</td><td width='91' align='center'>&nbsp;</td><td align='center'>&nbsp;</td></tr><tr><td width='269' align='left' bgcolor='#F3F1ED' class='style20'>Product</td><td width='80' align='center' bgcolor='#F3F1ED' class='style20'>Qty</td><td width='91' align='center' bgcolor='#F3F1ED' class='style20'>Unit Price</td><td align='center' bgcolor='#F3F1ED' class='style20'>Ext Price</td></tr>";
				
			$sql = "SELECT ec_orderdetail.* FROM ec_orderdetail WHERE ec_orderdetail.order_id = %d ORDER BY ec_orderdetail.product_id";
			$orderdetails = $this->db->get_results( $this->db->prepare( $sql, $orderid ) );
			
			foreach( $orderdetails as $row ){
				$finaltotal = number_format( $row->unit_price, 2 );
				$totalitemprice = number_format( $row->total_price , 2 );
				$message .= "<tr><td width='269' class='style22'>" . $row->title . "</td><td width='80' align='center' class='style22'>" . $row->quantity . "</td><td width='91' align='center' class='style22'>" . $finaltotal . "</td><td align='center' class='style22'>" . $totalitemprice . "</td></tr>";				
			}
			
			$message .="<tr><td width='269'>&nbsp;</td><td width='80' align='center'>&nbsp;</td><td width='91' align='center'>&nbsp;</td><td>&nbsp;</td></tr><tr><td colspan='4' class='style22'><p><br>Please double check your order when you receive it and let us know immediately if there are any concerns or issues. We always value your business and hope you enjoy your product.<br><br>Thank you very much!<br><br><br></p></td></tr><tr><td colspan='4'><p class='style22'></p></td></tr></table></body></html>";
			
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=utf-8\r\n";
			$headers .= "From: " . get_option( 'ec_option_order_from_email')  . "\r\n";
			$headers .= "Reply-To: " . get_option( 'ec_option_order_from_email' )  . "\r\n";
			$headers .= "X-Mailer: PHP/".phpversion()  . "\r\n";
			
			if( get_option( 'ec_option_use_wp_mail' ) ){
				wp_mail( $order[0]->user_email, 'Order Shipped - Confirmation' , $message, $headers );
			}else{
				mail( $order[0]->user_email, 'Order Shipped - Confirmation' , $message, $headers  );
			}
		}
		
		if( !mysql_error( ) ){
			return array( "success" );
		}else{
			return array( "error" );
		}
		
	}//updateshippingstatus
	
	function refundorder( $order_id, $is_full_refund, $refund_amount, $order_gateway ){
		
		// Get order charge info
		$order = $this->db->get_row( $this->db->prepare( "SELECT affirm_charge_id, stripe_charge_id, order_notes, refund_total, grand_total FROM ec_order WHERE order_id = %d", $order_id ) );
		
		// First refund order
		$result = false;
		
		if( $order_gateway == "affirm" ){
			$gateway = new ec_affirm( );
			$result = $gateway->refund_order( $order_id, $order->affirm_charge_id, $refund_amount );
			
		}else if( $order_gateway == "stripe" ){
			$gateway = new ec_stripe( );
			$result = $gateway->refund_charge( $order->stripe_charge_id, $refund_amount );
		}
		
		if( $result ){	// If goes through successfully, update order and return true
			
			$date = date('l jS \of F Y h:i:s A');
			if( strlen( $order->order_notes ) > 0 )
				$new_order_notes = $order->order_notes . PHP_EOL .  PHP_EOL . "Refund of " . $refund_amount . " made on " . $date;
			else
				$new_order_notes = "Refund of " . $refund_amount . " made on " . $date;
			
			if( $is_full_refund || ( $refund_amount + $order->refund_total ) >= $order->grand_total )
				$orderstatus_id = 16;
			else
				$orderstatus_id = 17;
			
			$this->db->query( $this->db->prepare( "UPDATE ec_order SET ec_order.refund_total = ( ec_order.refund_total + %s ), ec_order.order_notes = %s, ec_order.orderstatus_id = %d WHERE ec_order.order_id = %d", $refund_amount, $new_order_notes, $orderstatus_id, $order_id ) );
			
			return array( "order_notes" => $new_order_notes, "orderstatus_id" => $orderstatus_id );
			
		}else{
			return false;
		}
		
	}//refund order
	
	function resendgiftcardemail( $order_id, $orderdetails_id ){
		//return 'success' or 'error'
		return 'success';
		
	}
	
}//ec_admin_orders
?>