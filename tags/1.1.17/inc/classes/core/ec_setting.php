<?php

class ec_setting{
	protected $mysqli;						// ec_db structure
	
	private $setting_row;					// ec_setting DB table row
	
	function __construct( ){
		$this->mysqli = new ec_db();
		$this->setting_row = $this->mysqli->get_settings( );
	}
	
	public function get_setting( $setting ){
		if( isset( $this->setting_row->{$setting} ) )
			return $this->setting_row->{$setting};	
		else
			return "";
	}
	
	public function get_shipping_method( ){
		if( isset( $this->setting_row->shipping_method ) )
			return $this->setting_row->shipping_method;
		else
			return "";
	}
	
	public function get_ups_access_license_number( ){
		if( isset( $this->setting_row->ups_access_license_number ) )
			return $this->setting_row->ups_access_license_number;
		else
			return "";
	}
	
	public function get_ups_user_id( ){
		if( isset( $this->setting_row->ups_user_id ) )
			return $this->setting_row->ups_user_id;
		else
			return "";
	}
	
	public function get_ups_password( ){
		if( isset( $this->setting_row->ups_password ) )
			return $this->setting_row->ups_password;
		else
			return "";
	}
	
	public function get_ups_ship_from_zip( ){
		if( isset( $this->setting_row->ups_ship_from_zip ) )
			return $this->setting_row->ups_ship_from_zip;
		else
			return "";
	}
	
	public function get_ups_shipper_number( ){
		if( isset( $this->setting_row->ups_shipper_number ) )
			return $this->setting_row->ups_shipper_number;
		else
			return "";
	}
	
	public function get_ups_country_code( ){
		if( isset( $this->setting_row->ups_country_code ) )
			return $this->setting_row->ups_country_code;
		else
			return "";
	}
	
	public function get_ups_weight_type( ){
		if( isset( $this->setting_row->ups_weight_type ) )
			return $this->setting_row->ups_weight_type;
		else
			return "";
	}
	
	public function get_usps_user_name( ){
		if( isset( $this->setting_row->usps_user_name ) )
			return $this->setting_row->usps_user_name;
		else
			return "";
	}

	public function get_usps_ship_from_zip( ){
		if( isset( $this->setting_row->usps_ship_from_zip ) )
			return $this->setting_row->usps_ship_from_zip;
		else
			return "";
	}

	public function get_fedex_key( ){
		if( isset( $this->setting_row->fedex_key ) )
			return $this->setting_row->fedex_key;
		else
			return "";
	}

	public function get_fedex_account_number( ){
		if( isset( $this->setting_row->fedex_account_number ) )
			return $this->setting_row->fedex_account_number;
		else
			return "";
	}

	public function get_fedex_meter_number( ){
		if( isset( $this->setting_row->fedex_meter_number ) )
			return $this->setting_row->fedex_meter_number;
		else
			return "";
	}

	public function get_fedex_password( ){
		if( isset( $this->setting_row->fedex_password ) )
			return $this->setting_row->fedex_password;
		else
			return "";
	}
	
	public function get_fedex_ship_from_zip( ){
		if( isset( $this->setting_row->fedex_ship_from_zip ) )
			return $this->setting_row->fedex_ship_from_zip;
		else
			return "";
	}

	public function get_fedex_weight_units( ){
		if( isset( $this->setting_row->fedex_weight_units ) )
			return $this->setting_row->fedex_weight_units;
		else
			return "";
	}

	public function get_fedex_country_code( ){
		if( isset( $this->setting_row->fedex_country_code ) )
			return $this->setting_row->fedex_country_code;
		else
			return "";
	}
	
}

?>