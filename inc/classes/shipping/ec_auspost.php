<?php
	
class ec_auspost{
	private $auspost_api_key; 									// Your Australian Post API Key
	private $auspost_ship_from_zip; 							// Your Australian Post ship from zip code
	
	private $domestic_shipper_url;								// String
	private $international_shipper_url;							// String
	
	private $domestic_getall_shipper_url;						// String
	private $international_getall_shipper_url;					// String
	
	function __construct( $ec_setting ){
		$this->auspost_api_key = $ec_setting->get_auspost_api_key( );
		$this->auspost_ship_from_zip = $ec_setting->get_auspost_ship_from_zip( );
		
		$this->domestic_shipper_url = "https://auspost.com.au/api/postage/parcel/domestic/calculate";
		$this->international_shipper_url = "https://auspost.com.au/api/postage/parcel/international/calculate";
		
		$this->domestic_getall_shipper_url = "https://auspost.com.au/api/postage/parcel/domestic/service.json";
		$this->international_getall_shipper_url = "https://auspost.com.au/api/postage/parcel/international/service.json";
	}
	
	public function get_rate( $ship_code, $destination_zip, $destination_country, $weight ){
		if( $weight == 0 )
			return "0.00";
		
		if( !$destination_country )
			$destination_country = "AU";
			
		if( $destination_country == "AU" )
			$shipper_url = $this->domestic_shipper_url;
		else
			$shipper_url = $this->international_shipper_url;
			
		if( !$destination_zip || $destination_zip == "" )
			$destination_zip = $this->auspost_ship_from_zip;
		
		$shipper_url .= "?";
		if( $destination_country == "AU" )
			$shipper_url .= "from_postcode=" . $destination_zip . "&to_postcode=" . $this->auspost_ship_from_zip . "&length=10&width=10&height=10";
		else
			$shipper_url .= "country_code=" . $destination_country;
		
		$shipper_url .= "&service_code=" . $ship_code . "&weight=" . $weight;
		
		$request = new WP_Http;
		$response = $request->request( $shipper_url, array( 'method' => 'GET', 'headers' => "AUTH-KEY:" . $this->auspost_api_key ) );
		
		if( is_wp_error( $response ) ){
			$error_message = $response->get_error_message();
			error_log( "error in australian post get rate, " . $error_message );
			return false;
		}else
			return $this->process_response( $response['body'] );
		
	}
	
	public function get_all_rates( $destination_zip, $destination_country, $weight, $length = 10, $width = 10, $height = 10 ){
		if( $weight == 0 )
			return "0.00";
		
		if( !$destination_country )
			$destination_country = "AU";
			
		if( $destination_country == "AU" )
			$shipper_url = $this->domestic_getall_shipper_url;
		else
			$shipper_url = $this->international_getall_shipper_url;
			
		if( !$destination_zip || $destination_zip == "" )
			$destination_zip = $this->auspost_ship_from_zip;
		
		$shipper_url .= "?";
		if( $destination_country == "AU" )
			$shipper_url .= "from_postcode=" . $destination_zip . "&to_postcode=" . $this->auspost_ship_from_zip . "&length=" . $length . "&width=" . $width . "&height=" . $height;
		else
			$shipper_url .= "country_code=" . $destination_country;
		
		$shipper_url .= "&weight=" . $weight;
		
		$request = new WP_Http;
		$response = $request->request( $shipper_url, array( 'method' => 'GET', 'headers' => "AUTH-KEY:" . $this->auspost_api_key ) );
		
		if( is_wp_error( $response ) ){
			$error_message = $response->get_error_message();
			error_log( "error in australian post get rate, " . $error_message );
			return false;
		}else{
			return $this->process_all_rates_response( $response['body'] );
		}
	}
	
	public function get_rate_test( $ship_code, $destination_zip, $destination_country, $weight ){
		if( $weight == 0 )
			return "0.00";
		
		if( !$destination_country )
			$destination_country = "AU";
			
		if( $destination_country == "AU" )
			$shipper_url = $this->domestic_shipper_url;
		else
			$shipper_url = $this->international_shipper_url;
		
		$shipper_url .= "?";
		if( $destination_country == "AU" )
			$shipper_url .= "from_postcode=" . $destination_zip . "&to_postcode=" . $this->auspost_ship_from_zip . "&length=10&width=10&height=10";
		else
			$shipper_url .= "country_code=" . $destination_country;
		
		$shipper_url .= "&service_code=" . $ship_code . "&weight=" . $weight;
		
		$request = new WP_Http;
		$response = $request->request( $shipper_url, array( 'method' => 'GET', 'headers' => "AUTH-KEY:" . $this->auspost_api_key ) );
		
		if( is_wp_error( $response ) ){
			$error_message = $response->get_error_message();
			error_log( "error in australian post get rate, " . $error_message );
			return false;
		}else
			return $response['body'];
		
	}
	
	public function get_domestic_list( $zipcode, $length, $height, $width, $weight ){
		$shipper_url = "https://auspost.com.au/api/postage/parcel/domestic/service.json?from_postcode=" . $this->auspost_ship_from_zip . "&to_postcode=" . $zipcode . "&length=" . $length. "&height=" . $height . "&width=" . $width . "&weight=" . $weight;
		$request = new WP_Http;
		$response = $request->request( $shipper_url, array( 'method' => 'GET', 'headers' => "AUTH-KEY:" . $this->auspost_api_key ) );
		$xml = json_decode( $response['body'] );
		$rates = array( );
		$rate_list = $xml->services->service;
		for( $i=0; $i<count( $rate_list ); $i++ ){
			$rates[$i] = array( "code" => $rate_list[$i]->code,
								"name" => $rate_list[$i]->name,
								"price" => $rate_list[$i]->price,
								"max_extra_cover" => $rate_list[$i]->max_extra_cover );
		}
		return $rates;
	}
	
	public function get_international_list( $country, $weight ){
		$shipper_url = "https://auspost.com.au/api/postage/parcel/international/service.json?country_code=" . $country . "&weight=" . $weight;
		$request = new WP_Http;
		$response = $request->request( $shipper_url, array( 'method' => 'GET', 'headers' => "AUTH-KEY:" . $this->auspost_api_key ) );
		$xml = json_decode( $response['body'] );
		$rates = array( );
		$rate_list = $xml->services->service;
		for( $i=0; $i<count( $rate_list ); $i++ ){
			$rates[$i] = array( "code" => $rate_list[$i]->code,
								"name" => $rate_list[$i]->name,
								"price" => $rate_list[$i]->price,
								"max_extra_cover" => $rate_list[$i]->max_extra_cover );
		}
		return $rates;
	}
	
	private function process_all_rates_response( $result ){
		
		$rates = array( );
		$xml = json_decode( $result );
		if( isset( $xml->services ) && isset( $xml->services->service ) ){
			for( $i=0; $i<count( $xml->services->service ); $i++ ){
				$rates[] = array( 'rate_code' => $xml->services->service[$i]->code, 'rate' => $xml->services->service[$i]->price );
			}
		}
		return $rates;
	}
	
	private function process_response( $result ){
		
		$xml = json_decode( $result );
		if( isset( $xml->postage_result ) && isset( $xml->postage_result->total_cost ) )
			return $xml->postage_result->total_cost;
		else{
			error_log( "error in australian post get rate, response: " . $result );
			return "ERROR";
		}
	}
	
	public function validate_address( $desination_address, $destination_city, $destination_state, $destination_zip, $destination_country ){
		
		if( !$destination_country )
			$destination_country = "AU";
			
		if( $destination_country == "AU" )
			$shipper_url = $this->domestic_getall_shipper_url;
		else
			$shipper_url = $this->international_getall_shipper_url;
		
		$shipper_url .= "?";
		if( $destination_country == "AU" )
			$shipper_url .= "from_postcode=" . $destination_zip . "&to_postcode=" . $this->auspost_ship_from_zip . "&length=10&width=10&height=10";
		else
			$shipper_url .= "country_code=" . $destination_country;
		
		$shipper_url .= "&weight=2";
		
		$request = new WP_Http;
		$response = $request->request( $shipper_url, array( 'method' => 'GET', 'headers' => "AUTH-KEY:" . $this->auspost_api_key ) );
		
		
		if( is_wp_error( $response ) ){
			$error_message = $response->get_error_message();
			error_log( "error in australian post get rate, " . $error_message );
			return true;
		}else{
			$xml = json_decode( $response['body'] );
			
			if( isset( $xml->error ) )
				return false;
			else
				return true;
				
		}
		
	}
}
	
?>