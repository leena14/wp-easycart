<?php

class ec_order_totals{
	
	public $sub_total;													// FLOAT 11,2
	public $tax_total;													// FLOAT 11,2
	public $handling_total;												// FLOAT 11,2
	public $shipping_total;												// FLOAT 11,2
	public $duty_total;													// FLOAT 11,2
	public $vat_total;													// FLOAT 11,2
	public $discount_total;												// FLOAT 11,2
	public $grand_total;												// FLOAT 11,2
	
	function __construct( $cart, $user, $shipping, $tax, $discount ){
		$this->sub_total = number_format( $cart->subtotal, 2, '.', '' );
		$this->handling_total = number_format( $cart->get_handling_total( ), 2, '.', '' );
		if( $shipping->subtotal <= 0 ){
			$this->shipping_total = number_format( 0, 2, '.', '' );
		}else{
			$shipping_price = doubleval( $shipping->get_shipping_price( ) ) + $this->handling_total;
			$this->shipping_total = number_format( $shipping_price, 2, '.', '' );
		}
		$this->tax_total = number_format( $tax->tax_total, 2, '.', '' );
		$this->duty_total = number_format( $tax->duty_total, 2, '.', '' );
		$this->vat_total = number_format( $tax->vat_total, 2, '.', '' );
		if( strtolower(substr( $discount->coupon_code, 0, 3 ) ) == "vat" ){
			// Found a likely VAT Free Coupon, do a check to make sure it is valid
			$mysqli = new ec_db( );
			$promocode_row = $mysqli->redeem_coupon_code( $discount->coupon_code );
			if( $promocode_row && $promocode_row->is_free_item_based ){
				$this->vat_total = number_format( 0, 2, '.', '' );
			}
		}
		$this->discount_total = number_format( $discount->discount_total, 2, '.', '' );
		$this->shipping_total = $this->shipping_total - $discount->shipping_discount;
		$this->grand_total = number_format( $this->get_grand_total( $tax ), 2, '.', '' );	
	}
	
	private function get_grand_total( $tax ){
		if( $tax->vat_included ){
			return $this->sub_total + $this->shipping_total + $this->tax_total + $this->duty_total - $this->discount_total;
		}else{
			return $this->sub_total + $this->shipping_total + $this->tax_total + $this->duty_total + $this->vat_total - $this->discount_total;
		}
	}
	
	public function get_grand_total_in_cents( ){
		return number_format( $this->grand_total * 100, 0, '', '' );	
	}
	
}

?>