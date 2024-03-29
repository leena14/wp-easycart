<?php
// Video Option
if( isset( $this->page_options->video_viewed ) || get_option( 'ec_option_hide_design_help_video' ) == '1' ){
	$show_video = false;
}else{
	$show_video = true;
}

// Check for Safari/Admin //
$ua = $_SERVER["HTTP_USER_AGENT"];
$safariorchrome = strpos($ua, 'Safari') ? true : false;
$chrome = strpos($ua, 'Chrome') ? true : false;
if( $safariorchrome && !$chrome )
	$safari = true;
else
	$safari = false;

$ipad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$iphone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');

$is_admin = current_user_can( 'manage_options' );

if( isset( $_GET['preview'] ) ){
	$is_preview = true;
}else{
	$is_preview = false;
}

if( isset( $_GET['previewholder'] ) )
	$is_preview_holder = true;
else
	$is_preview_holder = false;
	
// END CHECK // 

/* PREVIEW CONTENT */
if( $is_preview_holder && $is_admin ){ ?>

<div class="ec_admin_preview_container" id="ec_admin_preview_container">
	<div class="ec_admin_preview_content">
    	<div class="ec_admin_preview_button_container">
            <div class="ec_admin_preview_ipad_landscape"><input type="button" onclick="ec_admin_ipad_landscape_preview( );" value="iPad Landscape"></div>
            <div class="ec_admin_preview_ipad_portrait"><input type="button" onclick="ec_admin_ipad_portrait_preview( );" value="iPad Portrait"></div>
            <div class="ec_admin_preview_iphone_landscape"><input type="button" onclick="ec_admin_iphone_landscape_preview( );" value="iPhone Landscape"></div>
            <div class="ec_admin_preview_iphone_portrait"><input type="button" onclick="ec_admin_iphone_portrait_preview( );" value="iPhone Portrait"></div>
        </div>
		<div id="ec_admin_preview_content" class="ec_admin_preview_wrapper ipad landscape">
			<iframe src="<?php echo $this->cart_page . $this->permalink_divider; ?>preview=true" width="100%" height="100%" id="ec_admin_preview_iframe"></iframe>
		</div>
	</div>
</div>

<?php }else if( $is_admin && !$safari && !$is_preview ){ ?>

<div class="ec_admin_video_container" id="ec_admin_video_container"<?php if( !$show_video ){ ?> style="display:none;"<?php }?>>
	<div class="ec_admin_video_content">
		<div class="ec_admin_video_padding">
    		<div class="ec_admin_video_holder">
    	    	<div class="ec_admin_video">
                	<h3>WP EasyCart Design Help</h3>
                    <h5>Do you need help in designing your perfect store? Watch our short video and start on your way to success.</h5>
                    <div class="ec_admin_video_hide_div"><a href="" onclick="ec_admin_hide_video_from_page( '<?php global $post; echo $post->ID; ?>' ); return false;">Hide the help video for this page?</a> OR if you no longer need design help, <a href="" onclick="ec_admin_hide_video_forever( ); return false;">hide it forever</a></div>
    	        	
                    <video width="853" height="480" controls>
						<source src="http://wpeasycart.com/videos/v3_feature_demo.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
    	        	<div class="ec_admin_video_close"><input type="button" onclick="ec_admin_close_video_screen( );" value="x"></div>
                </div>
    	    </div>
    	</div>
	</div>
</div>

<div class="ec_admin_successfully_update_container" id="ec_admin_page_updated">
	<div class="ec_admin_successfully_updated">
    	<div>Your Page Settings Have Been Updated Successfully. The Page Will Now Reload.</div>
    </div>
</div>
        
<div class="ec_admin_loader_container" id="ec_admin_page_updated_loader">
	<div class="ec_admin_loader">
    	<div>Updating Your Page Options...</div>
    </div>
</div>

<div class="ec_admin_loader_bg" id="ec_admin_loader_bg"></div>

<div id="ec_page_editor" class="ec_slideout_editor ec_display_editor_false ec_cart_editor">
	<div id="ec_page_editor_openclose_button" class="ec_slideout_openclose" data-post-id="<?php global $post; echo $post->ID; ?>">
    	<div class="dashicons dashicons-admin-generic"></div>
    </div>
    
    <div class="ec_admin_preview_button"><a href="<?php echo $this->cart_page . $this->permalink_divider; ?>previewholder=true" target="_blank">Show Device Preview</a></div>
    
    <div class="ec_admin_page_size">Cart Options</div>
    <div><strong>Desktop Columns</strong></div>
    <div><select id="ec_option_cart_columns_desktop">
    		<option value="0"<?php if( get_option( 'ec_option_cart_columns_desktop' ) == "" ){?> selected="selected"<?php }?>>Select One</option>
            <option value="1"<?php if( get_option( 'ec_option_cart_columns_desktop' ) == "1" ){?> selected="selected"<?php }?>>1 Column</option>
            <option value="2"<?php if( get_option( 'ec_option_cart_columns_desktop' ) == "2" ){?> selected="selected"<?php }?>>2 Columns</option>
    </select></div>
    <div><strong>Tablet Landscape Columns</strong></div>
    <div><select id="ec_option_cart_columns_laptop">
    		<option value="0"<?php if( get_option( 'ec_option_cart_columns_laptop' ) == "" ){?> selected="selected"<?php }?>>Select One</option>
            <option value="1"<?php if( get_option( 'ec_option_cart_columns_laptop' ) == "1" ){?> selected="selected"<?php }?>>1 Column</option>
            <option value="2"<?php if( get_option( 'ec_option_cart_columns_laptop' ) == "2" ){?> selected="selected"<?php }?>>2 Columns</option>
    </select></div>
    <div><strong>Tablet Portfolio Columns</strong></div>
    <div><select id="ec_option_cart_columns_tablet_wide">
    		<option value="0"<?php if( get_option( 'ec_option_cart_columns_tablet_wide' ) == "" ){?> selected="selected"<?php }?>>Select One</option>
            <option value="1"<?php if( get_option( 'ec_option_cart_columns_tablet_wide' ) == "1" ){?> selected="selected"<?php }?>>1 Column</option>
            <option value="2"<?php if( get_option( 'ec_option_cart_columns_tablet_wide' ) == "2" ){?> selected="selected"<?php }?>>2 Columns</option>
    </select></div>
    <div><strong>Smartphone Landscape Columns</strong></div>
    <div><select id="ec_option_cart_columns_tablet">
    		<option value="0"<?php if( get_option( 'ec_option_cart_columns_tablet' ) == "" ){?> selected="selected"<?php }?>>Select One</option>
            <option value="1"<?php if( get_option( 'ec_option_cart_columns_tablet' ) == "1" ){?> selected="selected"<?php }?>>1 Column</option>
            <option value="2"<?php if( get_option( 'ec_option_cart_columns_tablet' ) == "2" ){?> selected="selected"<?php }?>>2 Columns</option>
    </select></div>
    <div><strong>Smartphone Portfolio Columns</strong></div>
    <div><select id="ec_option_cart_columns_smartphone">
    		<option value="0"<?php if( get_option( 'ec_option_cart_columns_smartphone' ) == "" ){?> selected="selected"<?php }?>>Select One</option>
            <option value="1"<?php if( get_option( 'ec_option_cart_columns_smartphone' ) == "1" ){?> selected="selected"<?php }?>>1 Column</option>
            <option value="2"<?php if( get_option( 'ec_option_cart_columns_smartphone' ) == "2" ){?> selected="selected"<?php }?>>2 Columns</option>
    </select></div>
    <div><strong>Dark/Light Theme Background</strong></div>
    <div><select id="ec_option_use_dark_bg">
    		<option value="0"<?php if( get_option( 'ec_option_use_dark_bg' ) == "" ){?> selected="selected"<?php }?>>Select One</option>
            <option value="1"<?php if( get_option( 'ec_option_use_dark_bg' ) == "1" ){?> selected="selected"<?php }?>>Dark Background</option>
            <option value="0"<?php if( get_option( 'ec_option_use_dark_bg' ) == "0" ){?> selected="selected"<?php }?>>Light Background</option>
    </select></div>
    
    <div><input type="button" value="APPLY AND SAVE" onclick="ec_admin_save_cart_options( ); return false;" /></div>
    
    <div class="ec_editor_link_row"><a href="<?php echo get_admin_url( ); ?>admin.php?page=ec_adminv2&ec_page=store-setup&ec_panel=basic-settings#cart-settings" target="_blank">Edit Basic Cart Settings</a></div>
    
</div>

<script>
function ec_admin_save_cart_options( ){
	
	jQuery( "#ec_admin_page_updated_loader" ).show( );
	jQuery( "#ec_admin_loader_bg" ).show( );
	
	var data = {
		action: 'ec_ajax_save_cart_options',
		ec_option_cart_columns_desktop: jQuery( '#ec_option_cart_columns_desktop' ).val( ),
		ec_option_cart_columns_laptop: jQuery( '#ec_option_cart_columns_laptop' ).val( ),
		ec_option_cart_columns_tablet_wide: jQuery( '#ec_option_cart_columns_tablet_wide' ).val( ),
		ec_option_cart_columns_tablet: jQuery( '#ec_option_cart_columns_tablet' ).val( ),
		ec_option_cart_columns_smartphone: jQuery( '#ec_option_cart_columns_smartphone' ).val( ),
		ec_option_use_dark_bg: jQuery( '#ec_option_use_dark_bg' ).val( ),
	}
	
	jQuery.ajax({url: ajax_object.ajax_url, type: 'post', data: data, success: function(data){ 
		jQuery( "#ec_admin_page_updated_loader" ).hide( );
		jQuery( "#ec_admin_page_updated" ).show( );
		jQuery( "#ec_admin_loader_bg" ).fadeOut( 'slow' );
		location.reload( );
	} } );
	
	jQuery( '#ec_page_editor' ).animate( { left:'-290px' }, {queue:false, duration:220} ).removeClass( 'ec_display_editor_true' ).addClass( 'ec_display_editor_false' );
	
}
</script>

<?php }// Close editor content ?>

<?php $this->display_cart_success( ); ?>

<?php $this->display_cart_error( ); ?>

<?php $this->display_cart( "" ); ?>

<?php if( $this->cart->total_items > 0 ){ ?>

	<?php   // START CHECKOUT_INFO PAGE

    if( $this->should_display_page_one( ) ){ ?>

        <?php $this->display_checkout_details( ); ?>

    <?php } // END CHECKOUT_INFO PAGE ?>

    <?php   // START CHECKOUT_SHIPPING PAGE
	
	if( $this->should_display_page_two( ) ){ ?>

            <?php $this->display_shipping_method(); ?>

    <?php } // END CHECKOUT_SHIPPING PAGE ?>

    <?php   // START PAYMENT PAGE
	
	if( $this->should_display_page_three( ) ){ ?>

        <?php $this->display_payment( ); ?>

    <?php } // END CHECKOUT_PAYMENT PAGE ?>

<?php }else{ ?>

<div class="ec_cart_empty">
	<?php echo $GLOBALS['language']->get_text( 'cart', 'cart_empty_cart' ); ?>
</div>

<div class="ec_cart_empty_button_row">
	<a href="<?php echo $this->store_page; ?>" class="ec_cart_empty_button"><?php echo $GLOBALS['language']->get_text( 'cart', 'cart_return_to_store' ); ?></a>
</div>

<?php }?>
<div style="clear:both;"></div>
<div id="ec_current_media_size"></div>