<?php

class ec_accountpage{
	protected $mysqli;							// ec_db structure
	
	public $user;								// ec_user structure
	public $orders;								// ec_orderlist structure
	public $order;								// ec_orderitem structure
	public $subscriptions;						// ec_subscription_list structure
	public $subscription;						// ec_subscription_item structure
	
	private $user_email;						// VARCHAR
	private $user_password;						// VARCHAR
	
	private $account_page;						// VARCHAR
	private $cart_page;							// VARCHAR
	private $permalink_divider;					// CHAR
	
	////////////////////////////////////////////////////////
	// CONSTUCTOR FUNCTION
	////////////////////////////////////////////////////////
	function __construct( ){
		$this->mysqli = new ec_db();
		
		$this->user_email = "";
		if( isset( $_SESSION['ec_email'] ) )
			$this->user_email = $_SESSION['ec_email'];
		
		$this->user_password = "";
		if( isset( $_SESSION['ec_password'] ) )
			$this->user_password = $_SESSION['ec_password'];
		
		$this->user = new ec_user( $this->user_email );
		
		$this->orders = new ec_orderlist( $this->user->user_id, $this->user_email, $this->user_password );
		$this->subscriptions = new ec_subscription_list( $this->user );
		
		if( isset( $_GET['order_id'] ) ){
			if( isset( $_SESSION['ec_is_guest'] ) && $_SESSION['ec_is_guest'] )
				$order_row = $this->mysqli->get_guest_order_row( $_GET['order_id'], $_SESSION['ec_guest_key'] );
			
			else if( isset( $_GET['ec_guest_key'] ) && $_GET['ec_guest_key'] ){
				$_SESSION['ec_is_guest'] = true;
				$_SESSION['ec_guest_key'] = $_GET['ec_guest_key'];
				$order_row = $this->mysqli->get_guest_order_row( $_GET['order_id'], $_GET['ec_guest_key'] );
			
			}else
				$order_row = $this->mysqli->get_order_row( $_GET['order_id'], $this->user_email, $this->user_password );
			
			$this->order = new ec_orderdisplay( $order_row, true );
		}
		
		if( isset( $_GET['subscription_id'] ) ){
			$subscription_row = $this->mysqli->get_subscription_row( $_GET['subscription_id'] );
			$this->subscription = new ec_subscription( $subscription_row, true );
		}
		
		$accountpageid = get_option('ec_option_accountpage');
		$cartpageid = get_option('ec_option_cartpage');
		
		if( function_exists( 'icl_object_id' ) ){
			$accountpageid = icl_object_id( $accountpageid, 'page', true, ICL_LANGUAGE_CODE );
			$cartpageid = icl_object_id( $cartpageid, 'page', true, ICL_LANGUAGE_CODE );
		}
		
		$this->account_page = get_permalink( $accountpageid );
		$this->cart_page = get_permalink( $cartpageid );
		
		if( class_exists( "WordPressHTTPS" ) && isset( $_SERVER['HTTPS'] ) ){
			$https_class = new WordPressHTTPS( );
			$this->account_page = $https_class->makeUrlHttps( $this->account_page );
			$this->cart_page = $https_class->makeUrlHttps( $this->cart_page );
		}
		
		if( substr_count( $this->account_page, '?' ) )				$this->permalink_divider = "&";
		else														$this->permalink_divider = "?";
	}
	
	public function display_account_page( ){
		echo "<div class=\"ec_account_page\">";
		if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_page.php' ) )	
			include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_page.php' );
		else	
			include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_page.php' );
		echo "<input type=\"hidden\" name=\"ec_account_base_path\" id=\"ec_account_base_path\" value=\"" . plugins_url( ) . "\" />";
		echo "<input type=\"hidden\" name=\"ec_account_session_id\" id=\"ec_account_session_id\" value=\"" . session_id() . "\" />";
		echo "<input type=\"hidden\" name=\"ec_account_email\" id=\"ec_account_email\" value=\"" . $this->user_email . "\" />";
		
		$page_name = "";
		if( isset( $_GET['ec_page'] ) )
			$page_name = $_GET['ec_page'];
		
		echo "<input type=\"hidden\" name=\"ec_account_start_page\" id=\"ec_account_start_page\" value=\"" . $page_name . "\" />";
		echo "</div>";
	}
	
	public function display_account_error(){
		if( isset( $_GET['account_error'] ) ){
			$error_text = $GLOBALS['language']->get_text( "ec_errors", $_GET['account_error'] );
			if( $error_text )
				echo "<div class=\"ec_account_error\"><div>" . $error_text . "</div></div>";
		}
	}
	
	public function display_account_success(){
		if( isset( $_GET['account_success'] ) ){
			$success_text = $GLOBALS['language']->get_text( "ec_success", $_GET['account_success'] );
			if( $success_text )
				echo "<div class=\"ec_account_success\"><div>" . $success_text . "</div></div>";
		}
	}
	
	public function is_page_visible( $page_name ){
		if( isset( $_GET['ec_page'] ) ){ //Check for a ec_page variable, act differently if set.
			if( isset( $_SESSION['ec_password'] ) && $_SESSION['ec_password'] != "guest" ){ //If logged in we can show any page accept login
				if ( $page_name == 'login' )															return false;
				else if( $page_name == $_GET['ec_page'] )												return true;
				else if( $_GET['ec_page'] == 'login' && $page_name == 'dashboard')						return true;
				else																					return false;
			}else if( isset( $_SESSION['ec_is_guest'] ) && $_SESSION['ec_is_guest'] ){ // checked out guests can see order details
				if( $page_name == 'forgot_password' && $_GET['ec_page'] == 'forgot_password' )			return true;
				else if( $page_name == 'register' && $_GET['ec_page'] == 'register' )					return true;
				else if( $page_name == 'login' && $_GET['ec_page'] != 'register' && $_GET['ec_page'] != 'forgot_password' && $_GET['ec_page'] != 'order_details' )	
																										return true;
				else if( $page_name == 'order_details' && $_GET['ec_page'] == 'order_details' )			return true;
				else																					return false; 
			}else if( isset( $_GET['ec_guest_key'] ) && $_GET['ec_guest_key'] ){ // guests can see their order with a key
				if( $page_name == 'forgot_password' && $_GET['ec_page'] == 'forgot_password' )			return true;
				else if( $page_name == 'register' && $_GET['ec_page'] == 'register' )					return true;
				else if( $page_name == 'login' && $_GET['ec_page'] != 'register' && $_GET['ec_page'] != 'forgot_password' && $_GET['ec_page'] != 'order_details' )	
																										return true;
				else if( $page_name == 'order_details' && $_GET['ec_page'] == 'order_details' )			return true;
				else																					return false; 
			}else{ //If not logged in we can only show login or register
				if( $page_name == 'forgot_password' && $_GET['ec_page'] == 'forgot_password' )			return true;
				else if( $page_name == 'register' && $_GET['ec_page'] == 'register' )					return true;
				else if( $page_name == 'login' && $_GET['ec_page'] != 'register' && $_GET['ec_page'] != 'forgot_password' )	
																										return true;
				else																					return false;
			}
		}else{ //ec_page variable is not set
			if( isset( $_SESSION['ec_password'] ) && $_SESSION['ec_password'] != "guest" ){ //If logged in we should only show dashboard here
				if( $page_name == 'dashboard' )										return true;
				else																return false;
			}else{ //If not logged in we should only show login here
				if( $page_name == 'login' )											return true;
				else																return false;
			}
		}
	}
	
	/* START ACCOUNT LOGIN FUNCTIONS */
	public function display_account_login( ){
		if( $this->is_page_visible( "login" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_login.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_login.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_login.php' );
		}
	}
	
	public function display_account_login_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
	}
	
	public function display_account_login_form_end( ){
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" value=\"login\" />";
		echo "</form>";	
	}
	
	public function display_account_login_email_input( ){
		echo "<input type=\"email\" name=\"ec_account_login_email\" id=\"ec_account_login_email\" class=\"ec_account_login_input_field\" autocomplete=\"off\" autocapitalize=\"off\">";
	}
	
	public function display_account_login_password_input( ){
		echo "<input type=\"password\" name=\"ec_account_login_password\" id=\"ec_account_login_password\" class=\"ec_account_login_input_field\">";
	}
	
	public function display_account_login_button( $button_text ){
		echo "<input type=\"submit\" name=\"ec_account_login_button\" id=\"ec_account_login_button\" class=\"ec_account_login_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_login_button_click();\">";
	}
	
	public function display_account_login_forgot_password_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=forgot_password\" class=\"ec_account_login_link\">" . $link_text . "</a>";
	}
	
	public function display_account_login_create_account_button( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=register\" class=\"ec_account_login_create_account_button\">" . $link_text . "</a>";
	}
	
	/* END ACCOUNT LOGIN FUNCTIONS */
	
	/* START FORGOT PASSWORD FUNCTIONS */
	public function display_account_forgot_password( ){
		if( $this->is_page_visible( "forgot_password" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_forgot_password.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_forgot_password.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_forgot_password.php' );
		}
	}
	
	public function display_account_forgot_password_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\" />";	
	}
	
	public function display_account_forgot_password_form_end( ){
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" value=\"retrieve_password\" />";
		echo "</form>";
	}
	
	public function display_account_forgot_password_email_input( ){
		echo "<input type=\"email\" name=\"ec_account_forgot_password_email\" id=\"ec_account_forgot_password_email\" class=\"ec_account_forgot_password_input_field\">";	
	}
	
	public function display_account_forgot_password_submit_button( $button_text ){
		echo "<input type=\"submit\" name=\"ec_account_forgot_password_button\" id=\"ec_account_forgot_password_button\" class=\"ec_account_forgot_password_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_forgot_password_button_click();\">";
	}
	/* END FORGOT PASSWORD FUNCTIONS*/
	
	/* START REGISTER FUNCTIONS */
	public function display_account_register( ){
		if( $this->is_page_visible( "register" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_register.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_register.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_register.php' );
		}
	}
	
	public function display_account_register_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
	}
	
	public function display_account_register_form_end( ){
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" value=\"register\"/>";
		echo "</form>";
	}
	
	public function display_account_register_first_name_input( ){
		echo "<input type=\"text\" name=\"ec_account_register_first_name\" id=\"ec_account_register_first_name\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_last_name_input( ){
		echo "<input type=\"text\" name=\"ec_account_register_last_name\" id=\"ec_account_register_last_name\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_zip_input( ){
		echo "<input type=\"text\" name=\"ec_account_register_zip\" id=\"ec_account_register_zip\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_email_input( ){
		echo "<input type=\"email\" name=\"ec_account_register_email\" id=\"ec_account_register_email\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_retype_email_input( ){
		echo "<input type=\"email\" name=\"ec_account_register_retype_email\" id=\"ec_account_register_retype_email\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_password_input( ){
		echo "<input type=\"password\" name=\"ec_account_register_password\" id=\"ec_account_register_password\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_retype_password_input( ){
		echo "<input type=\"password\" name=\"ec_account_register_password_retype\" id=\"ec_account_register_password_retype\" class=\"ec_account_register_input_field\">";
	}
	
	public function display_account_register_is_subscriber_input( ){
		echo "<input type=\"checkbox\" name=\"ec_account_register_is_subscriber\" id=\"ec_account_register_is_subscriber\" class=\"ec_account_register_input_field\" />";	
	}
	
	public function display_account_register_button( $button_text ){
		if( get_option( 'ec_option_require_account_address' ) )
			echo "<input type=\"submit\" name=\"ec_account_register_button\" id=\"ec_account_register_button\" class=\"ec_account_register_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_register_button_click2( );\">";
		else
			echo "<input type=\"submit\" name=\"ec_account_register_button\" id=\"ec_account_register_button\" class=\"ec_account_register_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_register_button_click( );\">";
	}
	/* END REGISTER FUNCTIONS */
	
	/* START DASHBOARD FUNCTIONS */
	public function display_account_dashboard( ){
		if( $this->is_page_visible( "dashboard" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_dashboard.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_dashboard.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_dashboard.php' );
		}
	}
	
	public function display_dashboard_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=dashboard\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";	
	}
	
	public function display_orders_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=orders\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";	
	}
	
	public function display_personal_information_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=personal_information\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	
	public function display_billing_information_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=billing_information\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	
	public function display_shipping_information_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=shipping_information\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	
	public function display_password_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=password\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	
	public function display_subscriptions_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=subscriptions\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	
	public function display_payment_methods_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=payment_methods\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	
	public function display_logout_link( $link_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=logout\" class=\"ec_account_dashboard_link\">" . $link_text . "</a>";
	}
	/* END DASHBOARD FUNCTIONS */
	
	/* START ORDERS FUNCTIONS */
	public function display_account_orders( ){
		if( $this->is_page_visible( "orders" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_orders.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_orders.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_orders.php' );
		}
	}
	/* END ORDERS FUNCTIONS*/
	
	/* START ORDER DETAILS FUNCTIONS */
	public function display_account_order_details( ){
		if( $this->is_page_visible( "order_details" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_order_details.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_order_details.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_order_details.php' );
		}
	}
	
	public function display_order_detail_product_list( ){
		if( $this->order ){
			$this->order->display_order_detail_product_list( );
		}
	}
	
	public function display_print_order_icon( ){
		if( $this->order ){
			if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_account_order_details/print_icon.png" ) )	
				echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=print_receipt&order_id=" . $this->order->order_id . "\" target=\"_blank\"><img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_account_order_details/print_icon.png" ) . "\" alt=\"print\" /></a>";
			else
				echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=print_receipt&order_id=" . $this->order->order_id . "\" target=\"_blank\"><img src=\"" . plugins_url( EC_PLUGIN_DIRECTORY . "/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_account_order_details/print_icon.png" ) . "\" alt=\"print\" /></a>";
		}
	}
	
	public function get_print_order_icon_url( ){
		if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/images/print_icon.png" ) )
			return plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/images/print_icon.png"  );
		else
			return plugins_url( "wp-easycart/design/theme/" . get_option( 'ec_option_latest_theme' ) . "/images/print_icon.png"  );
	}
	
	public function display_complete_payment_link( ){
		if( $this->order && $this->order->orderstatus_id == 8 ){
			echo "<a href=\"" . $this->cart_page . $this->permalink_divider . "ec_page=third_party&order_id=" . $this->order->order_id . "\" class=\"ec_account_complete_order_link\">Complete Order Payment</a> ";
		}
	}
	/* END ORDER DETAILS FUNCTIONS*/
	
	/* START PERSONAL INFORMATION FUNCTIONS */
	public function display_account_personal_information( ){
		if( $this->is_page_visible( "personal_information" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_personal_information.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_personal_information.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_personal_information.php' );
		}
	}
	
	public function display_account_personal_information_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" id=\"ec_account_personal_information_form_action\" value=\"update_personal_information\" />";
	}
	
	public function display_account_personal_information_form_end( ){
		echo "</form>";
	}
	
	public function display_account_personal_information_first_name_input( ){
		echo "<input type=\"text\" name=\"ec_account_personal_information_first_name\" id=\"ec_account_personal_information_first_name\" class=\"ec_account_personal_information_input_field\" value=\"" . $this->user->first_name . "\" />";
	}
	
	public function display_account_personal_information_last_name_input( ){
		echo "<input type=\"text\" name=\"ec_account_personal_information_last_name\" id=\"ec_account_personal_information_last_name\" class=\"ec_account_personal_information_input_field\" value=\"" . $this->user->last_name . "\" />";
	}
	
	public function display_account_personal_information_zip_input( ){
		echo "<input type=\"text\" name=\"ec_account_personal_information_zip\" id=\"ec_account_personal_information_zip\" class=\"ec_account_personal_information_input_field\" value=\"" . $this->user->billing->zip . "\" />";
	}
	
	public function display_account_personal_information_email_input( ){
		echo "<input type=\"email\" name=\"ec_account_personal_information_email\" id=\"ec_account_personal_information_email\" class=\"ec_account_personal_information_input_field\" value=\"" . $this->user->email . "\" />";
	}
	
	public function display_account_personal_information_is_subscriber_input( ){
		echo "<input type=\"checkbox\" name=\"ec_account_personal_information_is_subscriber\" id=\"ec_account_personal_information_is_subscriber\" class=\"ec_account_personal_information_input_field\"";
		if( $this->user->is_subscriber )
		echo " checked=\"checked\"";
		echo "/>";
	}
	
	public function display_account_personal_information_update_button( $button_text ){
		echo "<input type=\"submit\" name=\"ec_account_personal_information_button\" id=\"ec_account_personal_information_button\" class=\"ec_account_personal_information_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_personal_information_update_click();\" />";
	}
	public function display_account_personal_information_cancel_link( $button_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=dashboard\" class=\"ec_account_personal_information_link\"><input type=\"button\" name=\"ec_account_personal_information_button\" id=\"ec_account_personal_information_button\" class=\"ec_account_personal_information_button\" value=\"" . $button_text . "\" onclick=\"window.location='" . $this->account_page . $this->permalink_divider . "ec_page=dashboard'\" /></a>";
	}
	

	/* END PERSONAL INFORMATION FUNCTIONS */
	
	/* START PASSWORD FUNCTIONS */
	public function display_account_password( ){
		if( $this->is_page_visible( "password" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_password.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_password.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_password.php' );
		}
	}
	
	public function display_account_password_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" id=\"ec_account_password_form_action\" value=\"update_password\" />";
	}
	
	public function display_account_password_form_end( ){
		echo "</form>";
	}
	
	public function display_account_password_current_password( ){
		echo "<input type=\"password\" name=\"ec_account_password_current_password\" id=\"ec_account_password_current_password\" class=\"ec_account_password_input_field\">";
	}
	
	public function display_account_password_new_password( ){
		echo "<input type=\"password\" name=\"ec_account_password_new_password\" id=\"ec_account_password_new_password\" class=\"ec_account_password_input_field\">";
	}
	
	public function display_account_password_retype_new_password( ){
		echo "<input type=\"password\" name=\"ec_account_password_retype_new_password\" id=\"ec_account_password_retype_new_password\" class=\"ec_account_password_input_field\">";
	}
	
	public function display_account_password_update_button( $button_text ){
		echo "<input type=\"submit\" name=\"ec_account_password_button\" id=\"ec_account_password_button\" class=\"ec_account_password_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_password_button_click();\" />";
	}
	public function display_account_password_cancel_link( $button_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=dashboard\" class=\"ec_account_password_link\"><input type=\"button\" name=\"ec_account_password_button\" id=\"ec_account_password_button\" class=\"ec_account_password_button\" value=\"" . $button_text . "\" onclick=\"window.location='" . $this->account_page . $this->permalink_divider . "ec_page=dashboard'\" /></a>";
	}

	/* END PASSWORD FUNCTIONS */
	
	/* START BILLING INFORMATION FUNCTIONS */
	public function display_account_billing_information( ){
		if( $this->is_page_visible( "billing_information" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_billing_information.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_billing_information.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_billing_information.php' );
		}
	}
	
	public function display_account_billing_information_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" id=\"ec_account_billing_information_form_action\" value=\"update_billing_information\" />";
	}
	
	public function display_account_billing_information_form_end( ){
		echo "</form>";
	}
	
	public function display_account_billing_information_first_name_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_first_name\" id=\"ec_account_billing_information_first_name\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->first_name . "\" />";
	}
	
	public function display_account_billing_information_last_name_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_last_name\" id=\"ec_account_billing_information_last_name\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->last_name . "\" />";
	}
	
	public function display_account_billing_information_company_name_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_company_name\" id=\"ec_account_billing_information_company_name\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->company_name . "\" />";
	}
	
	public function display_account_billing_information_address_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_address\" id=\"ec_account_billing_information_address\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->address_line_1 . "\" />";
	}
	
	public function display_account_billing_information_address2_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_address2\" id=\"ec_account_billing_information_address2\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->address_line_2 . "\" />";
	}
	
	public function display_account_billing_information_city_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_city\" id=\"ec_account_billing_information_city\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->city . "\" />";
	}
	
	public function display_account_billing_information_state_input(){
		
		if( get_option( 'ec_option_use_smart_states' ) ){
			
			// DISPLAY STATE DROP DOWN MENU
			$states = $this->mysqli->get_states( );
			$selected_state = $this->user->billing->get_value( "state" );
			$selected_country = $this->user->billing->get_value( "country2" );
			
			$current_country = "";
			$close_last_state = false;
			$state_found = false;
			$current_state_group = "";
			$close_last_state_group = false;
			
			foreach($states as $state){
				if( $current_country != $state->iso2_cnt ){
					if( $close_last_state ){
						echo "</select>";
					}
					echo "<select name=\"ec_account_billing_information_state_" . $state->iso2_cnt . "\" id=\"ec_account_billing_information_state_" . $state->iso2_cnt . "\" class=\"ec_account_billing_information_input_field ec_billing_state_dropdown\"";
					if( $state->iso2_cnt != $selected_country ){
						echo " style=\"display:none;\"";
					}else{
						$state_found = true;
					}
					echo ">";
					
					if( $state->iso2_cnt == "CA" ){ // Canada
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_billing_information", "cart_billing_information_select_province" ) . "</option>";
					}else if( $state->iso2_cnt == "GB" ){ // United Kingdom
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_billing_information", "cart_billing_information_select_county" ) . "</option>";
					}else if( $state->iso2_cnt == "US" ){ //USA 
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_billing_information", "cart_billing_information_select_state" ) . "</option>";
					}else{
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_billing_information", "cart_billing_information_select_other" ) . "</option>";
					}
					
					$current_country = $state->iso2_cnt;
					$close_last_state = true;
				}
				
				if( $current_state_group != $state->group_sta && $state->group_sta != "" ){
					if( $close_last_state_group ){
						echo "</optgroup>";
					}
					echo "<optgroup label=\"" . $state->group_sta . "\">";
					$current_state_group = $state->group_sta;
					$close_last_state_group = true;
				}
				
				echo "<option value=\"" . $state->code_sta . "\"";
				if( $state->code_sta == $selected_state )
					echo " selected=\"selected\"";
				echo ">" . $state->name_sta . "</option>";
			}
			
			if( $close_last_state_group ){
				echo "</optgroup>";
			}
			
			echo "</select>";
			
			// DISPLAY STATE TEXT INPUT	
			echo "<input type=\"text\" name=\"ec_account_billing_information_state\" id=\"ec_account_billing_information_state\" class=\"ec_account_billing_information_input_field\" value=\"" . $selected_state . "\"";
			if( $state_found ){
				echo " style=\"display:none;\"";
			}
			echo " />";
			
		}else{
			// Use the basic method of old
			if( get_option( 'ec_option_use_state_dropdown' ) ){
				$states = $this->mysqli->get_states( );
				$selected_state = $this->user->billing->state;
				
				echo "<select name=\"ec_account_billing_information_state\" id=\"ec_account_billing_information_state\" class=\"ec_account_billing_information_input_field\">";
				echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "account_billing_information", "account_billing_information_default_no_state" ) . "</option>";
				foreach($states as $state){
					echo "<option value=\"" . $state->code_sta . "\"";
					if( $state->code_sta == $selected_state )
					echo " selected=\"selected\"";
					echo ">" . $state->name_sta . "</option>";
				}
				echo "</select>";
			}else{
				echo "<input type=\"text\" name=\"ec_account_billing_information_state\" id=\"ec_account_billing_information_state\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->state . "\" />";
			}
		}// Close if/else for state display type
		
	}
	
	public function display_account_billing_information_zip_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_zip\" id=\"ec_account_billing_information_zip\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->zip . "\" />";
	}
	
	public function display_account_billing_information_country_input(){
		if( get_option( 'ec_option_use_country_dropdown' ) ){
			$countries = $this->mysqli->get_countries( );
			$selected_country = $this->user->billing->country;
			
			echo "<select name=\"ec_account_billing_information_country\" id=\"ec_account_billing_information_country\" class=\"ec_account_billing_information_input_field\">";
			echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "account_billing_information", "account_billing_information_default_no_country" ) . "</option>";
			foreach($countries as $country){
				echo "<option value=\"" . $country->iso2_cnt . "\"";
				if( $country->iso2_cnt == $selected_country )
				echo " selected=\"selected\"";
				echo ">" . $country->name_cnt . "</option>";
			}
			echo "</select>";
		}else{
			echo "<input type=\"text\" name=\"ec_account_billing_information_country\" id=\"ec_account_billing_information_country\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->country . "\" />";
		}
	}
	
	public function display_account_billing_information_phone_input(){
		echo "<input type=\"text\" name=\"ec_account_billing_information_phone\" id=\"ec_account_billing_information_phone\" class=\"ec_account_billing_information_input_field\" value=\"" . $this->user->billing->phone . "\" />";
	}
	
	public function display_account_billing_information_update_button( $button_text ){
		echo "<input type=\"submit\" name=\"ec_account_billing_information_button\" id=\"ec_account_billing_information_button\" class=\"ec_account_billing_information_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_billing_information_update_click();\" />";
	}
	public function display_account_billing_information_cancel_link( $button_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=dashboard\" class=\"ec_account_billing_information_link\">" . "<input type=\"button\" name=\"ec_account_billing_information_button\" id=\"ec_account_billing_information_button\" class=\"ec_account_billing_information_button\" value=\"" . $button_text . "\" onclick=\"window.location='" . $this->account_page . $this->permalink_divider . "ec_page=dashboard'\" /></a>";
	}
	

	/* END BILLING INFORMATION FUNCTIONS */
	
	/* START SHIPPING INFORMATION FUNCTIONS */
	public function display_account_shipping_information( ){
		if( $this->is_page_visible( "shipping_information" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_shipping_information.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_shipping_information.php' );
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_shipping_information.php' );
		}
	}
	
	public function display_account_shipping_information_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" id=\"ec_account_shipping_information_form_action\" value=\"update_shipping_information\" />";
	}
	
	public function display_account_shipping_information_form_end( ){
		echo "</form>";
	}
	
	public function display_account_shipping_information_first_name_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_first_name\" id=\"ec_account_shipping_information_first_name\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->first_name . "\" />";
	}
	
	public function display_account_shipping_information_last_name_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_last_name\" id=\"ec_account_shipping_information_last_name\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->last_name . "\" />";
	}
	
	public function display_account_shipping_information_address_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_address\" id=\"ec_account_shipping_information_address\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->address_line_1 . "\" />";
	}
	
	public function display_account_shipping_information_address2_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_address2\" id=\"ec_account_shipping_information_address2\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->address_line_2 . "\" />";
	}
	
	public function display_account_shipping_information_city_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_city\" id=\"ec_account_shipping_information_city\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->city . "\" />";
	}
	
	public function display_account_shipping_information_state_input(){
		
		if( get_option( 'ec_option_use_smart_states' ) ){
			
			// DISPLAY STATE DROP DOWN MENU
			$states = $this->mysqli->get_states( );
			$selected_state = $this->user->shipping->get_value( "state" );
			$selected_country = $this->user->shipping->get_value( "country2" );
			
			$current_country = "";
			$close_last_state = false;
			$state_found = false;
			$current_state_group = "";
			$close_last_state_group = false;
			
			foreach($states as $state){
				if( $current_country != $state->iso2_cnt ){
					if( $close_last_state ){
						echo "</select>";
					}
					echo "<select name=\"ec_account_shipping_information_state_" . $state->iso2_cnt . "\" id=\"ec_account_shipping_information_state_" . $state->iso2_cnt . "\" class=\"ec_account_shipping_information_input_field ec_shipping_state_dropdown\"";
					if( $state->iso2_cnt != $selected_country ){
						echo " style=\"display:none;\"";
					}else{
						$state_found = true;
					}
					echo ">";
					
					if( $state->iso2_cnt == "CA" ){ // Canada
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_shipping_information", "cart_shipping_information_select_province" ) . "</option>";
					}else if( $state->iso2_cnt == "GB" ){ // United Kingdom
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_shipping_information", "cart_shipping_information_select_county" ) . "</option>";
					}else if( $state->iso2_cnt == "US" ){ //USA 
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_shipping_information", "cart_shipping_information_select_state" ) . "</option>";
					}else{
						echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "cart_shipping_information", "cart_shipping_information_select_other" ) . "</option>";
					}
					
					$current_country = $state->iso2_cnt;
					$close_last_state = true;
				}
				
				if( $current_state_group != $state->group_sta && $state->group_sta != "" ){
					if( $close_last_state_group ){
						echo "</optgroup>";
					}
					echo "<optgroup label=\"" . $state->group_sta . "\">";
					$current_state_group = $state->group_sta;
					$close_last_state_group = true;
				}
				
				echo "<option value=\"" . $state->code_sta . "\"";
				if( $state->code_sta == $selected_state )
					echo " selected=\"selected\"";
				echo ">" . $state->name_sta . "</option>";
			}
			
			if( $close_last_state_group ){
				echo "</optgroup>";
			}
			
			echo "</select>";
			
			// DISPLAY STATE TEXT INPUT	
			echo "<input type=\"text\" name=\"ec_account_shipping_information_state\" id=\"ec_account_shipping_information_state\" class=\"ec_account_shipping_information_input_field\" value=\"" . $selected_state . "\"";
			if( $state_found ){
				echo " style=\"display:none;\"";
			}
			echo " />";
			
		}else{
			// Use the basic method of old
			if( get_option( 'ec_option_use_state_dropdown' ) ){
				$states = $this->mysqli->get_states( );
				$selected_state = $this->user->shipping->state;
				
				echo "<select name=\"ec_account_shipping_information_state\" id=\"ec_account_shipping_information_state\" class=\"ec_account_shipping_information_input_field\">";
				echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "account_shipping_information", "account_shipping_information_default_no_state" ) . "</option>";
				foreach($states as $state){
					echo "<option value=\"" . $state->code_sta . "\"";
					if( $state->code_sta == $selected_state )
					echo " selected=\"selected\"";
					echo ">" . $state->name_sta . "</option>";
				}
				echo "</select>";
			}else{
				echo "<input type=\"text\" name=\"ec_account_shipping_information_state\" id=\"ec_account_shipping_information_state\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->state . "\" />";
			}
		}// Close if/else for state display type
		
	}
	
	public function display_account_shipping_information_zip_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_zip\" id=\"ec_account_shipping_information_zip\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->zip . "\" />";
	}
	
	public function display_account_shipping_information_country_input(){
		if( get_option( 'ec_option_use_country_dropdown' ) ){
			$countries = $this->mysqli->get_countries( );
			$selected_country = $this->user->shipping->country;
			
			echo "<select name=\"ec_account_shipping_information_country\" id=\"ec_account_shipping_information_country\" class=\"ec_account_shipping_information_input_field\">";
			echo "<option value=\"0\">" . $GLOBALS['language']->get_text( "account_shipping_information", "account_shipping_information_default_no_country" ) . "</option>";
			foreach($countries as $country){
				echo "<option value=\"" . $country->iso2_cnt . "\"";
				if( $country->iso2_cnt == $selected_country )
				echo " selected=\"selected\"";
				echo ">" . $country->name_cnt . "</option>";
			}
			echo "</select>";
		}else{
			echo "<input type=\"text\" name=\"ec_account_shipping_information_country\" id=\"ec_account_shipping_information_country\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->country . "\" />";
		}
	}
	
	public function display_account_shipping_information_phone_input(){
		echo "<input type=\"text\" name=\"ec_account_shipping_information_phone\" id=\"ec_account_shipping_information_phone\" class=\"ec_account_shipping_information_input_field\" value=\"" . $this->user->shipping->phone . "\" />";
	}
	
	public function display_account_shipping_information_update_button( $button_text ){
		echo "<input type=\"submit\" name=\"ec_account_shipping_information_button\" id=\"ec_account_shipping_information_button\" class=\"ec_account_shipping_information_button\" value=\"" . $button_text . "\" onclick=\"return ec_account_shipping_information_update_click();\" />";
	}
	
	public function display_account_shipping_information_cancel_link( $button_text ){
		echo "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=dashboard\" class=\"ec_account_shipping_information_link\">" ."<input type=\"button\" name=\"ec_account_shipping_information_button\" id=\"ec_account_shipping_information_button\" class=\"ec_account_shipping_information_button\" value=\"" . $button_text . "\" onclick=\"window.location='" . $this->account_page . $this->permalink_divider . "ec_page=dashboard'\" /></a>";
	}
	

	/* END SHIPPING INFORMATION FUNCTIONS */
	
	
	/* START SUBSCRIPTIONS FUNCTIONS */
	public function display_account_subscriptions( ){
		if( $this->is_page_visible( "subscriptions" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_subscriptions.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_subscriptions.php' );
			else if( file_exists( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_subscriptions.php' ) )
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_subscriptions.php' );
		}
	}
	
	public function using_subscriptions( ){
		if( get_option( 'ec_option_payment_process_method' ) == "stripe" && get_option( 'ec_option_show_account_subscriptions_link' ) ){
			return true;
		}else{
			return false;
		}
	}
	/* END SUBSCRIPTIONS FUNCTIONS*/
	
	/* START SUBSCRIPTION DETAILS FUNCTIONS */
	public function display_account_subscription_details( ){
		if( $this->is_page_visible( "subscription_details" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_subscription_details.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_subscription_details.php' );
			else if( file_exists( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_subscription_details.php' ) )
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_subscription_details.php' );
		}
	}
	
	/* END SUBSCRIPTION DETAILS FUNCTIONS */
	
	/* START PAYMENT METHODS FUNCTIONS */
	public function display_account_payment_methods( ){
		if( $this->is_page_visible( "payment_methods" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_payment_methods.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_payment_methods.php' );
			else if( file_exists( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_payment_methods.php' ) )
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_payment_methods.php' );
		}
	}
	/* END PAYMENT METHODS FUNCTIONS*/
	
	/* START PAYMENT METHOD DETAILS FUNCTIONS */
	public function display_account_payment_method_details( ){
		if( $this->is_page_visible( "payment_method_details" ) ){
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_payment_method_details.php' ) )	
				include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_payment_method_details.php' );
			else if( file_exists( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_payment_method_details.php' ) )
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_payment_method_details.php' );
		}
	}
	
	/* END PAYMENT METHOD DETAILS FUNCTIONS */
	
	/* START FORM ACTION FUNCTIONS */
	public function process_form_action( $action ){
		if( $action == "login" )
			$this->process_login( );
		else if( $action == "register" )
			$this->process_register( );
		else if( $action == "retrieve_password" )
			$this->process_retrieve_password( );
		else if( $action == "update_personal_information" )
			$this->process_update_personal_information( );
		else if( $action == "update_password" )
			$this->process_update_password( );
		else if( $action == "update_billing_information" )
			$this->process_update_billing_information( );
		else if( $action == "update_shipping_information" )
			$this->process_update_shipping_information( );
		else if( $action == "logout" )
			$this->process_logout( );
		else if( $action == "update_subscription" )
			$this->process_update_subscription( );
		else if( $action == "cancel_subscription" )
			$this->process_cancel_subscription( );
		else if( $action == "order_create_account" )
			$this->process_order_create_account( );
	}
	
	private function process_login( ){
		$email = $_POST['ec_account_login_email'];
		$password = md5( $_POST['ec_account_login_password'] );
		
		$user = $this->mysqli->get_user( $email, $password );
		
		if( $user && $user->user_level == "pending" ){
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=login&account_error=not_activated" );
			
		}else if( $user ){
			$_SESSION['ec_user_id'] = $user->user_id;
			$_SESSION['ec_email'] = $email;
			$_SESSION['ec_username'] = $user->first_name . " " . $user->last_name;
			$_SESSION['ec_first_name'] = $user->first_name;
			$_SESSION['ec_last_name'] = $user->last_name;
			$_SESSION['ec_password'] = $password;
		
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard" );
			
		}else{
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=login&account_error=login_failed" );
				
		}
	}
	
	private function process_register( ){
		
		if( isset( $_POST['ec_account_register_email'] ) && isset( $_POST['ec_account_register_password'] ) && $_POST['ec_account_register_email'] != "" && $_POST['ec_account_register_password'] != "" ){
		
			$first_name = "";
			if( isset( $_POST['ec_account_register_first_name'] ) )
				$first_name = $_POST['ec_account_register_first_name'];
			
			$last_name = "";
			if( isset( $_POST['ec_account_register_last_name'] ) )
				$last_name = $_POST['ec_account_register_last_name'];
				
			$email = $_POST['ec_account_register_email'];
			$password = md5( $_POST['ec_account_register_password'] );
			
			$is_subscriber = $_POST['ec_account_register_is_subscriber'];
			$billing_id = 0;
			
			// Insert billing address if enabled
			if( get_option( 'ec_option_require_account_address' ) ){
				$billing = array( "first_name" 	=> $_POST['ec_account_billing_information_first_name'],
								  "last_name"	=> $_POST['ec_account_billing_information_last_name'],
								  "address"		=> $_POST['ec_account_billing_information_address'],
								  "city"		=> $_POST['ec_account_billing_information_city'],
								  "zip_code"	=> $_POST['ec_account_billing_information_zip'],
								  "country"		=> $_POST['ec_account_billing_information_country'],
								);
								
				if( isset( $_POST['ec_account_billing_information_state_' . $billing['country']] ) ){
					$billing['state'] = stripslashes( $_POST['ec_account_billing_information_state_' . $billing['country']] );
				}else{
					$billing['state'] = stripslashes( $_POST['ec_account_billing_information_state'] );
				}
				
				if( isset( $_POST['ec_account_billing_information_company_name'] ) ){
					$billing['company_name'] = stripslashes( $_POST['ec_account_billing_information_company_name'] );
				}else{
					$billing['company_name'] = "";
				}
				
				if( isset( $_POST['ec_account_billing_information_address2'] ) ){
					$billing['address2'] = stripslashes( $_POST['ec_account_billing_information_address2'] );
				}else{
					$billing['address2'] = "";
				}
				
				if( isset( $_POST['ec_account_billing_information_phone'] ) ){
					$billing['phone'] = stripslashes( $_POST['ec_account_billing_information_phone'] );
				}else{
					$billing['phone'] = "";
				}
				
				$billing_id = $this->mysqli->insert_address( $billing["first_name"], $billing["last_name"], $billing["address"], $billing["address2"], $billing["city"], $billing["state"], $billing["zip_code"], $billing["country"], $billing["phone"], $billing["company_name"] );
			
			}
			
			if( isset( $_POST['ec_account_register_user_notes'] ) ){
				$user_notes = stripslashes( $_POST['ec_account_register_user_notes'] );
			}else{
				$user_notes = "";
			}
			
			// Insert the user
			if( get_option( 'ec_option_require_email_validation' ) ){
				// Send a validation email here.
				$this->send_validation_email( $email );
				$user_id = $this->mysqli->insert_user( $email, $password, $first_name, $last_name, $billing_id, 0, "pending", $is_subscriber, $user_notes );
			}else{
				$user_id = $this->mysqli->insert_user( $email, $password, $first_name, $last_name, $billing_id, 0, "shopper", $is_subscriber, $user_notes );
			}
			
			// Update the address user_id
			if( get_option( 'ec_option_require_account_address' ) ){
				$this->mysqli->update_address_user_id( $billing_id, $user_id );
			}
			
			// Quickbooks Hook
			if( file_exists( WP_PLUGIN_DIR . "/" . EC_QB_PLUGIN_DIRECTORY . "/ec_quickbooks.php" ) ){
				$quickbooks = new ec_quickbooks( );
				$quickbooks->add_user( $user_id );
			}
			
			if( $user_id ){
				
				if( get_option( 'ec_option_require_email_validation' ) ){
				
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=login&account_success=validation_required" );
				
				}else{
					
					$_SESSION['ec_user_id'] = $user_id;
					$_SESSION['ec_email'] = $email;
					$_SESSION['ec_username'] = $first_name . " " . $last_name;
					$_SESSION['ec_first_name'] = $first_name;
					$_SESSION['ec_last_name'] = $last_name;
					$_SESSION['ec_password'] = $password;
					
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard" );
					
				}
				
			}else{
				
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=register&account_error=register_email_error" );
					
			}
			
		}else{
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=register&account_error=register_invalid" );
		
		}
		
	}
	
	private function process_retrieve_password( ){
		$email = $_POST['ec_account_forgot_password_email'];
		$new_password = $this->get_random_password( );
		
		$success = $this->mysqli->reset_password( $email, md5( $new_password ) );
		
		if( $success ){
			$this->send_new_password_email( $email, $new_password );
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=login&account_success=reset_email_sent" );
		}else{
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=register&account_error=no_reset_email_found" );
		}
		
	}
	
	private function process_update_personal_information( ){
		$old_email = $_SESSION['ec_email'];
		$password = $_SESSION['ec_password'];
		$first_name = $_POST['ec_account_personal_information_first_name'];
		$last_name = $_POST['ec_account_personal_information_last_name'];
		$email = $_POST['ec_account_personal_information_email'];
		if( isset( $_POST['ec_account_personal_information_is_subscriber'] ) &&  $_POST['ec_account_personal_information_is_subscriber'] )
			$is_subscriber = 1;
		else
			$is_subscriber = 0;
		
		$success = $this->mysqli->update_personal_information( $old_email, $password, $first_name, $last_name, $email, $is_subscriber );
		
		//Update Custom Fields if They Exist
		if( count( $this->user->customfields ) > 0 ){
			for( $i=0; $i<count( $this->user->customfields ); $i++ ){
				$this->mysqli->update_customfield_data( $this->user->customfields[$i][0], $_POST['ec_user_custom_field_' . $this->user->customfields[$i][0]] );
			}
		}
		
		if( $success >= 0 ){
			$_SESSION['ec_email'] = $email;
			$_SESSION['ec_username'] = $first_name . " " . $last_name;
			$_SESSION['ec_first_name'] = $first_name;
			$_SESSION['ec_last_name'] = $last_name;
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard&account_success=personal_information_updated" );
		}else
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=personal_information&account_error=personal_information_update_error" );
		
	}
	
	private function process_update_password( ){
		$session_email = $_SESSION['ec_email'];
		$session_password = $_SESSION['ec_password'];
		
		$current_password = md5( $_POST['ec_account_password_current_password'] );
		$new_password = md5( $_POST['ec_account_password_new_password'] );
		$retype_new_password = md5( $_POST['ec_account_password_retype_new_password'] );
		
		if( $session_password != $current_password )
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=password&account_error=password_wrong_current" );
		else if( $new_password != $retype_new_password )
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=password&account_error=password_no_match" );
		else{
			$success = $this->mysqli->update_password( $session_email, $current_password, $new_password );
			
			if( $success >= 0 ){
				$_SESSION['ec_password'] = $new_password;
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard&account_success=password_updated" );
			}else
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=password&account_error=password_update_error" );
		}
	}
	
	private function process_update_billing_information( ){
		
		$country = $_POST['ec_account_billing_information_country'];
		
		$first_name = $_POST['ec_account_billing_information_first_name'];
		$last_name = $_POST['ec_account_billing_information_last_name'];
		if( isset( $_POST['ec_account_billing_information_company_name'] ) ){
			$company_name = $_POST['ec_account_billing_information_company_name'];
		}else{
			$company_name = "";
		}
		$address = $_POST['ec_account_billing_information_address'];
		if( isset( $_POST['ec_account_billing_information_address2'] ) ){
			$address2 = $_POST['ec_account_billing_information_address2'];
		}else{
			$address2 = "";
		}
		
		$city = $_POST['ec_account_billing_information_city'];
		if( isset( $_POST['ec_account_billing_information_state_' . $country] ) ){
			$state = $_POST['ec_account_billing_information_state_' . $country];
		}else{
			$state = $_POST['ec_account_billing_information_state'];
		}
		
		$zip = $_POST['ec_account_billing_information_zip'];
		$phone = $_POST['ec_account_billing_information_phone'];
		
		if( $first_name == $this->user->billing->first_name && 
			$last_name == $this->user->billing->last_name && 
			$company_name == $this->user->billing->company_name && 
			$address == $this->user->billing->address_line_1 && 
			$address2 == $this->user->billing->address_line_2 && 
			$city == $this->user->billing->city && 
			$state == $this->user->billing->state && 
			$zip == $this->user->billing->zip && 
			$country == $this->user->billing->country &&
			$phone == $this->user->billing->phone ){
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard&account_success=billing_information_updated" );
				
		}else{
		
			$address_id = $this->user->billing_id;
			if( $address_id )
				$success = $this->mysqli->update_user_address( $address_id, $first_name, $last_name, $address, $address2, $city, $state, $zip, $country, $phone, $company_name );
			else{
				$success = $this->mysqli->insert_user_address( $first_name, $last_name, $address, $address2, $city, $state, $zip, $country, $phone, $this->user_email, $this->user_password, "billing", $company_name );
			}
			
			if( $success >= 0 )
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard&account_success=billing_information_updated" );
			else
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=billing_information&account_error=billing_information_error" );
			
		}
	}
	
	private function process_update_shipping_information( ){
		
		$country = $_POST['ec_account_shipping_information_country'];
		
		$first_name = $_POST['ec_account_shipping_information_first_name'];
		$last_name = $_POST['ec_account_shipping_information_last_name'];
		if( isset( $_POST['ec_account_shipping_information_company_name'] ) ){
			$company_name = $_POST['ec_account_shipping_information_company_name'];
		}else{
			$company_name = "";
		}
		$address = $_POST['ec_account_shipping_information_address'];
		if( isset( $_POST['ec_account_shipping_information_address2'] ) ){
			$address2 = $_POST['ec_account_shipping_information_address2'];
		}else{
			$address2 = "";
		}
		
		$city = $_POST['ec_account_shipping_information_city'];
		if( isset( $_POST['ec_account_shipping_information_state_' . $country] ) ){
			$state = $_POST['ec_account_shipping_information_state_' . $country];
		}else{
			$state = $_POST['ec_account_shipping_information_state'];
		}
		
		$zip = $_POST['ec_account_shipping_information_zip'];
		$phone = $_POST['ec_account_shipping_information_phone'];
		
		if( $first_name == $this->user->shipping->first_name && 
			$last_name == $this->user->shipping->last_name && 
			$company_name == $this->user->shipping->company_name && 
			$address == $this->user->shipping->address_line_1 && 
			$address2 == $this->user->shipping->address_line_2 && 
			$city == $this->user->shipping->city && 
			$state == $this->user->shipping->state && 
			$zip == $this->user->shipping->zip && 
			$country == $this->user->shipping->country &&
			$phone == $this->user->shipping->phone ){
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard&account_success=shipping_information_updated" );
				
		}else{
		
			$address_id = $this->user->shipping_id;
			if( $address_id )
				$success = $this->mysqli->update_user_address( $address_id, $first_name, $last_name, $address, $address2, $city, $state, $zip, $country, $phone, $company_name );
			else{
				$success = $this->mysqli->insert_user_address( $first_name, $last_name, $address, $address2, $city, $state, $zip, $country, $phone, $this->user_email, $this->user_password, "shipping", $company_name );
			}
			
			if( $success >= 0 )
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=dashboard&account_success=shipping_information_updated" );
			else
				header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=shipping_information&account_error=shipping_information_error" );
			
		}
	}
	
	private function process_logout( ){
		unset( $_SESSION['ec_user_id'] );
		unset( $_SESSION['ec_email'] );
		unset( $_SESSION['ec_username'] );
		unset( $_SESSION['ec_first_name'] );
		unset( $_SESSION['ec_last_name'] );
		unset( $_SESSION['ec_password'] );
		
		unset( $_SESSION['ec_billing_first_name'] );
		unset( $_SESSION['ec_billing_last_name'] );
		unset( $_SESSION['ec_billing_company_name'] );
		unset( $_SESSION['ec_billing_address'] );
		unset( $_SESSION['ec_billing_address2'] );
		unset( $_SESSION['ec_billing_city'] );
		unset( $_SESSION['ec_billing_state'] );
		unset( $_SESSION['ec_billing_zip'] );
		unset( $_SESSION['ec_billing_country'] );
		unset( $_SESSION['ec_billing_phone'] );
		
		unset( $_SESSION['ec_shipping_first_name'] );
		unset( $_SESSION['ec_shipping_last_name'] );
		unset( $_SESSION['ec_shipping_company_name'] );
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
			unset( $_SESSION['ec_email'] );
			unset( $_SESSION['ec_password'] );
		}
		
		unset( $_SESSION['ec_create_account'] );
		unset( $_SESSION['ec_couponcode'] );
		unset( $_SESSION['ec_giftcard'] );
	
		header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=login" );
	}
	
	private function process_update_subscription( ){
		
		global $wpdb;
		$products = $this->mysqli->get_product_list( $wpdb->prepare( " WHERE product.product_id = %d", $_POST['ec_selected_plan'] ), "", "", "" );
		
		// Check that a product was found
		if( count( $products ) > 0 ){
			
			// Setup Re-usable vars
			$product = new ec_product( $products[0] );
			$payment_method = get_option( 'ec_option_payment_process_method' );
			$success = false;
			$plan_added = $product->stripe_plan_added;
			
			// Check if we need to add the plan to Stripe
			if( $payment_method == "stripe" ){
				$stripe = new ec_stripe( );
				if( !$product->stripe_plan_added ){
					$plan_added = $stripe->insert_plan( $product );
					$this->mysqli->update_product_stripe_added( $product->product_id );
				}
				
				if( $plan_added ){
					$success = $stripe->update_subscription( $product, $this->user, $card, $_POST['stripe_subscription_id'] );
				}else{
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $_POST['subscription_id'] . "&account_error=subscription_update_failed&errcode=01" );
				}
			}
						
			
			//Upgrade and billing adjustment
			if( isset( $_POST['ec_card_number'] ) && $_POST['ec_card_number'] != "" ){
				$first_name = $_POST['ec_account_billing_information_first_name'];
				$last_name = $_POST['ec_account_billing_information_last_name'];
				$address = $_POST['ec_account_billing_information_address'];
				$city = $_POST['ec_account_billing_information_city'];
				$state = $_POST['ec_account_billing_information_state'];
				$zip = $_POST['ec_account_billing_information_zip'];
				$country = $_POST['ec_account_billing_information_country'];
				$phone = $_POST['ec_account_billing_information_phone'];
				
				$card_type = $this->get_payment_type( $this->sanatize_card_number( $_POST['ec_card_number'] ) );
				$card_holder_name = $_POST['ec_account_billing_information_first_name'] . " " . $_POST['ec_account_billing_information_last_name'];
				$card_number = $_POST['ec_card_number'];
				$exp_month = $_POST['ec_expiration_month'];
				$exp_year = $_POST['ec_expiration_year'];
				$security_code = $_POST['ec_security_code'];
				
				$address_id = $this->user->billing_id;
				$this->mysqli->update_user_address( $address_id, $first_name, $last_name, $address, "", $city, $state, $zip, $country, $phone );
				$this->user->setup_billing_info_data( $first_name, $last_name, $address, "", $city, $state, $country, $zip, $phone );
				$card = new ec_credit_card( $card_type, $card_holder_name, $card_number, $exp_month, $exp_year, $security_code );
				
				if( $payment_method == "stripe" ){
					$stripe = new ec_stripe( );
					$success = $stripe->update_subscription( $product, $this->user, $card, $_POST['stripe_subscription_id'] );
				}
					
				// Update our DB if the subscription was successfully updated
				if( $success ){
					$this->mysqli->update_subscription( $_POST['subscription_id'], $this->user, $product, $card );
					$this->mysqli->update_user_default_card( $this->user, $card );
				}
				
				if( $success ){
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $_POST['subscription_id'] . "&account_success=subscription_updated" );
				}else{
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $_POST['subscription_id'] . "&account_error=subscription_update_failed&errcode=02" );
				}
				
			// Only an upgrade, no change to billing	
			}else{
				
				if( $payment_method == "stripe" ){
					$stripe = new ec_stripe( );
					$success = $stripe->update_subscription( $product, $this->user, NULL, $_POST['stripe_subscription_id'] );
				}
				
				if( $success ){
					$this->mysqli->upgrade_subscription( $_POST['subscription_id'], $product );
				}
				
				if( $success ){
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $_POST['subscription_id'] . "&account_success=subscription_updated" );
				}else{
					header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $_POST['subscription_id'] . "&account_error=subscription_update_failed&errcode=03" );
				}

			}// End Update of subscription
			
		}else{ // No product has been found error
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $_POST['subscription_id'] . "&account_error=subscription_update_failed&errcode=04" );
			
		}
		
	}// End process update subscription
	
	private function process_cancel_subscription( ){
		$subscription_id = $_POST['ec_account_subscription_id'];
		$subscription_row = $this->mysqli->get_subscription_row( $subscription_id );
		$stripe = new ec_stripe( );
		$cancel_success = $stripe->cancel_subscription( $this->user, $subscription_row->stripe_subscription_id );
		if( $cancel_success ){
			$this->mysqli->cancel_subscription( $subscription_id );
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscriptions&account_success=subscription_canceled" );
		}else{
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=subscription_details&subscription_id=" . $subscription_id . "&account_error=subscription_cancel_failed" );
		}
	}
	
	private function process_order_create_account( ){
		$order_id = $_POST['order_id'];
		$email = $_POST['email_address'];
		$password = $_POST['ec_password'];
		
		$order_row = $this->mysqli->get_order_row( $order_id, "guest", "guest" );
		
		if( $this->mysqli->does_user_exist( $email ) ){
			header( "location: " . $this->cart_page . $this->permalink_divider . "ec_page=checkout_success&order_id=" . $order_id . "&ec_cart_error=email_exists" );
		}else if( $order_row->user_id == 0 ){
			$billing_id = $this->mysqli->insert_address( $order_row->billing_first_name, $order_row->billing_last_name, $order_row->billing_address_line_1, $order_row->billing_address_line_2, $order_row->billing_city, $order_row->billing_state, $order_row->billing_zip, $order_row->billing_country, $order_row->billing_phone );
			$shipping_id = $this->mysqli->insert_address( $order_row->shipping_first_name, $order_row->shipping_last_name, $order_row->shipping_address_line_1, $order_row->shipping_address_line_2, $order_row->shipping_city, $order_row->shipping_state, $order_row->shipping_zip, $order_row->shipping_country, $order_row->shipping_phone );
			
			$user_id = $this->mysqli->insert_user( $email, $password, $order_row->billing_first_name, $order_row->billing_last_name, $billing_id, $shipping_id, "shopper", 0 );
			$this->mysqli->update_order_user( $user_id, $order_id );
			
			$_SESSION['ec_user_id'] = $user_id;
			$_SESSION['ec_email'] = $email;
			$_SESSION['ec_username'] = $order_row->billing_first_name . " " . $order_row->billing_last_name;
			$_SESSION['ec_first_name'] = $order_row->billing_first_name;
			$_SESSION['ec_last_name'] = $order_row->billing_last_name;
			$_SESSION['ec_password'] = $password;
			
			header( "location: " . $this->account_page . $this->permalink_divider . "ec_page=order_details&order_id=" . $order_id . "&account_success=cart_account_created" );
		}
	}
	
	/* END FORM ACTION FUNCTIONS */
	
	private function send_new_password_email( $email, $new_password ){
		
		$user = $this->mysqli->get_user( $email, md5( $new_password ) );
		
		$email_logo_url = get_option( 'ec_option_email_logo' );
	 	
		// Get receipt
		ob_start();
        if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_retrieve_password_email.php' ) )	
			include( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_account_retrieve_password_email.php' );	
		else
			include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . '/design/layout/' . get_option( 'ec_option_latest_layout' ) . '/ec_account_retrieve_password_email.php' );
		$message = ob_get_contents();
		ob_end_clean();
		
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=utf-8";
		$headers[] = "From: " . get_option( 'ec_option_password_from_email' );
		$headers[] = "Reply-To: " . get_option( 'ec_option_password_from_email' );
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		if( get_option( 'ec_option_use_wp_mail' ) )
			wp_mail( $email, $GLOBALS['language']->get_text( "account_forgot_password_email", "account_forgot_password_email_title" ), $message, implode("\r\n", $headers));
		else
			mail( $email, $GLOBALS['language']->get_text( "account_forgot_password_email", "account_forgot_password_email_title" ), $message, implode("\r\n", $headers));
	}
	
	private function get_random_password( ){
		$rand_chars = array( "A", "B", "C", "D", "E", "F", "G", "H", "I", "J" );
		$rand_password = $rand_chars[ rand( 0, 9 ) ] . $rand_chars[ rand( 0, 9 ) ] . $rand_chars[ rand( 0, 9 ) ] . $rand_chars[ rand( 0, 9 ) ] . rand( 0, 9 ) . rand( 0, 9 ) . rand( 0, 9 ) . rand( 0, 9 ) . rand( 0, 9 );
		return $rand_password;
	}
	
	public function send_validation_email( $email ){
	 	$key = md5( $email . "ecsalt" );
		
		// Get receipt
		$message = $GLOBALS['language']->get_text( "account_validation_email", "account_validation_email_message" ) . "\r\n";
		$message .= "<a href=\"" . $this->account_page . $this->permalink_divider . "ec_page=activate_account&email=" . $email . "&key=" . $key . "\" target=\"_blank\">" . $GLOBALS['language']->get_text( "account_validation_email", "account_validation_email_link" ) . "</a>";
		
		$headers   = array();
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=utf-8";
		$headers[] = "From: " . get_option( 'ec_option_password_from_email' );
		$headers[] = "Reply-To: " . get_option( 'ec_option_password_from_email' );
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		if( get_option( 'ec_option_use_wp_mail' ) )
			wp_mail( $email, $GLOBALS['language']->get_text( "account_validation_email", "account_validation_email_title" ), $message, implode("\r\n", $headers));
		else
			mail( $email, $GLOBALS['language']->get_text( "account_validation_email", "account_validation_email_title" ), $message, implode("\r\n", $headers));
	}
	
	public function ec_display_payment_method_input( $select_one_text ){
		echo "<select name=\"ec_cart_payment_type\" id=\"ec_cart_payment_type\" class=\"ec_cart_payment_information_input_select\">";
		
		echo "<option value=\"0\">" . $select_one_text . "</option>";
		
		if( get_option('ec_option_use_visa') )
		echo "<option value=\"visa\">Visa</option>";
		
		if( get_option('ec_option_use_delta') )
		echo "<option value=\"delta\">Visa Debit/Delta</option>";
		
		if( get_option('ec_option_use_uke') )
		echo "<option value=\"uke\">Visa Electron</option>";
		
		if( get_option('ec_option_use_discover') )
		echo "<option value=\"discover\">Discover</option>";
		
		if( get_option('ec_option_use_mastercard') )
		echo "<option value=\"mastercard\">Mastercard</option>";
		
		if( get_option('ec_option_use_mcdebit') )
		echo "<option value=\"mcdebit\">Debit Mastercard</option>";
		
		if( get_option('ec_option_use_amex') )
		echo "<option value=\"amex\">American Express</option>";
		
		if( get_option('ec_option_use_jcb') )
		echo "<option value=\"jcb\">JCB</option>";
		
		if( get_option('ec_option_use_diners') )
		echo "<option value=\"diners\">Diners</option>";
		
		if( get_option('ec_option_use_laser') )
		echo "<option value=\"laser\">Laser</option>";
		
		if( get_option('ec_option_use_maestro') )
		echo "<option value=\"maestro\">Maestro</option>";
		
		echo "</select>";
	}
	
	public function ec_display_card_holder_name_input(){
		echo "<input type=\"text\" name=\"ec_card_holder_name\" id=\"ec_card_holder_name\" class=\"ec_cart_payment_information_input_text\" value=\"\" />";
	}
	
	public function ec_display_card_number_input(){
		echo "<input type=\"text\" name=\"ec_card_number\" id=\"ec_card_number\" class=\"ec_cart_payment_information_input_text\" value=\"\" />";
	}
	
	public function ec_display_card_expiration_month_input( $select_text ){
		echo "<select name=\"ec_expiration_month\" id=\"ec_expiration_month\" class=\"ec_cart_payment_information_input_select\">";
		echo "<option value=\"0\">" . $select_text . "</option>";
		for( $i=1; $i<=12; $i++ ){
			echo "<option value=\"";
			if( $i<10 )										$month = "0" . $i;
			else											$month = $i;
			echo $month . "\">" . $month . "</option>";
		}
		echo "</select>";
	}
	
	public function ec_display_card_expiration_year_input( $select_text ){
		echo "<select name=\"ec_expiration_year\" id=\"ec_expiration_year\" class=\"ec_cart_payment_information_input_select\">";
		echo "<option value=\"0\">" . $select_text . "</option>";
		for( $i=date( 'Y' ); $i < date( 'Y' ) + 15; $i++ ){
			echo "<option value=\"" . $i . "\">" . $i . "</option>";	
		}
		echo "</select>";
	}
	
	public function ec_display_card_security_code_input(){
		echo "<input type=\"text\" name=\"ec_security_code\" id=\"ec_security_code\" class=\"ec_cart_payment_information_input_select\" value=\"\" />";
	}
	
	public function display_subscription_update_form_start( ){
		echo "<form action=\"" . $this->account_page . "\" method=\"POST\">";
	}
	
	public function display_subscription_update_form_end( ){
		echo "<input type=\"hidden\" name=\"stripe_subscription_id\" value=\"" . $this->subscription->get_stripe_id( ) . "\" />";
		echo "<input type=\"hidden\" name=\"subscription_id\" value=\"" . $_GET['subscription_id'] . "\" />";
		echo "<input type=\"hidden\" name=\"ec_account_form_action\" value=\"update_subscription\" />";
		echo "</form>";
	}
	
	public function ec_account_display_credit_card_images(){
		//display credit card icons
		if( get_option('ec_option_use_visa') || get_option('ec_option_use_delta') || get_option('ec_option_use_uke') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/visa.png") . "\" alt=\"Visa\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_visa\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/visa_inactive.png") . "\" alt=\"Visa\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_visa_inactive\" />";
		
		/*
		if( get_option('ec_option_use_delta') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/visadebit.png") . "\" alt=\"Visa Debit\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_delta\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/visadebit_inactive.png") . "\" alt=\"Visa Debit\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_delta_inactive\" />";
			
		if( get_option('ec_option_use_uke') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/visaelectron.png") . "\" alt=\"Visa Electron\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_uke\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/visaelectron_inactive.png") . "\" alt=\"Visa Electron\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_uke_inactive\" />";
		*/
		
		if( get_option('ec_option_use_discover') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/discover.png") . "\" alt=\"Discover\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_discover\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/discover_inactive.png") . "\" alt=\"Discover\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_discover_inactive\" />";
		
		if( get_option('ec_option_use_mastercard') || get_option('ec_option_use_mcdebit') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/mastercard.png") . "\" alt=\"Mastercard\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_mastercard\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/mastercard_inactive.png") . "\" alt=\"Mastercard\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_mastercard_inactive\" />";
		
		if( get_option('ec_option_use_amex') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/american_express.png") . "\" alt=\"AMEX\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_amex\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/american_express_inactive.png") . "\" alt=\"AMEX\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_amex_inactive\" />";
		
		if( get_option('ec_option_use_jcb') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/jcb.png") . "\" alt=\"JCB\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_jcb\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/jcb_inactive.png") . "\" alt=\"JCB\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_jcb_inactive\" />";
		
		if( get_option('ec_option_use_diners') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/diners.png") . "\" alt=\"Diners\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_diners\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/diners_inactive.png") . "\" alt=\"Diners\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_diners_inactive\" />";
		
		/*
		if( get_option('ec_option_use_laser') )
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/laser.png") . "\" alt=\"Laser\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_laser\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/laser_inactive.png") . "\" alt=\"Laser\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_laser_inactive\" />";
		*/
		
		if( get_option('ec_option_use_maestro') || get_option('ec_option_use_laser'))
			echo "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/maestro.png") . "\" alt=\"Maestro\" class=\"ec_cart_payment_information_credit_card_active\" id=\"ec_cart_payment_credit_card_icon_maestro\" />" . "<img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_payment_information/maestro_inactive.png") . "\" alt=\"Maestro\" class=\"ec_cart_payment_information_credit_card_inactive\" id=\"ec_cart_payment_credit_card_icon_maestro_inactive\" />";
		
		
	}
	
	public function ec_account_display_card_holder_name_hidden_input(){
		echo "<input type=\"hidden\" name=\"ec_card_holder_name\" id=\"ec_card_holder_name\" class=\"ec_cart_payment_information_input_text\" value=\"" . $this->user->billing->first_name . " " . $this->user->billing->last_name . "\" />";
	}
	
	private function sanatize_card_number( $card_number ){
		
		return preg_replace( "/[^0-9]/", "", $card_number );
	
	}
	
	private function get_payment_type( $card_number ){
		
		if (ereg("^5[1-5][0-9]{14}$", $card_number))
                return "mastercard";
 
        else if (ereg("^4[0-9]{12}([0-9]{3})?$", $card_number))
                return "visa";
 
        else if (ereg("^3[47][0-9]{13}$", $card_number))
                return "amex";
 
        else if (ereg("^3(0[0-5]|[68][0-9])[0-9]{11}$", $card_number))
                return "diners";
 
        else if (ereg("^6011[0-9]{12}$", $card_number))
                return "discover";
 
        else if (ereg("^(3[0-9]{4}|2131|1800)[0-9]{11}$", $card_number))
                return "jcb";
				
		else
				return "Credit Card";
		
	}
}

?>