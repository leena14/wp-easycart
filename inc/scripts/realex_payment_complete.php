<?php
ini_set( 'log_errors', true );
ini_set( 'error_log', dirname( __FILE__ ).'/realex_log.txt' );

if( isset( $_POST['ORDER_ID'] ) ){

	//Load Wordpress Connection Data
	define( 'WP_USE_THEMES', false );
	require( '../../../../../wp-load.php' );
	
	$mysqli = new ec_db( );
	
	$response_string = print_r( $_POST, true );
	
	$realex_merchant_id = get_option( 'ec_option_realex_thirdparty_merchant_id' );
	$realex_secret = get_option( 'ec_option_realex_thirdparty_secret' );
	$realex_currency = get_option( 'ec_option_realex_thirdparty_currency' );
	
	$timestamp = $_POST['TIMESTAMP'];
	$result = $_POST['RESULT'];
	$order_id = $_POST['ORDER_ID'];
	$message = $_POST['MESSAGE'];
	$authcode = $_POST['AUTHCODE'];
	$pasref = $_POST['PASREF'];
	$realexmd5 = $_POST['MD5HASH'];
	
	$tmp = "$timestamp.$realex_merchant_id.$order_id.$result.$message.$pasref.$authcode";
	
	$md5hash = md5($tmp);
	$tmp_md5 = "$md5hash.$realex_secret";
	$md5hash = md5($tmp_md5);
	
	$sha1hash = sha1($tmp);
	$tmp_sha1 = "$sha1hash.$realex_secret";
	$sha1hash = sha1($tmp_sha1);
	
	// Verify this came from Realex
	if( $md5hash == $_POST['MD5HASH'] && $sha1hash == $_POST['SHA1HASH'] ){
	
		$mysqli->insert_response( $order_id, 0, "Realex Third Party", $response_string );
		
		if( $_POST['RESULT'] == '00' ){ 
			
			$mysqli->update_order_status( $order_id, "10" );
			
			// send email
			$db_admin = new ec_db_admin( );
			$order_row = $db_admin->get_order_row_admin( $order_id );
			$order_display = new ec_orderdisplay( $order_row, true, true );
			$order_display->send_email_receipt( );
		
			// Quickbooks Hook
			if( file_exists( WP_PLUGIN_DIR . "/" . EC_QB_PLUGIN_DIRECTORY . "/ec_quickbooks.php" ) ){
				$quickbooks = new ec_quickbooks( );
				$quickbooks->add_order( $order_id );
			}
			
		} else if( $_POST['AUTHCODE'] == 'refund' ){ 
			$mysqli->update_order_status( $order_id, "16" );
		} else {
			$mysqli->update_order_status( $order_id, "8" );
		}
	}
}
?>