<?php

class ec_tax{
	
	protected $mysqli;										// ec_db structure
	
	public $state_tax;										// FLOAT 15,3
	public $country_tax;									// FLOAT 15,3
	public $all_tax;										// FLOAT 15,3
	
	public $tax_total;										// FLOAT 15,3
	
	public $duty_total;										// FLOAT 15,3
	public $vat_total;										// FLOAT 15,3
	
	// State Tax
	public $state_tax_enabled;								// BOOL
	public $state_tax_match;								// BOOL
	public $state_tax_rate;									// FLOAT 11,2
	
	// Country Tax
	public $country_tax_enabled;							// BOOL
	public $country_tax_match;								// BOOL
	public $country_tax_rate;								// FLOAT 11,2
	
	// All Tax
	public $all_tax_enabled;								// BOOL
	public $all_tax_rate;									// FLOAT 11,2
	
	// Duty
	public $duty_enabled;									// BOOL
	public $duty_country_match;								// BOOL
	public $duty_rate;										// FLOAT 11,2
	
	// VAT
	public $vat_enabled;									// BOOL
	public $vat_country_match;								// BOOL
	public $vat_rate;										// FLOAT 11,2
	public $vat_added;										// BOOL
	public $vat_included;									// BOOL
	
	// List of Countries for VAT
	public $country_list;									// Array( 'name_cnt'=>'United States', 
															//		  'iso2_cnt'=>'US', 
															//		  'vat_rate_cnt'=>'20' )
	
	// Passed in Values
	private $cart_subtotal;		// Used for Duty			// FLOAT 7,2
	private $taxable_subtotal;	// Used for Tax				// FLOAT 7,2
	private $vatable_total;		// Used for VAT				// FLOAT 15,3
	
	private $shipping_state;								// VARCHAR 255
	private $shipping_country;								// VARCHAR 255
	
	private $taxfree;										// BOOLEAN
	
	function __construct( $cart_subtotal, $taxable_subtotal, $vatable_total, $shipping_state, $shipping_country, $taxfree = false ){
		
		// Initialize Structures and Lists
		$this->mysqli 							= 			new ec_db();
		$this->country_list						=			$this->mysqli->get_countries( );
		$taxrates								= 			$this->mysqli->get_taxrates( );
		
		// Save the Subtotals
		$this->cart_subtotal 					= 			$cart_subtotal;
		$this->taxable_subtotal 				= 			$taxable_subtotal;
		$this->vatable_total 					= 			$vatable_total;
		
		// Save the User Entered Data
		$this->shipping_state 					= 			strtoupper( $shipping_state );
		$this->shipping_country 				= 			strtoupper( $shipping_country );
		
		$this->taxfree							=			$taxfree;
		
		// Initialize the Values to Zero/False
		$this->initialize_tax_values( );
		
		// Setup the Values from DB
		$this->setup_tax_info( $taxrates );
		
		// Calculate Actual Values
		$this->calculate_taxes( );
	}
	
	private function initialize_tax_values( ){
		// State Tax
		$this->state_tax_enabled 		= false;
		$this->state_tax_match 			= false;
		$this->state_tax_rate			= 0;
		
		// Country Tax
		$this->country_tax_enabled		= false;
		$this->country_tax_match		= false;
		$this->country_tax_rate			= 0;
		
		// All Tax
		$this->all_tax_enabled			= false;
		$this->all_tax_rate				= 0;
		
		// Duty
		$this->duty_enabled				= false;
		$this->duty_country_match		= false;
		$this->duty_rate				= 0;
		
		// VAT
		$this->vat_enabled				= false;
		$this->vat_country_match		= false;
		$this->vat_rate					= 0;
	}
	
	private function setup_tax_info( $taxrates ){
		foreach( $taxrates as $taxrate ){
			if( !$this->state_tax_match && $taxrate->tax_by_state ){
				$this->state_tax_enabled = true;
				if(	$this->shipping_state == $taxrate->state_code ){
					$this->state_tax_match = true;
					$this->state_tax_rate = $taxrate->state_rate;
				}
			}else if( $taxrate->tax_by_country ){
				$this->country_tax_enabled = true;
				if( $this->shipping_country == $taxrate->country_code ){
					$this->country_tax_match = true;
					$this->country_tax_rate = $taxrate->country_rate;	
				}
			}else if( $taxrate->tax_by_all ){
				$this->all_tax_enabled = true;
				$this->all_tax_rate = $taxrate->all_rate;
			}else if( $taxrate->tax_by_duty ){
				$this->duty_enabled = true;
				if( $this->shipping_country != $taxrate->duty_exempt_country_code ){
					$this->duty_country_match = true;
					$this->duty_rate = $taxrate->duty_rate;
				}
			}else if( $taxrate->tax_by_vat ){
				$this->vat_enabled = true;
				$vat_row = $taxrate;
				$this->vat_added = $taxrate->vat_added;
				$this->vat_included = $taxrate->vat_included;
			}else if( $taxrate->tax_by_single_vat ){
				$this->vat_enabled = true;
				$this->vat_rate = $taxrate->vat_rate;
				$this->vat_added = $taxrate->vat_added;
				$this->vat_included = $taxrate->vat_included;
			}
		}
		
		if( $this->vat_enabled && $this->vat_rate <= 0 ){
			for( $i=0; $i<count($this->country_list); $i++){
				if( $this->shipping_country == $this->country_list[$i]->iso2_cnt ){
					$this->vat_country_match = true;
					$this->vat_rate = $this->country_list[$i]->vat_rate_cnt;
				}
			}
			if( !$this->vat_country_match ){
				$this->vat_rate = $vat_row->vat_rate;
			}
		}
	}
	
	private function calculate_taxes( ){
		$this->state_tax 						= 			0;
		$this->country_tax 						= 			0;
		$this->all_tax 							= 			0;
		$this->duty_total 						= 			0;
		$this->vat_total 						= 			0;
		
		if( !$this->taxfree ){
			// Calculate State Tax
			if( $this->state_tax_enabled && $this->state_tax_match )
				$this->state_tax = $this->taxable_subtotal * $this->state_tax_rate / 100;
			
			// Calculate Country Tax
			if( $this->country_tax_enabled && $this->country_tax_match )
				$this->country_tax = $this->taxable_subtotal * $this->country_tax_rate / 100;
			
			// Calculate All Tax
			if( $this->all_tax_enabled )
				$this->all_tax = $this->taxable_subtotal * $this->all_tax_rate / 100;	
			
			//Calculate Sales Tax Total
			$this->tax_total = $this->state_tax + $this->country_tax + $this->all_tax;
			
			// Calculate Duty
			if( $this->duty_enabled && $this->duty_country_match )
				$this->duty_total = $this->cart_subtotal * $this->duty_rate / 100;
			
			// Calculate VAT Values
			if( $this->vat_enabled ){
				$GLOBALS['ec_vat_rate'] = $this->vat_rate;
				if( $this->vat_included ){
					$this->vat_total = ( $this->vatable_total / ( ( $this->vat_rate / 100 ) + 1 ) ) * ( $this->vat_rate / 100 );
				}else{
					$this->vat_total = $this->vatable_total * $this->vat_rate / 100;
				}
			}
		}
	}
	
	public function is_tax_enabled( ){
		if( $this->state_tax_enabled || $this->country_tax_enabled || $this->all_tax_enabled )
			return true;
		else
			return false;
	}
	
	public function is_duty_enabled( ){
		if( $this->duty_enabled && $this->duty_country_match )
			return true;
		else
			return false;
	}
	
	public function is_vat_enabled( ){
		if( $this->vat_enabled )
			return true;
		else
			return false;
	}
	
}

?>