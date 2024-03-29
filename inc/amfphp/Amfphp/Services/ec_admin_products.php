<?php
/*
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//All Code and Design is copyrighted by Level Four Development, llc
//
//Level Four Development, LLC provides this code "as is" without warranty of any kind, either express or implied,     
//including but not limited to the implied warranties of merchantability and/or fitness for a particular purpose.         
//
//Only licnesed users may use this code and storfront for live purposes. All other use is prohibited and may be 
//subject to copyright violation laws. If you have any questions regarding proper use of this code, please
//contact Level Four Development, llc and EasyCart prior to use.
//
//All use of this storefront is subject to our terms of agreement found on Level Four Development, llc's  website.
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
*/ 
class ec_admin_products{
	
	private $db;
	
	function ec_admin_products( ){
		
		global $wpdb;
		$this->db = $wpdb;
		
		require_once( "../../classes/core/ec_db.php" );
		
		if( file_exists( "../../../../wp-easycart-quickbooks/QuickBooks.php" ) ){
			require_once( "../../../../wp-easycart-quickbooks/ec_quickbooks.php" );
			require_once( "../../../../wp-easycart-quickbooks/QuickBooks.php" );
		}

	}
	
	public function _getMethodRoles($methodName){
		if ($methodName == 'removeproductcategory') return array('admin');
		else if($methodName == 'createproductcategory') return array('admin');
		else if($methodName == 'getproductcategories') return array('admin');
		else if($methodName == 'deleteadvancedoption') return array('admin');
		else if($methodName == 'updatealldownloadcustomers') return array('admin');
		else if($methodName == 'updateallamazondownloadcustomers') return array('admin');
		else if($methodName == 'deletealladvancedoption') return array('admin');
		else if($methodName == 'addadvancedoption') return array('admin');
		else if($methodName == 'getadvancedproductoptions') return array('admin');
		else if($methodName == 'getfeaturedproducts') return array('admin');
		else if($methodName == 'getproductlist') return array('admin');
		else if($methodName == 'getproducts') return array('admin');
		else if($methodName == 'duplicateproduct') return array('admin');
		else if($methodName == 'deleteproduct') return array('admin');
		else if($methodName == 'updateproduct') return array('admin');
		else if($methodName == 'addproduct') return array('admin');
		else if($methodName == 'deleteimage') return array('admin');
		else if($methodName == 'deleteoptionitemimage') return array('admin');
		else if($methodName == 'deletefiledownload') return array('admin');
		else if($methodName == 'getawsfiles') return array('admin');
		else  return null;
	}
	
	function createproductcategory( $categoryname, $productid ){
		
		$sql = "INSERT INTO ec_category( category_name ) VALUES( %s )";
		$success = $this->db->query( $this->db->prepare( $sql, $categoryname ) );
		
		if( $success === FALSE ){
			return array( "error" );
		
		}else{
			
			$category_id = $this->db->insert_id;
			$post = array(	'post_content'	=> "[ec_store groupid=\"" . $category_id . "\"]",
							'post_status'	=> "publish",
							'post_title'	=> $GLOBALS['language']->convert_text( $categoryname ),
							'post_type'		=> "ec_store"
						  );
			$post_id = wp_insert_post( $post );
			
			$db = new ec_db( );
			$db->update_category_post_id( $category_id, $post_id );
			
			$results = $this->db->get_results( "SELECT SQL_CALC_FOUND_ROWS ec_category.* FROM ec_category" );
			$totalrows = $this->db->get_var( "SELECT FOUND_ROWS()" );
			
			if( $totalrows > 0 ){
				$results[0]->totalrow = $totalrows;
				return $results;
			}else{
				return array( "noresults" );
			}
		}
		
	}
	
	function getproductcategories( $product_id ){
		 
		$sql = "SELECT ec_category.category_id, ec_category.category_name, ec_categoryitem.product_id, ec_categoryitem.category_id FROM ec_categoryitem LEFT JOIN ec_category ON ( ec_categoryitem.category_id = ec_category.category_id ) WHERE ec_categoryitem.product_id = %d ORDER BY ec_category.category_name";
		$results = $this->db->get_results( $this->db->prepare( $sql, $product_id ) );
		
		if( count( $results ) > 0 ){
		  return $results;
		}else{
		  return array( "noresults" );
		}

	}
	
	function removeproductcategory( $category_id, $product_id ){
		
		$deletesql = "DELETE FROM ec_categoryitem WHERE ec_categoryitem.product_id = %d AND ec_categoryitem.category_id = %d";
		$success = $this->db->query( $this->db->prepare( $deletesql, $product_id, $category_id ) );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	
	function updatealldownloadcustomers( $productid, $newdownloadid, $olddownloadid ){
		
		/////////////////////////////////////////////////////
		//currently only changing customers who have matching file names, but can use product id if necessary to do all customers on this product.
		/////////////////////////////////////////////////////
		$sql = "UPDATE ec_download SET ec_download.download_file_name = %s, ec_download.is_amazon_download = 0, ec_download.amazon_key = '' WHERE ec_download.product_id = %d";
		$success = $this->db->query( $this->db->prepare( $sql, $newdownloadid, $productid ) );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	function updateallamazondownloadcustomers( $productid, $newamazonkey, $oldamazonkey ){
		
		/////////////////////////////////////////////////////
		//currently only changing customers who have matching file names, but can use product id if necessary to do all customers on this product.
		/////////////////////////////////////////////////////
		$sql = "UPDATE ec_download SET ec_download.download_file_name = '', ec_download.is_amazon_download = 1, ec_download.amazon_key = %s WHERE ec_download.product_id = %d";
		$success = $this->db->query( $this->db->prepare( $sql, $newamazonkey, $productid ) );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	
	function deletealladvancedoption( $product_id ){

		$sql = "DELETE FROM ec_option_to_product WHERE ec_option_to_product.product_id = %d";
		$success = $this->db->query( $this->db->prepare( $sql, $product_id ) );	  
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	
	function deleteadvancedoption( $optionlinkid, $product_id ){
		
		$sql = "DELETE FROM ec_option_to_product WHERE ec_option_to_product.option_to_product_id = %d";
		$success = $this->db->query( $this->db->prepare( $sql, $optionlinkid ) );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			$sql = "SELECT ec_option.option_name, ec_option.option_label, ec_option.option_type, ec_option.option_required, ec_option.option_error_text, ec_option.option_id, ec_product.model_number, ec_product.product_id, ec_option_to_product.product_id, ec_option_to_product.option_to_product_id, ec_option_to_product.option_id 
			  FROM ec_option_to_product
				INNER JOIN ec_product ON (ec_option_to_product.product_id = ec_product.product_id)
				INNER JOIN ec_option ON (ec_option_to_product.option_id = ec_option.option_id)
			  WHERE
				ec_option_to_product.product_id = %d
			  ORDER BY
				ec_option_to_product.option_to_product_id";
			$results = $this->db->get_results( $this->db->prepare( $sql, $product_id ) );
		
			if( count( $results ) > 0 ){
				return $results;
			}else{
				return array( "noresults" );
			}
		}
		
	}

	function addadvancedoption( $product_id, $optionid ){
		
		$sql = "INSERT INTO ec_option_to_product( option_id, product_id, role_label ) VALUES( %d, %d, 'shopper' )";
		$success = $this->db->query( $this->db->prepare( $sql, $optionid, $product_id ) );
		
		if( $success === FALSE ){
			return array( "error" );
		
		}else{
			$sql = "SELECT 
				ec_option.option_name,
				ec_option.option_label,
				ec_option.option_type,
				ec_option.option_required,
				ec_option.option_error_text,
				ec_option.option_id,
				ec_product.model_number,
				ec_product.product_id,
				ec_option_to_product.product_id,
				ec_option_to_product.option_to_product_id,
				ec_option_to_product.option_id
			  FROM
				ec_option_to_product
				INNER JOIN ec_product ON (ec_option_to_product.product_id = ec_product.product_id)
				INNER JOIN ec_option ON (ec_option_to_product.option_id = ec_option.option_id)
			  WHERE
				ec_option_to_product.product_id = %d
			  ORDER BY
				ec_option_to_product.option_to_product_id";
		
			$results = $this->db->get_results( $this->db->prepare( $sql, $product_id ) );
		
			if( count( $results ) > 0 ){
				return $results;
			}else{
				return array( "noresults" );
			}
		}
		
	}
	
	function getadvancedproductoptions( $product_id ){
		  
		$sql = "SELECT ec_option.option_name, ec_option.option_label, ec_option.option_type, ec_option.option_required, ec_option.option_error_text, ec_option.option_id, ec_product.model_number, ec_product.product_id, ec_option_to_product.product_id, ec_option_to_product.option_to_product_id, ec_option_to_product.option_id
			  FROM ec_option_to_product
				INNER JOIN ec_product ON (ec_option_to_product.product_id = ec_product.product_id)
				INNER JOIN ec_option ON (ec_option_to_product.option_id = ec_option.option_id)
			  WHERE
				ec_option_to_product.product_id = %d
			  ORDER BY
				ec_option_to_product.option_to_product_id";
		$results = $this->db->get_results( $this->db->prepare( $sql, $product_id ) );
		
		if( count( $results ) > 0 ){
			return $results;
		}else{
			return array( "noresults" );
		}
		
	}
	
	function getfeaturedproducts( ){
		
		$sql = "SELECT ec_product.product_id, ec_product.title, ec_product.model_number, ec_product.is_subscription_item FROM ec_product ORDER BY ec_product.title ASC";
		$results = $this->db->get_results( $sql );
		
		if( count( $results ) > 0 ){
			return $results;
		}else{
			return array( "noresults" );
		}
		
	}
	
	function getproductlist( ){

		$sql = "SELECT ec_product.title, ec_product.product_id, ec_product.is_subscription_item FROM ec_product ORDER BY ec_product.title ASC";
		$results = $this->db->get_results( $sql );
		
		if( count( $results ) > 0 ){
			return $results;
		}else{
			return array( "noresults" );
		}
		
	}
	
	function getproducts( $startrecord, $limit, $orderby, $ordertype, $filter ){
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS ec_product.* FROM ec_product  WHERE ec_product.product_id != '' " . $filter . " ORDER BY ".  $orderby ." ".  $ordertype . " LIMIT ".  $startrecord . ", ". $limit;
		$results = $this->db->get_results( $sql );
		$totalrows = $this->db->get_var( "SELECT FOUND_ROWS()" );
		$returnArray = array( );
		
		if( count( $results ) > 0 ){
			
			foreach( $results as $row ){
			  
				if( $row->use_optionitem_images == 1 ){
					$sql = "SELECT ec_optionitemimage.image1 FROM ec_optionitemimage WHERE ec_optionitemimage.product_id = %d";
					$image_results = $this->db->get_results( $this->db->prepare( $sql, $row->product_id ) );
					foreach( $image_results as $images ){
						$row->image1 = $images->image1;	
					}
				}
				
				$row->totalrows = $totalrows;
				$row->livepaymentprocessmethod = get_option('ec_option_payment_process_method');
				$returnArray[] = $row;
			}
		  
			return $returnArray;
		
		}else{
			return array( "noresults" );
		}
		
	}
	
	function duplicateproduct( $productid ){
		
		$sql = "SELECT ec_product.* FROM ec_product WHERE ec_product.product_id = %d";
		$product = $this->db->get_row( $this->db->prepare( $sql, $productid ) );
		$original_record = $product;
		
		$randmodel = rand(1000000, 10000000);
		
		$sql = "INSERT INTO ec_product( model_number ) VALUES( %s )";
		$success = $this->db->query( $this->db->prepare( $sql, $randmodel ) );
		
		if( $success === FALSE ){
			return array( "error" );
		
		}else{
			// Internal Function to Insert Duplicate, While Resetting Important Attributes
			$newid = $this->db->insert_id;
			$sql = "UPDATE ec_product SET ";
			foreach( $original_record as $key => $value ){
				
				if( $key != "product_id" && $key != "model_number" ){
					if( $key == "stock_quantity" ){
						$sql .= '`'.$key.'` = "0", ';
					}else if( $key == 'added_to_db_date' ){
						$sql .= '`'.$key.'` = NOW(), ';
					}else if( $key == 'views' ){
						$sql .= '`'.$key.'` = "0", ';
					}else{
						$sql .= '`'.$key.'` = ' . $this->db->prepare( '%s', $value ) .', ';
					}
				}

			}
			
			$sql = substr( $sql, 0, strlen( $query ) - 2 ); # lop off the extra trailing comma
			$sql .= " WHERE product_id = " . $newid;
			$this->db->query($sql);
			// END DUPLICATION INSERT
			
			// Duplicate Option Image Rows
			$sql = "SELECT * FROM ec_optionitemimage WHERE ec_optionitemimage.product_id = %d";
			$results = $this->db->get_results( $this->db->prepare( $sql, $productid ) );
			
			$sql = "INSERT INTO ec_optionitemimage( optionitem_id, image1, image2, image3, image4, image5, product_id ) VALUES( %s, %s, %s, %s, %s, %s, %d )";
			foreach( $results as $row ){
				$this->db->query( $this->db->prepare( $sql, $row->optionitem_id, $row->image1, $row->image2, $row->image3, $row->image4, $row->image5, $newid ) );
			}
			// END DUPLICATE OPTION IMAGE ROWS
			
			// Duplicate Tiered Pricing
			$sql = "SELECT * FROM ec_pricetier WHERE ec_pricetier.product_id = %d";
			$results = $this->db->get_results( $this->db->prepare( $sql, $productid ) );
			
			$sql = "INSERT INTO ec_pricetier( product_id, price, quantity) VALUES( %d, %s, %s )";
			foreach( $results as $row ){
				$this->db->query( $this->db->prepare( $sql, $newid, $row->price, $row->quantity ) );
			}
			// END DUPLICATE TIERED PRICING
			
			// Duplicate Category Listings
			$sql = "SELECT * FROM ec_categoryitem WHERE ec_categoryitem.product_id = %d";
			$results = $this->db->get_results( $this->db->prepare( $sql, $productid ) );
			
			$sql = "INSERT INTO ec_categoryitem( product_id, category_id ) VALUES( %d, %d )";
			foreach( $results as $row ){
				$this->db->query( $this->db->prepare( $sql, $newid, $row->category_id ) ); 
			}
			// END DUPLICATE CATEGORY LISTINGS
			
			// Duplicate B2B Role Pricing
			$sql = "SELECT * FROM ec_roleprice WHERE ec_roleprice.product_id = %d";
			$results = $this->db->get_results( $this->db->prepare( $sql, $productid));
			
			$sql = "INSERT INTO ec_roleprice( product_id, role_label, role_price ) VALUES( %d, %s, %s )";
			foreach( $results as $row ){
				$this->db->query( $this->db->prepare( $sql, $newid, $row->role_label, $row->role_price ) ); 
			}
			// END DUPLICATE B2B ROLE PRICING
			
			// Duplicate Option Quantity Rows
			$sql = "SELECT * FROM ec_optionitemquantity WHERE ec_optionitemquantity.product_id = %d";
			$results = $this->db->get_results( $this->db->prepare( $sql, $productid ) );
			
			$sql = "INSERT INTO ec_optionitemquantity( optionitem_id_1, optionitem_id_2, optionitem_id_3, optionitem_id_4, optionitem_id_5, quantity, product_id ) VALUES( %d, %d, %d, %d, %d, %s, %d )";
			foreach( $results as $row ){
				$this->db->query( $this->db->prepare( $sql, $row->optionitem_id_1, $row->optionitem_id_2, $row->optionitem_id_3, $row->optionitem_id_4, $row->optionitem_id_5, $row->quantity, $newid ) );
			}
			// END DUPLICATE OPTION QUANTITY ROWS
			
			//Enqueue Quickbooks Update Customer
			if( file_exists( "../../../../wp-easycart-quickbooks/QuickBooks.php" ) ){
				$quickbooks = new ec_quickbooks( );
				$quickbooks->add_product( $randmodel );	
			}
			
			// Insert a WordPress Custom post type post.
			$sql = "SELECT ec_product.title FROM ec_product WHERE ec_product.model_number = %s";
			$product = $this->db->get_row( $this->db->prepare( $sql, $randmodel ) );
			$post = array(	'post_content'	=> "[ec_store modelnumber=\"" . $randmodel . "\"]",
							'post_status'	=> "publish",
							'post_title'	=> $GLOBALS['language']->convert_text( $product->title ),
							'post_type'		=> "ec_store"
						  );
			$post_id = wp_insert_post( $post, $wp_error );
			$db = new ec_db( );
			$db->update_product_post_id( $newid, $post_id );
			
			return array( "success" );	
		} // close success if/else from above
		
	}
	
	function deleteproduct( $productid ){
		
		// First get post id and remove from WordPress system
		$sql = "SELECT ec_product.post_id FROM ec_product WHERE ec_product.product_id = %d";
		$this_post_id = $this->db->get_var( $this->db->prepare( $sql, $productid ) );
		wp_delete_post( $this_post_id, true );
		
		// Remove From Stripe Possibly
		if( get_option( 'ec_option_payment_process_method' ) == 'stripe' ){
			//create an object for call to stripe
			$stripe_plan = ( object ) array( "product_id" => $productid );
			$stripe = new ec_stripe;
			$response = $stripe->delete_plan( $stripe_plan );
		}
		
		// Remove Product
		$sql = "DELETE FROM ec_product WHERE ec_product.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		// Remove Option Item Images
		$sql = "DELETE FROM ec_optionitemimage WHERE ec_optionitemimage.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		// Remove Price Tiers
		$sql = "DELETE FROM ec_pricetier WHERE ec_pricetier.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		// Remove Role Pricing
		$sql = "DELETE FROM ec_roleprice WHERE ec_roleprice.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		// Remove Option Item Quantity
		$sql = "DELETE FROM ec_optionitemquantity WHERE ec_optionitemquantity.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		// Remove Reviews
		$sql = "DELETE FROM ec_review WHERE ec_review.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		// Remove Item from Product Groupings
		$sql = "DELETE FROM ec_categoryitem WHERE ec_categoryitem.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $productid ) );
		
		return array( "success" ); // would need to check each query to return failed...
		
	}
	
	function updateproduct( $productid, $product ){
		
		// Update the WordPress Entry
		$sql = "SELECT ec_product.post_id FROM ec_product WHERE ec_product.product_id = %d";
		$this_post_id = $this->db->get_var( $this->db->prepare( $sql, $productid ) );
		$post = array(	'ID'			=> $this_post_id,
						'post_content'	=> "[ec_store modelnumber=\"" . $product->modelnumber . "\"]",
						'post_status'	=> "publish",
						'post_title'	=> $GLOBALS['language']->convert_text( $product->producttitle ),
						'post_type'		=> "ec_store",
						'post_name'		=> str_replace(' ', '-', $GLOBALS['language']->convert_text( $product->producttitle ) ),
					  );
		wp_update_post( $post );
		
		// Configure Default Images  
		if( $product->useoptionitemimages == 1 ){
			$product->Image1 = '';
			$product->Image2 = '';
			$product->Image3 = '';
			$product->Image4 = '';
			$product->Image5 = ''; 
		}
		
		// Set Default Handling Price
		$handling_price = '0.000';
		if( $product->handling_price ) 
			$handling_price = $product->handling_price;
		
		// Set Default VAT Rate
		$vat_rate = '0.000';
		if( $product->vatrate )
			$vat_rate = $product->vatrate;
		
		$sql = "UPDATE ec_product SET price = %s, title = %s, description = %s, model_number = %s, activate_in_store = %s, manufacturer_id = %s, image1 = %s, image2 = %s, image3 = %s, image4 = %s, image5 = %s, is_giftcard = %s, download_file_name = %s, is_taxable = %s, is_download = %s, weight = %s, stock_quantity = %s, show_on_startup = %s, menulevel1_id_1 = %s, menulevel1_id_2 = %s, menulevel1_id_3 = %s, menulevel2_id_1 = %s, menulevel2_id_2 = %s, menulevel2_id_3 = %s, menulevel3_id_1 = %s, menulevel3_id_2 = %s, menulevel3_id_3 = %s, option_id_1 = %s, option_id_2 = %s, option_id_3 = %s, option_id_4 = %s, option_id_5 = %s, featured_product_id_1 = %s, featured_product_id_2 = %s, featured_product_id_3 = %s, featured_product_id_4 = %s, seo_description = %s, use_specifications = %s, use_customer_reviews = %s, specifications = %s, list_price = %s, seo_keywords = %s, is_special = %s, use_optionitem_images = %s, use_optionitem_quantity_tracking = %s, is_donation = %s, show_stock_quantity = %s, maximum_downloads_allowed = %s, download_timelimit_seconds = %s, handling_price = %s, vat_rate= %s, use_advanced_optionset = %s, is_subscription_item = %s, subscription_bill_length = %s, subscription_bill_period = %s, height = %s, width = %s, length = %s, trial_period_days = %s, allow_multiple_subscription_purchases = %s, subscription_plan_id = %s, membership_page = %s, min_purchase_quantity = %s, is_amazon_download = %s, amazon_key = %s, catalog_mode = %s, catalog_mode_phrase = %s, inquiry_mode = %s, inquiry_url = %s, is_deconetwork = %s, deconetwork_mode = %s, deconetwork_product_id = %s, deconetwork_size_id = %s, deconetwork_color_id = %s, deconetwork_design_id = %s, short_description = %s, tag_type = %s, tag_bg_color = %s, tag_text = %s, tag_text_color = %s WHERE product_id = %d";
		
		$success = $this->db->get_results( $this->db->prepare( $sql, $product->listprice, $product->producttitle, $product->productdescription, $product->modelnumber, $product->listproduct, $product->productmanufacturer, $product->Image1, $product->Image2, $product->Image3, $product->Image4, $product->Image5, $product->isgiftcard, $product->downloadid, $product->taxableproduct, $product->isdownload, $product->productweight, $product->quantity, $product->featuredproduct, $product->Cat1Name, $product->Cat2Name, $product->Cat3Name, $product->Cat1bName, $product->Cat2bName, $product->Cat3bName, $product->Cat1cName, $product->Cat2cName, $product->Cat3cName, $product->option1, $product->option2, $product->option3, $product->option4, $product->option5, $product->featureproduct1, $product->featureproduct2, $product->featureproduct3, $product->featureproduct4, $product->seoshortdescription, $product->usespecs, $product->allowreviews, $product->specifications, $product->previousprice, $product->seokeywords, $product->isspecial, $product->useoptionitemimages, $product->usequantitytracking, $product->isdonation, $product->show_stock_quantity, $product->maximum_downloads_allowed, $product->download_timelimit_seconds, $product->handling_price, $product->vatrate, $product->use_advanced_optionset, $product->issubscription, $product->subscriptioninterval, $product->subscriptionperiod, $product->productheight, $product->productwidth, $product->productlength, $product->trialdays, $product->allowmultisubscriptions, $product->subscriptionstripeplanid, $product->membershippage, $product->min_purchase_quantity, $product->is_amazon_download, $product->amazon_key, $product->catalog_mode, $product->catalog_mode_phrase, $product->inquiry_mode, $product->inquiry_url, $product->isdeconetwork, $product->deconetwork_mode,$product->deconetwork_product_id,$product->deconetwork_size_id,$product->deconetwork_color_id, $product->deconetwork_design_id, $product->shortdescription, $product->tag_type, $product->tag_bg_color, $product->tag_text, $product->tag_text_color, $productid ) );
		
		// Enqueue Quickbooks Update Customer
		if( file_exists( "../../../../wp-easycart-quickbooks/QuickBooks.php" ) ){
			$quickbooks = new ec_quickbooks( );
			$quickbooks->update_product( $product->modelnumber );	
		}
		
		// If Using Stripe and is Subscription
		if( get_option( 'ec_option_payment_process_method' ) == 'stripe' && $product->issubscription == '1' ){
			$stripe_plan = ( object ) array(
					"product_id" 	=> $productid,
					"title"			=> $product->producttitle );
		
			$stripe = new ec_stripe;
			$response = $stripe->update_plan( $stripe_plan );
			
			if( $response == false ){
				$stripe_plan = ( object ) array(
					  "price" 						=> $product->listprice,
					  "product_id" 					=> $productid,
					  "title"						=> $product->producttitle,
					  "subscription_bill_period" 	=> $product->subscriptionperiod,
					  "subscription_bill_length" 	=> $product->subscriptioninterval,
					  "trial_period_days" 			=> $product->trialdays );
		
				$stripe = new ec_stripe;
				$response = $stripe->insert_plan( $stripe_plan );
				if( $response ){
					$updatestripeboolean = "UPDATE ec_product SET ec_product.stripe_plan_added = '1' WHERE ec_product.product_id = %d";
					$this->db->query( $this->db->prepare( $updatestripeboolean, $productid ) );
				}
			}
		} // Close Stripe Subscription Check
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}

	function addproduct( $product ){
		
		if( $product->useoptionitemimages == 1 ){
			$product->Image1 = '';
			$product->Image2 = '';
			$product->Image3 = '';
			$product->Image4 = '';
			$product->Image5 = ''; 
		}
		
		// Set Default Handling Price
		$handling_price = '0.000';
		if( $product->handling_price )
			$handling_price = $product->handling_price;
		
		// Set Default VAT Rate
		$vat_rate = '0.000';
		if( $product->vatrate )
			$vat_rate = $product->vatrate;
		
		$sql = "INSERT INTO ec_product( ec_product.price, ec_product.title, ec_product.description, ec_product.model_number, ec_product.activate_in_store, ec_product.manufacturer_id, ec_product.image1, ec_product.image2, ec_product.image3, ec_product.image4, ec_product.image5, ec_product.is_giftcard, ec_product.download_file_name, ec_product.is_taxable, ec_product.is_download, ec_product.weight, ec_product.stock_quantity, ec_product.show_on_startup, ec_product.menulevel1_id_1, ec_product.menulevel1_id_2, ec_product.menulevel1_id_3, ec_product.menulevel2_id_1, ec_product.menulevel2_id_2, ec_product.menulevel2_id_3, ec_product.menulevel3_id_1, ec_product.menulevel3_id_2, ec_product.menulevel3_id_3, ec_product.option_id_1, ec_product.option_id_2, ec_product.option_id_3, ec_product.option_id_4, ec_product.option_id_5, ec_product.featured_product_id_1, ec_product.featured_product_id_2, ec_product.featured_product_id_3, ec_product.featured_product_id_4, ec_product.seo_description, ec_product.use_specifications, ec_product.use_customer_reviews, ec_product.specifications, ec_product.list_price, ec_product.seo_keywords, ec_product.is_special, ec_product.use_optionitem_images, ec_product.use_optionitem_quantity_tracking, ec_product.is_donation, ec_product.show_stock_quantity, ec_product.maximum_downloads_allowed, ec_product.download_timelimit_seconds, ec_product.handling_price, ec_product.vat_rate, ec_product.use_advanced_optionset, ec_product.is_subscription_item, ec_product.subscription_bill_length, ec_product.subscription_bill_period, ec_product.height, ec_product.width, ec_product.length, ec_product.trial_period_days, ec_product.allow_multiple_subscription_purchases, ec_product.subscription_plan_id, ec_product.membership_page, ec_product.min_purchase_quantity, ec_product.is_amazon_download , ec_product.amazon_key, ec_product.catalog_mode, ec_product.catalog_mode_phrase, ec_product.inquiry_mode, ec_product.inquiry_url, ec_product.is_deconetwork, ec_product.deconetwork_mode, ec_product.deconetwork_product_id, ec_product.deconetwork_size_id, ec_product.deconetwork_color_id, ec_product.deconetwork_design_id, ec_product.short_description, ec_product.tag_type, ec_product.tag_bg_color, ec_product.tag_text, ec_product.tag_text_color )
		VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)";
		
		$success = $this->db->query( $this->db->prepare( $sql, $product->listprice, $product->producttitle, $product->productdescription, $product->modelnumber, $product->listproduct, $product->productmanufacturer, $product->Image1, $product->Image2, $product->Image3, $product->Image4, $product->Image5, $product->isgiftcard, $product->downloadid, $product->taxableproduct, $product->isdownload, $product->productweight, $product->quantity, $product->featuredproduct, $product->Cat1Name, $product->Cat2Name, $product->Cat3Name, $product->Cat1bName, $product->Cat2bName, $product->Cat3bName, $product->Cat1cName, $product->Cat2cName, $product->Cat3cName, $product->option1, $product->option2, $product->option3, $product->option4, $product->option5, $product->featureproduct1, $product->featureproduct2, $product->featureproduct3, $product->featureproduct4, $product->seoshortdescription, $product->usespecs, $product->allowreviews, $product->specifications, $product->previousprice, $product->seokeywords, $product->isspecial, $product->useoptionitemimages, $product->usequantitytracking, $product->isdonation, $product->show_stock_quantity, $product->maximum_downloads_allowed, $product->download_timelimit_seconds, $handling_price, $vat_rate, $product->use_advanced_optionset, $product->issubscription, $product->subscriptioninterval, $product->subscriptionperiod, $product->productheight, $product->productwidth, $product->productlength, $product->trialdays, $product->allowmultisubscriptions, $product->subscriptionstripeplanid, $product->membershippage, $product->min_purchase_quantity, $product->is_amazon_download , $product->amazon_key, $product->catalog_mode, $product->catalog_mode_phrase, $product->inquiry_mode, $product->inquiry_url,$product->isdeconetwork, $product->deconetwork_mode, $product->deconetwork_product_id, $product->deconetwork_size_id, $product->deconetwork_color_id, $product->deconetwork_design_id, $product->shortdescription, $product->tag_type, $product->tag_bg_color, $product->tag_text, $product->tag_text_color ) );
		
		if( $success === FALSE) {
			return array( "duplicate" );
		}
						
		$sql = "SELECT ec_product.product_id FROM ec_product WHERE ec_product.model_number = %s";
		$newproductid = $this->db->get_var( $this->db->prepare( $sql, $product->modelnumber ) );
		
		$sql = "UPDATE ec_optionitemquantity SET ec_optionitemquantity.product_id = %d WHERE ec_optionitemquantity.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $newproductid, $product->product_id ) );
		
		$sql = "UPDATE ec_optionitemimage SET ec_optionitemimage.product_id = %d WHERE ec_optionitemimage.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $newproductid, $product->product_id ) );
		
		$sql = "UPDATE ec_categoryitem SET ec_categoryitem.product_id = %d WHERE ec_categoryitem.product_id = %d";
		$this->db->query( $this->db->prepare( $sql, $newproductid, $product->product_id ) );
		
		
		// If Using Stripe and is Subscription, Insert Plan
		if( get_option( 'ec_option_payment_process_method' ) == 'stripe' && $product->issubscription == '1' ){
			$stripe_plan = ( object ) array(
					"price" 						=> $product->listprice,
					"product_id" 					=> $newproductid,
					"title"							=> $product->producttitle,
					"subscription_bill_period" 		=> $product->subscriptionperiod,
					"subscription_bill_length" 		=> $product->subscriptioninterval,
					"trial_period_days" 			=> $product->trialdays );
		
			$stripe = new ec_stripe;
			$response = $stripe->insert_plan( $stripe_plan );
			if( $response ){
				$sql = "UPDATE ec_product SET ec_product.stripe_plan_added = '1' WHERE ec_product.product_id = %d";
				$this->db->query( $this->db->prepare( $sql, $newproductid ) );
			}
		}
		
		//Enqueue Quickbooks Update Customer
		if( file_exists( "../../../../wp-easycart-quickbooks/QuickBooks.php" ) ){
			$quickbooks = new ec_quickbooks( );
			$quickbooks->add_product( $product->modelnumber );	
		}
		
		// Insert a WordPress Custom post type post.
		$post = array(	'post_content'	=> "[ec_store modelnumber=\"" . $product->modelnumber . "\"]",
					'post_status'	=> "publish",
					'post_title'	=> $GLOBALS['language']->convert_text( $product->producttitle ),
					'post_type'		=> "ec_store"
				  );
		$post_id = wp_insert_post( $post, $wp_error );
		$db = new ec_db( );
		$db->update_product_post_id( $newproductid, $post_id );
		
		return array( "success" );
		
	}
	
	function deleteimage( $productid, $imagelocation, $imagename ){
		
		$sql = "UPDATE ec_product SET image" . $imagelocation . " = '' WHERE ec_product.product_id = %d";
		$duplicatesql = "SELECT product_id from ec_product WHERE ec_product.image" . $imagelocation . " = %s";
		$duplicateresult = $this->db->get_var( $this->db->prepare( $duplicatesql, $imagename ) );
		
		// If image exists elsewhere, do not delete it.
		if( $duplicateresult == NULL ) {
			if( file_exists( "../../../products/pics" . $imagelocation . "/" . $imagename ) )
				unlink( "../../../products/pics" . $imagelocation . "/" . $imagename );
		}
		
		$success = $this->db->query( $this->db->prepare( $sql, $productid ) );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	
	function deleteoptionitemimage( $productid, $optionitemid, $imagelocation, $imagename ){
		
		$sql = "UPDATE ec_optionitemimage SET image" . $imagelocation . " = '' WHERE ec_optionitemimage.product_id = %s AND ec_optionitemimage.optionitem_id = %s";
		
		$success = $this->db->query( $this->db->prepare( $sql, $productid, $optionitemid ) );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	
	
	function deletefiledownload( $productid, $filename ){
		
		$sql = "UPDATE ec_product SET ec_product.download_file_name = '' WHERE ec_product.product_id = %d";
		$success = $this->db->query( $this->db->prepare( $sql, $productid ) );
		
		if( file_exists( "../../../products/downloads/" . $filename ) )
			unlink( "../../../products/downloads/" . $filename );
		
		if( $success === FALSE ){
			return array( "error" );
		}else{
			return array( "success" );
		}
		
	}
	
	function getawsfiles( ){
		
		$returnArray = array( );
		
		if( ( get_option( 'ec_option_amazon_key' ) != '' && get_option( 'ec_option_amazon_key' ) != '0' ) && 
			( get_option( 'ec_option_amazon_secret' ) != '' && get_option( 'ec_option_amazon_secret' ) != '0' ) &&
			( get_option( 'ec_option_amazon_bucket' ) != '' && get_option( 'ec_option_amazon_bucket' ) != '0' ) ){
				
			if( phpversion( ) >= 5.3 ){
					
				require_once( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . "/inc/classes/account/ec_amazons3.php" );
				$amazons3 = new ec_amazons3( );
				$returnArray = $amazons3->get_aws_files( );
			
			}else{
				
				$returnArray[] = "PHP 5.3+ Required";
				
			}
			
			if( count( $returnArray ) > 0 ){
				return $returnArray;
			}else{
				return array( "noresults" );
			}
			
		}else{
			return array( "noresults" );
		}
		
	}

}
?>