<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type='text/css'>
    <!--
		.ec_title {font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 18px; float:left; width:100%; border-bottom:3px solid #CCC; margin-bottom:15px; }
        .ec_image { float:left; width:35%;}
		.ec_image > img{ max-width:100%; }
		.ec_content{ width:65%; padding-left:15px; }
		.ec_content_row{ font-family: Arial, Helvetica, sans-serif; font-size:12px; float:left; width:100%; margin:0 0 10px; }
		.ec_content_row strong{ font-weight:bold; }
		.ec_content_row.ec_extra_margin{ margin-top:25px; }
	-->
    </style>
</head>

<body>

	<table>
    	<thead>
        	<tr>
            	<td class="ec_title" colspan="2"><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_header" ); ?></td>
            </tr>
    	</thead>
    	<tbody>
    		<tr>
            	<td class="ec_image"><img src="<?php 
				if( $cart_item->is_deconetwork ){
					echo get_option( 'ec_option_deconetwork_url' ) . $cart_item->deconetwork_image_link;
				}else if( $cart_item->image1_optionitem && file_exists( WP_PLUGIN_DIR . "/wp-easycart-data/products/pics1/" . $cart_item->image1_optionitem ) ){
					echo plugins_url( "wp-easycart-data/products/pics1/" . $cart_item->image1_optionitem );
				}else{
					echo plugins_url( "wp-easycart-data/products/pics1/" . $cart_item->image1 );
				} ?>" alt="<?php echo $cart_item->title; ?>" /></td>
                <td class="ec_content">
                    <div class="ec_content_row"><strong><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_to" ); ?>:</strong> <?php echo $cart_item->gift_card_to_name; ?></div>
                    <div class="ec_content_row"><strong><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_from" ); ?>:</strong> <?php echo $cart_item->gift_card_from_name; ?></div>
                    <div class="ec_content_row"><?php echo $cart_item->gift_card_message; ?></div>
                    <div class="ec_content_row ec_extra_margin"><strong><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_id" ); ?>: <?php echo $giftcard_id; ?></strong></div>
                    <div class="ec_content_row"><strong><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_amount" ); ?>: <?php echo $GLOBALS['currency']->get_currency_display( $cart_item->unit_price ); ?></strong></div>
                    <div class="ec_content_row ec_extra_margin"><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_giftcard_receipt_message" ); ?> <a href="<?php echo $this->store_page; ?>" target="_blank"><?php echo $this->store_page; ?></a>.</div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>