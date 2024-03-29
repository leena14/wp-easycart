<?php

class ec_cartitem{
	
	public $mysqli;													// ec_db class
	
	public $orderdetail_id = 0;										// INT
	public $cartitem_id;											// INT
	public $product_id;												// INT
	public $model_number;											// VARCHAR 255
	public $orderdetails_model_number;								// VARCHAR 255
	public $post_id;												// INT
	public $manufacturer_id;										// INT
	public $use_advanced_optionset;									// BOOL
	
	public $quantity;												// INT
	public $grid_quantity;											// INT
	public $weight;													// INT
	public $total_weight;											// FLOAT
	public $width;
	public $height;
	public $length;
	
	public $title;													// VARCHAR 255
	public $description;											// BLOB
	
	public $unit_price;												// FLOAT 15,3
	public $total_price;											// FLOAT 15,3
	public $prev_price;												// FLOAT 15,3
	public $handling_price;											// FLOAT 15,3
	public $discount_price;											// FLOAT 15,3
	public $pricetiers = array();									// Array of rows of ec_pricetier
	
	public $vat_enabled;											// FLAOT 15,3
	
	public $is_giftcard;											// BOOL
	public $is_download;											// BOOL
	public $is_donation;											// BOOL
	public $is_taxable;												// BOOL
	public $is_amazon_download;										// BOOL
	
	public $image1;													// VARCHAR 255
	public $image1_optionitem;										// VARCHAR 255
	
	public $optionitem1_name;										// VARCHAR 255
	public $optionitem2_name;										// VARCHAR 255
	public $optionitem3_name;										// VARCHAR 255
	public $optionitem4_name;										// VARCHAR 255
	public $optionitem5_name;										// VARCHAR 255
	
	public $optionitem1_label;										// VARCHAR 255
	public $optionitem2_label;										// VARCHAR 255
	public $optionitem3_label;										// VARCHAR 255
	public $optionitem4_label;										// VARCHAR 255
	public $optionitem5_label;										// VARCHAR 255
	
	public $optionitem1_price;										// FLOAT 7,2
	public $optionitem2_price;										// FLOAT 7,2
	public $optionitem3_price;										// FLOAT 7,2
	public $optionitem4_price;										// FLOAT 7,2
	public $optionitem5_price;										// FLOAT 7,2
	
	public $optionitem1_id;											// INT
	public $optionitem2_id;											// INT
	public $optionitem3_id;											// INT
	public $optionitem4_id;											// INT
	public $optionitem5_id;											// INT
	
	public $advanced_options;										// array
	
	public $custom_vars = array();									// array
	
	public $giftcard_id = 0;										// INT
	public $gift_card_message;										// TEXT
	public $gift_card_from_name;									// VARCHAR 255
	public $gift_card_to_name;										// VARCHAR 255
	public $gift_card_email;										// VARCHAR 255
		
	public $donation_price;											// FLOAT 9,2
	
	public $is_deconetwork;											// BOOL
	public $deconetwork_id;											// VARCHAR 64
	public $deconetwork_name;										// VARCHAR 512
	public $deconetwork_product_code;								// VARCHAR 64
	public $deconetwork_options;									// VARCHAR 512
	public $deconetwork_edit_link;									// VARCHAR 512
	public $deconetwork_color_code;									// VARCHAR 64
	public $deconetwork_product_id;									// VARCHAR 64
	public $deconetwork_image_link;									// VARCHAR 512
	public $deconetwork_discount;									// FLOAT 15,3
	public $deconetwork_tax;										// FLOAT 15,3
	public $deconetwork_total;										// FLOAT 15,3
	public $deconetwork_version;									// INT
	
	public $download_id = 0;										// INT
	public $download_file_name;										// VARCHAR 255
	public $amazon_key;												// VARCHAR 255
	public $use_optionitem_quantity_tracking;						// BOOL
	
	public $promotions;												// array of promtions
	
	public $store_page;												// VARCHAR
	public $cart_page;												// VARCHAR
	public $permalink_divider;										// CHAR
	
	function __construct( $cartitem_data ){
		$this->mysqli = new ec_db( );
		
		$this->cartitem_id = $cartitem_data->cartitem_id;
		$this->product_id = $cartitem_data->product_id;
		$this->model_number = $cartitem_data->model_number;
		$this->orderdetails_model_number = $cartitem_data->model_number;
		$this->post_id = $cartitem_data->post_id;
		$this->manufacturer_id = $cartitem_data->manufacturer_id;
		
		$this->quantity = $cartitem_data->quantity;
		$this->grid_quantity = $cartitem_data->grid_quantity;
		if( $this->grid_quantity > 0 )
			$this->quantity = $this->grid_quantity;
			
		$this->weight = $cartitem_data->weight;
		$this->width = $cartitem_data->width;
		$this->height = $cartitem_data->height;
		$this->length = $cartitem_data->length;
		
		$this->title = $GLOBALS['language']->convert_text( $cartitem_data->title );
		$this->description = $GLOBALS['language']->convert_text( $cartitem_data->description );
		
		$this->is_giftcard = $cartitem_data->is_giftcard;
		$this->is_download = $cartitem_data->is_download;
		$this->is_donation = $cartitem_data->is_donation;
		$this->is_taxable = $cartitem_data->is_taxable;
		$this->is_amazon_download = $cartitem_data->is_amazon_download;
		
		$this->image1 = $cartitem_data->image1;
		$this->image1_optionitem = $cartitem_data->optionitemimage_image1;
		
		if($cartitem_data->optionitem1_data){
			$option = explode("***", $cartitem_data->optionitem1_data);
			$this->optionitem1_name = $GLOBALS['language']->convert_text( $option[0] ); 
			$this->optionitem1_price = $option[1];
			$this->optionitem1_label = $GLOBALS['language']->convert_text( $option[2] );
			$this->optionitem1_id = $option[3];
			if( $option[4] != "" )
				$this->orderdetails_model_number = $this->orderdetails_model_number . '-' . $option[4];
		}else{
			$this->optionitem1_name = ""; $this->optionitem1_price = 0.00;
		}
		
		if($cartitem_data->optionitem2_data){
			$option = explode("***", $cartitem_data->optionitem2_data);
			$this->optionitem2_name = $GLOBALS['language']->convert_text( $option[0] ); 
			$this->optionitem2_price = $option[1];
			$this->optionitem2_label = $GLOBALS['language']->convert_text( $option[2] );
			$this->optionitem2_id = $option[3];
			if( $option[4] != "" )
				$this->orderdetails_model_number = $this->orderdetails_model_number . '-' . $option[4];
		}else{
			$this->optionitem2_name = ""; $this->optionitem2_price = 0.00;
		}
		
		if($cartitem_data->optionitem3_data){
			$option = explode("***", $cartitem_data->optionitem3_data);
			$this->optionitem3_name = $GLOBALS['language']->convert_text( $option[0] ); 
			$this->optionitem3_price = $option[1];
			$this->optionitem3_label = $GLOBALS['language']->convert_text( $option[2] );
			$this->optionitem3_id = $option[3];
			if( $option[4] != "" )
				$this->orderdetails_model_number = $this->orderdetails_model_number . '-' . $option[4];
		}else{
			$this->optionitem3_name = ""; $this->optionitem3_price = 0.00;
		}
		
		if($cartitem_data->optionitem4_data){
			$option = explode("***", $cartitem_data->optionitem4_data);
			$this->optionitem4_name = $GLOBALS['language']->convert_text( $option[0] );
			$this->optionitem4_price = $option[1];
			$this->optionitem4_label = $GLOBALS['language']->convert_text( $option[2] );
			$this->optionitem4_id = $option[3];
			if( $option[4] != "" )
				$this->orderdetails_model_number = $this->orderdetails_model_number . '-' . $option[4];
		}else{
			$this->optionitem4_name = ""; $this->optionitem4_price = 0.00;
		}
		
		if($cartitem_data->optionitem5_data){
			$option = explode("***", $cartitem_data->optionitem5_data);
			$this->optionitem5_name = $GLOBALS['language']->convert_text( $option[0] );
			$this->optionitem5_price = $option[1];
			$this->optionitem5_label = $GLOBALS['language']->convert_text( $option[2] );
			$this->optionitem5_id = $option[3];
			if( $option[4] != "" )
				$this->orderdetails_model_number = $this->orderdetails_model_number . '-' . $option[4];
		}else{
			$this->optionitem5_name = ""; $this->optionitem5_price = 0.00;
		}
		
		if( $cartitem_data->pricetier_data ){
			$pricetiers = explode( "---", $cartitem_data->pricetier_data );
			for( $i=0; $i<count( $pricetiers ); $i++ ){
				$pricetier = explode( "***", $pricetiers[$i] );
				$this->pricetiers[$i] = array( $pricetier[0], $pricetier[1] );
			}
			usort( $this->pricetiers, array( "ec_cartitem", "ec_sort_price_tier" ) );
		}
		
		$this->use_advanced_optionset = $cartitem_data->use_advanced_optionset;
		
		$this->gift_card_message = $cartitem_data->gift_card_message;
		$this->gift_card_from_name = $cartitem_data->gift_card_from_name;
		$this->gift_card_to_name = $cartitem_data->gift_card_to_name;
		$this->gift_card_email = $cartitem_data->gift_card_email;
		
		$this->download_file_name = $cartitem_data->download_file_name;
		$this->amazon_key = $cartitem_data->amazon_key;
		$this->maximum_downloads_allowed = $cartitem_data->maximum_downloads_allowed;
		$this->download_timelimit_seconds = $cartitem_data->download_timelimit_seconds;
		
		$this->use_optionitem_quantity_tracking = $cartitem_data->use_optionitem_quantity_tracking;
		
		$this->donation_price = $cartitem_data->donation_price;
		
		$this->is_deconetwork = $cartitem_data->is_deconetwork;
		$this->deconetwork_id = $cartitem_data->deconetwork_id;
		$this->deconetwork_name = $cartitem_data->deconetwork_name;
		$this->deconetwork_product_code = $cartitem_data->deconetwork_product_code;
		$this->deconetwork_options = $cartitem_data->deconetwork_options;
		$this->deconetwork_edit_link = $cartitem_data->deconetwork_edit_link;
		$this->deconetwork_color_code = $cartitem_data->deconetwork_color_code;
		$this->deconetwork_product_id = $cartitem_data->deconetwork_product_id;
		$this->deconetwork_image_link = $cartitem_data->deconetwork_image_link;
		$this->deconetwork_discount = $cartitem_data->deconetwork_discount;
		$this->deconetwork_tax = $cartitem_data->deconetwork_tax;
		$this->deconetwork_total = $cartitem_data->deconetwork_total;
		$this->deconetwork_version = $cartitem_data->deconetwork_version;
		
		if( isset( $GLOBALS['ec_hooks']['ec_extra_cartitem_vars'] ) ){
			for( $i=0; $i<count( $GLOBALS['ec_hooks']['ec_extra_cartitem_vars'] ); $i++ ){
				$arr = $GLOBALS['ec_hooks']['ec_extra_cartitem_vars'][$i][0]( array( ), array( ) );
				for( $j=0; $j<count( $arr ); $j++ ){
					$this->custom_vars[ $arr[$j] ] =  $cartitem_data->{$arr[$j]};
				}
			}
		}
		$options_price = 0;
		$options_price_onetime = 0;
		$options_weight = 0;
		$options_weight_onetime = 0;
		$grid_weight_change = 0;
		$grid_price_change = 0;
		
		$this->advanced_options = $this->mysqli->get_advanced_cart_options( $this->cartitem_id );
		
		if( $this->use_advanced_optionset ){
			foreach( $this->advanced_options as $advanced_option ){
				
				if( $advanced_option->optionitem_model_number != "" )
					$this->orderdetails_model_number = $this->orderdetails_model_number . '-' . $advanced_option->optionitem_model_number;
				
				if( $advanced_option->option_type == "grid" ){
					
					if( $advanced_option->optionitem_price != 0 ){
						$grid_price_change = $grid_price_change + ( $advanced_option->optionitem_price * $advanced_option->optionitem_value ); 
					}else if( $advanced_option->optionitem_price_onetime != 0 ){ 
						$grid_price_change = $grid_price_change + $advanced_option->optionitem_price_onetime; 
					}else if( $advanced_option->optionitem_price_override >= 0 ){
						$grid_price_change = $grid_price_change + ( ( $advanced_option->optionitem_price_override - $cartitem_data->price ) * $advanced_option->optionitem_value );
					}
					
					if( $advanced_option->optionitem_weight != 0 ){
						$grid_weight_change = $grid_weight_change + ( $advanced_option->optionitem_weight * $advanced_option->optionitem_value ); 
					}else if( $advanced_option->optionitem_weight_onetime != 0 ){ 
						$grid_weight_change = $grid_weight_change + $advanced_option->optionitem_weight_onetime; 
					}else if( $advanced_option->optionitem_weight_override >= 0 ){
						$grid_weight_change = $grid_weight_change + ( ( $advanced_option->optionitem_weight_override - $cartitem_data->weight ) * $advanced_option->optionitem_value );
					}
					
				}else{
					if( $advanced_option->optionitem_price != 0 ){
						$options_price = $options_price + $advanced_option->optionitem_price; 
					}else if( $advanced_option->optionitem_price_onetime != 0 ){ 
						$options_price_onetime = $options_price_onetime + $advanced_option->optionitem_price_onetime; 
					}else if( $advanced_option->optionitem_price_override >= 0 ){
						$cartitem_data->price = $advanced_option->optionitem_price_override;
					}
					
					if( $advanced_option->optionitem_weight != 0 ){
						$options_weight = $options_weight + $advanced_option->optionitem_weight; 
					}else if( $advanced_option->optionitem_weight_onetime != 0 ){ 
						$options_weight_onetime = $options_weight_onetime + $advanced_option->optionitem_weight_onetime; 
					}else if( $advanced_option->optionitem_weight_override >= 0 ){
						$this->weight = $advanced_option->optionitem_weight_override;
					}
				}
			}
			for( $i=0; $i<count( $this->advanced_options ); $i++ ){
				$this->advanced_options[$i]->option_name = $GLOBALS['language']->convert_text( $this->advanced_options[$i]->option_name );
				$this->advanced_options[$i]->optionitem_name = $GLOBALS['language']->convert_text( $this->advanced_options[$i]->optionitem_name );
				$this->advanced_options[$i]->optionitem_value = $GLOBALS['language']->convert_text( $this->advanced_options[$i]->optionitem_value );
			}
		}else{
			$options_price = $this->optionitem1_price + $this->optionitem2_price + $this->optionitem3_price + $this->optionitem4_price + $this->optionitem5_price;
		}
		
		// Update the weight from option item weight
		$this->weight = $this->weight + $options_weight + $grid_weight_change;
		
		// Look for role based pricing
		if( isset( $_SESSION['ec_email'] ) && isset( $_SESSION['ec_password'] ) ){
			$roleprice = $this->mysqli->get_roleprice( $_SESSION['ec_email'], $_SESSION['ec_password'], $this->product_id );
		}
		
		if( $this->is_donation ){
			$this->unit_price = $cartitem_data->donation_price + $options_price;
		}else if( isset( $roleprice ) ){
			$this->unit_price = $roleprice + $options_price;
		}else if( count( $this->pricetiers ) > 0 ){
			$this->unit_price = $cartitem_data->price + $options_price;
			for( $i=0; $i<count( $this->pricetiers ); $i++ ){
				if( $this->quantity >= $this->pricetiers[$i][1] ){
					$this->unit_price = $this->pricetiers[$i][0] + $options_price;	
				}
			}
		}else{
			$this->unit_price = $cartitem_data->price + $options_price;	
		}
		
		$this->total_price = ( $this->unit_price * $this->quantity ) + $options_price_onetime + $grid_price_change;
		$this->total_weight = ( $this->weight * $this->quantity ) + $options_weight_onetime;
		$this->handling_price = $cartitem_data->handling_price;
		
		if( $cartitem_data->vat_rate > 0 )
			$this->vat_enabled = true;
		else
			$this->vat_enabled = false;
		
		if( $this->is_deconetwork ){
			$this->unit_price = $this->deconetwork_total / $this->quantity;
			$this->total_price = $this->deconetwork_total;
			$this->discount_price = $this->deconetwork_discount;
		}
			
		$store_page_id = get_option('ec_option_storepage');
		$cart_page_id = get_option('ec_option_cartpage');
		
		if( function_exists( 'icl_object_id' ) ){
			$store_page_id = icl_object_id( $store_page_id, 'page', true, ICL_LANGUAGE_CODE );
			$cart_page_id = icl_object_id( $cart_page_id, 'page', true, ICL_LANGUAGE_CODE );
		}
		
		$this->store_page = get_permalink( $store_page_id );
		$this->cart_page = get_permalink( $cart_page_id );
		
		if( class_exists( "WordPressHTTPS" ) && isset( $_SERVER['HTTPS'] ) ){
			$https_class = new WordPressHTTPS( );
			$this->store_page = $https_class->makeUrlHttps( $this->store_page );
			$this->cart_page = $https_class->makeUrlHttps( $this->cart_page );
		}
		
		if( substr_count( $this->cart_page, '?' ) )					$this->permalink_divider = "&";
		else														$this->permalink_divider = "?";
	}
	
	public function display_cartitem_id(){
		echo $this->cartitem_id;
	}
	
	public function get_quantity(){
		return $this->quantity;
	}
	
	public function display_quantity(){
		echo $this->quantity;
	}
	
	public function get_item_unit_price(){
		return $this->unit_price;
	}
	
	public function get_discount_unit_price(){
		return $this->discount_price;
	}
	
	public function get_item_total(){
		return $this->total_price;
	}
	
	public function get_weight(){
		return $this->total_weight;
	}
	
	public function get_shippable_total(){
		if( !$this->is_giftcard && !$this->is_download && !$this->is_donation ){
			return $this->total_price;
		}else{
			return 0;
		}
	}
	
	public function display_image( $size ){
		
		if( $this->is_deconetwork ){
		
			echo "<a href=\"https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_edit_link . "\"><img src=\"https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_image_link . "?version=" . $this->deconetwork_version . "\" alt=\"" . $this->model_number . "\" /></a>";
		
		}else{
			
			echo "<a href=\"" . $this->ec_get_permalink( $this->post_id );
		
			if( substr_count( $this->ec_get_permalink( $this->post_id ), '?' ) ){
				$second_permalink_divider = "&";
			}else{
				$second_permalink_divider = "?";
			}
			
			if( $this->image1_optionitem ){
				if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $this->image1_optionitem ) )
					echo $second_permalink_divider . "optionitem_id=" . $this->optionitem1_id . "\"><img src=\"" . plugins_url( "wp-easycart-data/products/pics1/" . $this->image1_optionitem ) . "\" alt=\"" . $this->model_number . "\" />";
				else
					echo $second_permalink_divider . "optionitem_id=" . $this->optionitem1_id . "\"><img src=\"" . plugins_url( EC_PLUGIN_DIRECTORY . "/products/pics1/" . $this->image1_optionitem ) . "\" alt=\"" . $this->model_number . "\" />";
			}else{
				if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $this->image1 ) )
					echo "\"><img src=\"" . plugins_url( "wp-easycart-data/products/pics1/" . $this->image1 ) . "\" alt=\"" . $this->model_number . "\" />";
				
				else if( file_exists( WP_PLUGIN_DIR . '/' . EC_PLUGIN_DIRECTORY . "/products/pics1/" . $this->image1 ) )
					echo "\"><img src=\"" . plugins_url( EC_PLUGIN_DIRECTORY . "/products/pics1/" . $this->image1 ) . "\" alt=\"" . $this->model_number . "\" />";
				
				else if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/images/ec_image_not_found.jpg" ) )
					echo "\"><img src=\"" . plugins_url( "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/images/ec_image_not_found.jpg" ) . "\" alt=\"" . $this->model_number . "\" />";
				
				else if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_image_not_found.jpg" ) )
					echo "\"><img src=\"" . plugins_url( "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_image_not_found.jpg" ) . "\" alt=\"" . $this->model_number . "\" />";
				
				else
					echo "\"><img src=\"" . plugins_url( EC_PLUGIN_DIRECTORY . "/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_image_not_found.jpg" ) . "\" alt=\"" . $this->model_number . "\" />";
			}
			echo "</a>";
			
		}
		
	}
	
	public function get_image_url( ){
		
		if( $this->is_deconetwork ){
			return "https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_image_link . "?version=" . $this->deconetwork_version;
		
		}else if( $this->image1_optionitem && file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $this->image1_optionitem ) && !is_dir( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $this->image1_optionitem ) ){
			return plugins_url( "wp-easycart-data/products/pics1/" . $this->image1_optionitem );
		
		}else if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $this->image1 ) && !is_dir( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $this->image1 ) ){
			return plugins_url( "wp-easycart-data/products/pics1/" . $this->image1 );
		
		}else if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/images/ec_image_not_found.jpg" ) ){
			return plugins_url( "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/images/ec_image_not_found.jpg" );
		
		}else{
			return plugins_url( "/wp-easycart/design/theme/" . get_option( 'ec_option_latest_theme' ) . "/images/ec_image_not_found.jpg" );
		}
		
	}
	
	public function get_product_url( ){
		
		if( $this->is_deconetwork )
			return "https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_edit_link;
		else
			return $this->ec_get_permalink( $this->post_id );
		
	}
	
	public function display_title( ){
		
		if( $this->is_deconetwork )
			echo $this->deconetwork_name;
		else
			echo $this->title;
		
	}
	
	public function display_title_link( ){
		
		if( $this->is_deconetwork ){
			
			echo "<a href=\"https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_edit_link . "\">" . $this->deconetwork_name . "</a>";
			
		}else{
			
			echo "<a href=\"" . $this->ec_get_permalink( $this->post_id );
		
			if( substr_count( $this->ec_get_permalink( $this->post_id ), '?' ) ){
				$second_permalink_divider = "&";
			}else{
				$second_permalink_divider = "?";
			}
			
			if( $this->image1_optionitem )
				echo $second_permalink_divider . "optionitem_id=" . $this->optionitem1_id;
			
			echo "\">" . $this->title . "</a>";
			
		}
		
	}
	
	public function get_title_link( ){
		
		if( $this->is_deconetwork ){
			
			return "https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_edit_link;
			
		}else{
			
			$ret_string = $this->ec_get_permalink( $this->post_id );
		
			if( substr_count( $ret_string, '?' ) ){
				$second_permalink_divider = "&";
			}else{
				$second_permalink_divider = "?";
			}
			
			if( $this->image1_optionitem )
				$ret_string .= $second_permalink_divider . "optionitem_id=" . $this->optionitem1_id;
			
			return $ret_string;
			
		}
		
	}
	
	public function has_option1( ){
		
		if( ( $this->is_deconetwork && $this->deconetwork_options ) || $this->optionitem1_name )
			return true;
		else
			return false;
		
	}
	
	public function display_option1( ){
		
		if( $this->is_deconetwork ){
			
			echo str_replace( "<br/><br/>", "<br/>", $this->deconetwork_options );
			echo "<br/><a href=\"https://" . get_option( 'ec_option_deconetwork_url' ) . $this->deconetwork_edit_link . "\">Edit Design</a>";
			
		}else{
			
			if( $this->optionitem1_price == "0.00" &&  $this->optionitem1_name)
				echo $this->optionitem1_label . ": " . $this->optionitem1_name;
			else if( $this->optionitem1_name ){
				if( $this->optionitem1_price > 0.00 )
					echo $this->optionitem1_label . ": " . $this->optionitem1_name . " ( +" . $GLOBALS['currency']->get_currency_display( $this->optionitem1_price ) . " )";
				else
					echo $this->optionitem1_label . ": " . $this->optionitem1_name . " ( " . $GLOBALS['currency']->get_currency_display( $this->optionitem1_price ) . " )";
			}
			
		}
		
	}
	
	public function has_option2( ){
		if( $this->optionitem2_name )
			return true;
		else
			return false;
	}
	
	public function display_option2( ){
		if( $this->optionitem2_price == "0.00" &&  $this->optionitem2_name )
			echo $this->optionitem2_label . ": " . $this->optionitem2_name;
		else if( $this->optionitem2_name ){
			if( $this->optionitem2_price > 0.00 )
				echo $this->optionitem2_label . ": " . $this->optionitem2_name . " ( +" . $GLOBALS['currency']->get_currency_display( $this->optionitem2_price ) . " )";
			else
				echo $this->optionitem2_label . ": " . $this->optionitem2_name . " ( " . $GLOBALS['currency']->get_currency_display( $this->optionitem2_price ) . " )";
		}
	}
	
	public function has_option3( ){
		if( $this->optionitem3_name )
			return true;
		else
			return false;
	}
	
	public function display_option3( ){
		if( $this->optionitem3_price == "0.00" &&  $this->optionitem3_name )
			echo $this->optionitem3_label . ": " . $this->optionitem3_name;
		else if( $this->optionitem3_name ){
			if( $this->optionitem3_price > 0.00 )
				echo $this->optionitem3_label . ": " . $this->optionitem3_name . " ( +" . $GLOBALS['currency']->get_currency_display( $this->optionitem3_price ) . " )";
			else
				echo $this->optionitem3_label . ": " . $this->optionitem3_name . " ( " . $GLOBALS['currency']->get_currency_display( $this->optionitem3_price ) . " )";
		}
	}
	
	public function has_option4( ){
		if( $this->optionitem4_name )
			return true;
		else
			return false;
	}
	
	public function display_option4( ){
		if( $this->optionitem4_price == "0.00" &&  $this->optionitem4_name )
			echo $this->optionitem4_label . ": " . $this->optionitem4_name;
		else if( $this->optionitem4_name ){
			if( $this->optionitem4_price > 0.00 )
				echo $this->optionitem4_label . ": " . $this->optionitem4_name . " ( +" . $GLOBALS['currency']->get_currency_display( $this->optionitem4_price ) . " )";
			else
				echo $this->optionitem4_label . ": " . $this->optionitem4_name . " ( " . $GLOBALS['currency']->get_currency_display( $this->optionitem4_price ) . " )";
		}
	}
	
	public function has_option5( ){
		if( $this->optionitem5_name )
			return true;
		else
			return false;
	}
	
	public function display_option5( ){
		if( $this->optionitem5_price == "0.00" &&  $this->optionitem5_name )
			echo $this->optionitem5_label . ": " . $this->optionitem5_name;
		else if( $this->optionitem5_name ){
			if( $this->optionitem5_price > 0.00 )
				echo $this->optionitem5_label . ": " . $this->optionitem5_name . " ( +" . $GLOBALS['currency']->get_currency_display( $this->optionitem5_price ) . " )";
			else
				echo $this->optionitem5_label . ": " . $this->optionitem5_name . " ( " . $GLOBALS['currency']->get_currency_display( $this->optionitem5_price ) . " )";
		}
	}
	
	public function has_gift_card_message( ){
		if( $this->gift_card_message )
			return true;
		else
			return false;
	}
	
	public function display_gift_card_message( $message_text ){
		if( $this->gift_card_message )
			echo $message_text . $this->gift_card_message;
	}
	
	public function has_gift_card_from_name( ){
		if( $this->gift_card_from_name )
			return true;
		else
			return false;
	}
	
	public function display_gift_card_from_name( $from_text ){
		if( $this->gift_card_from_name )
			echo $from_text . $this->gift_card_from_name;
	}
	
	public function has_gift_card_to_name( ){
		if( $this->gift_card_to_name )
			return true;
		else
			return false;
	}
	
	public function display_gift_card_to_name( $to_text ){
		if( $this->gift_card_to_name )
			echo $to_text . $this->gift_card_to_name;
	}
	
	public function has_print_gift_card_link( ){
		if( $this->is_giftcard )
			return true;
		else
			return false;
	}
	
	public function has_download_link( ){
		if( $this->is_download )
			return true;
		else
			return false;
	}
	
	public function display_update_form_start( ){
		
		if( !$this->is_deconetwork ){
			
			if( isset( $_GET['ec_page'] ) )
				echo "<form action=\"" . $this->cart_page . $this->permalink_divider . "ec_page=" . $_GET['ec_page'] . "\" method=\"post\">";
			else
				echo "<form action=\"" . $this->cart_page . "\" method=\"post\">";
		
		}
	
	}
	
	public function display_update_form_end( ){
		
		if( !$this->is_deconetwork ){
			
			echo "<input type=\"hidden\" name=\"ec_update_cartitem_id\" id=\"ec_update_cartitem_id_" . $this->cartitem_id . "\" value=\"" . $this->cartitem_id . "\" />";
			echo "<input type=\"hidden\" name=\"ec_cart_form_action\" id=\"ec_cart_form_action\" value=\"ec_update_action\" />";
			echo "</form>";	
		
		}
		
	}
	
	public function display_quantity_box( ){
		
		if( $this->is_deconetwork ){
			
			echo $this->quantity;
			
		}else{
			
			if( $this->grid_quantity > 0 ){
				echo "<input type=\"hidden\" id=\"ec_cartitem_quantity_" . $this->cartitem_id . "\" name=\"ec_cartitem_quantity_" . $this->cartitem_id . "\" value=\"" . $this->quantity . "\" min=\"1\" />" . $this->grid_quantity;
			}else{
				echo "<input type=\"number\" id=\"ec_cartitem_quantity_" . $this->cartitem_id . "\" name=\"ec_cartitem_quantity_" . $this->cartitem_id . "\" value=\"" . $this->quantity . "\" min=\"1\" />";
			}
			
		}
		
	}
	
	public function display_update_button( $update_text ){
		
		if( !$this->is_deconetwork ){
			
			echo "<input type=\"submit\" id=\"update_" . $this->cartitem_id . "\" name=\"update_" . $this->cartitem_id . "\" value=\"" . $update_text . "\" onclick=\"ec_cart_item_update( '" . $this->cartitem_id . "' ); return false;\" />";
			
		}
		
	}
	
	public function display_delete_button( $remove_text ){
		
		if( isset( $_GET['ec_page'] ) )
			echo "<form action=\"" . $this->cart_page . $this->permalink_divider . "ec_page=" . $_GET['ec_page'] . "\" method=\"post\">";
		else
			echo "<form action=\"" . $this->cart_page . "\" method=\"post\">";
			
		echo "<input type=\"submit\" id=\"remove_" . $this->cartitem_id . "\" name=\"remove_" . $this->cartitem_id . "\" value=\"" . $remove_text . "\" onclick=\"ec_google_removeFromCart( '" . $this->model_number . "', '" . $this->title . "', document.getElementById( 'ec_cartitem_quantity_" . $this->cartitem_id . "' ), '" . number_format( $this->unit_price, 2, '.', '' ) . "' ); ec_cart_item_delete( '" . $this->cartitem_id . "' ); return false;\"/>";
		echo "<input type=\"hidden\" name=\"ec_cart_form_action\" id=\"ec_cart_form_action\" value=\"ec_delete_action\" />";
		echo "<input type=\"hidden\" name=\"ec_delete_cartitem_id\" id=\"ec_delete_cartitem_id_" . $this->cartitem_id . "\" value=\"" . $this->cartitem_id . "\" />";
		echo "</form>";
	
	}
	
	public function display_unit_price( ){
		
		if( $this->is_deconetwork ){
			
			echo "<span id=\"ec_cartitem_unit_price_" . $this->cartitem_id . "\">" . $GLOBALS['currency']->get_currency_display( $this->deconetwork_total / $this->quantity ) . "</span>";
			
		}else{
			
			echo "<span id=\"ec_cartitem_unit_price_" . $this->cartitem_id . "\">" . $GLOBALS['currency']->get_currency_display( $this->unit_price ) . "</span>";
		
			if( $this->prev_price )
				echo "<span id=\"ec_cartitem_prev_price_" . $this->cartitem_id . "\" class=\"ec_product_old_price\">" . $GLOBALS['currency']->get_currency_display( $this->prev_price ) . "</span>";
				
		}
		
	}
	
	public function get_unit_price( ){
		
		if( $this->is_deconetwork )
			return $GLOBALS['currency']->get_currency_display( $this->deconetwork_total / $this->quantity );
		else
			return $GLOBALS['currency']->get_currency_display( $this->unit_price );
		
	}
	
	public function display_item_total( ){
		
		if( $this->is_deconetwork ){
			
			echo "<span id=\"ec_cartitem_unit_price_" . $this->cartitem_id . "\">" . $GLOBALS['currency']->get_currency_display( $this->deconetwork_total ) . "</span>";
			
		}else{
		
			echo "<span id=\"ec_cartitem_total_" . $this->cartitem_id . "\">" . $GLOBALS['currency']->get_currency_display( $this->total_price ) . "</span>";
		
		}
		
	}
	
	public function get_total( ){
		
		if( $this->is_deconetwork )
			return $GLOBALS['currency']->get_currency_display( $this->deconetwork_total );
		else
			return $GLOBALS['currency']->get_currency_display( $this->total_price );
		
	}
	
	public function display_vat_rate( ){
		if( $this->vat_enabled )
			if( isset( $GLOBALS['ec_vat_rate'] ) )
				echo number_format( $GLOBALS['ec_vat_rate'], 0 );	
			else{
				$tax_struct = new ec_tax( 0,0,0,"","" );
				echo number_format( $tax_struct->vat_rate , 0 );
			}	
		else
			echo number_format( 0, 0 );	
	}
	
	public function display_item_loader( ){
		if( file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_page/loader.gif" ) )	
			echo "<div class=\"ec_cart_item_loader\" id=\"ec_cart_item_loader_" . $this->cartitem_id . "\"><img src=\"" . plugins_url( "wp-easycart-data/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_page/loader.gif" ) . "\" /></div>";	
		else
			echo "<div class=\"ec_cart_item_loader\" id=\"ec_cart_item_loader_" . $this->cartitem_id . "\"><img src=\"" . plugins_url( EC_PLUGIN_DIRECTORY . "/design/theme/" . get_option( 'ec_option_base_theme' ) . "/ec_cart_page/loader.gif" ) . "\" /></div>";
	}
	
	public function get_advanced_options( ){
		return $this->advanced_options;
	}
	
	private function ec_get_permalink( $postid ){
		
		if( !get_option( 'ec_option_use_old_linking_style' ) && $postid != "0" ){
			return get_permalink( $postid );
		}else{
			return $this->store_page . $this->permalink_divider . "model_number=" . $this->model_number;
		}
		
	}
	
	static function ec_sort_price_tier( $a, $b ){
		if( $a[1] > $b[1] ){
			return 1;
		}else{
			return -1;
		}
	}
}

?>