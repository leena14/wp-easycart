<?php
/*
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//All Code and Design is copyrighted by Level Four Development, llc
//
//Level Four Development, LLC provides this code "as is" without warranty of any kind, either express or implied,     
//including but not limited to the implied warranties of merchantability and/or fitness for a particular purpose.         
//
//Only licensed users may use this code and storfront for live purposes. All other use is prohibited and may be 
//subject to copyright violation laws. If you have any questions regarding proper use of this code, please
//contact Level Four Development, llc and EasyCart prior to use.
//
//All use of this storefront is subject to our terms of agreement found on Level Four Development, llc's  website.
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
*/

//load our connection settings
ob_start( NULL, 4096 );
require_once( '../../../../../../wp-load.php' );
global $wpdb;

$requestID = "-1";
if( isset( $_GET['reqID'] ) )
	$requestID = $_GET['reqID'];

$user_sql = "SELECT  ec_user.*, ec_role.admin_access FROM ec_user LEFT JOIN ec_role ON (ec_user.user_level = ec_role.role_label) WHERE ec_user.password = %s AND  (ec_user.user_level = 'admin' OR ec_role.admin_access = 1)";
$users = $wpdb->get_results( $wpdb->prepare( $user_sql, $requestID ) );

if( !empty( $users ) || is_user_logged_in( ) ){

	$settings_sql = "SELECT max_width, max_height FROM ec_setting";
	$settings_row = $wpdb->get_row( $settings_sql );
	
	$date = $_POST['datemd5'];
	$optionitemid = $_POST['optionitemid'];
	$maxwidth = $settings_row['max_width'];
	$maxheight = $settings_row['max_height'];
	$imagequality = $_POST['imagequality'];

	$filename = $_FILES['Filedata']['name'];	
	$filetmpname = $_FILES['Filedata']['tmp_name'];	
	$fileType = $_FILES["Filedata"]["type"];
	$fileSizeMB = ( $_FILES["Filedata"]["size"] / 1024 / 1000 );

	$explodedfilename = pathinfo( $filename );
	$nameoffile = $explodedfilename['filename'];
	$fileextension = $explodedfilename['extension'];

	include( "resizer.php" );

	move_uploaded_file( $_FILES['Filedata']['tmp_name'], "../../../products/swatches/".$nameoffile."_".$date.".".$fileextension );
	copy( "../../../products/swatches/".$nameoffile."_".$date.".".$fileextension, "../../../../wp-easycart-data/products/swatches/".$nameoffile."_".$date.".".$fileextension );

	$resizeObj = new resizer( "../../../products/swatches/".$nameoffile."_".$date.".".$fileextension );
	$resizeObj->resize( $maxwidth, $maxheight, "../../../products/swatches/".$nameoffile."_".$date.".".$fileextension, $imagequality );
	
	$resizeObj = new resizer( "../../../../wp-easycart-data/products/swatches/".$nameoffile."_".$date.".".$fileextension );
	$resizeObj->resize( $maxwidth, $maxheight, "../../../../wp-easycart-data/products/swatches/".$nameoffile."_".$date.".".$fileextension, $imagequality );

	$sqlfilename = $nameoffile . '_' . $date . '.' .$fileextension;	
	$sql = "UPDATE ec_optionitem SET ec_optionitem.optionitem_icon = %s WHERE ec_optionitem.optionitem_id = %s";
	$wpdb->query( $wpdb->prepare( $sql, $sqlfilename, $optionitemid ) );

}
?>