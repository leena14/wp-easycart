<html>


<head>


	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


    <title><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_receipt_title" ) . " " . $order_id; ?></title>


	<style type='text/css'>


    <!--


		.style20 {font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 12px; }


        .style22 {font-family: Arial, Helvetica, sans-serif; font-size: 12px; }


		.ec_option_label{font-family: Arial, Helvetica, sans-serif; font-size:11px; font-weight:bold; }


		.ec_option_name{font-family: Arial, Helvetica, sans-serif; font-size:11px; }


	-->


    </style>


</head>


<body>


<table width='539' border='0' align='center'>


  <tr>


    <td colspan='4' align='left' class='style22'><img src='<?php echo $email_logo_url; ?>'></td>


  </tr>


  <tr>


    <td colspan='4' align='left' class='style22'><p><br>


        <?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_line_1" ) . " " . $order->billing_first_name . " " . $order->billing_last_name; ?>:</p>


      <p><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_line_2" ); ?> <strong><?php echo $order_id; ?></strong></p>


      <p><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_line_3" ); ?></p>


      <p><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_line_4" ); ?><br>


        <br>


        <br>


      </p></td>


  </tr>


  <tr>


    <td colspan='4' align='left' class='style20'><table width='100%' border='0' align='center' cellpadding='0' cellspacing='0'>


        <tr>


          <td width='47%' bgcolor='#F3F1ED' class='style20'><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_billing_label" ); ?></td>


          <td width='3%'>&nbsp;</td>


          <td width='50%' bgcolor='#F3F1ED' class='style20'></td>


        </tr>


        <tr>


          <td><span class='style22'><?php echo $order->billing_first_name; ?> <?php echo $order->billing_last_name; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_first_name; ?> <?php echo $order->shipping_last_name; ?><?php }?></span></td>


        </tr>

		<?php if( $order->billing_company_name != "" || ( get_option( 'ec_option_use_shipping' ) && $order->shipping_company_name != "" ) ){ ?>

        <tr>


          <td><span class='style22'><?php echo $order->billing_company_name; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_company_name; ?><?php }?></span></td>


        </tr>
		
        <?php }?>

        <tr>


          <td><span class='style22'><?php echo $order->billing_address_line_1; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_address_line_1; ?><?php }?></span></td>


        </tr>


        <?php if( $order->billing_address_line_2 != "" || $order->shipping_address_line_2 != "" ){ ?>


        <tr>


          <td><span class='style22'><?php echo $order->billing_address_line_2; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_address_line_2; ?><?php }?></span></td>


        </tr>


        <?php } ?>


        <tr>


          <td><span class='style22'><?php echo $order->billing_city; ?>, <?php echo $order->billing_state; ?> <?php echo $order->billing_zip; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_city; ?>, <?php echo $order->shipping_state; ?> <?php echo $order->shipping_zip; ?><?php }?></span></td>


        </tr>


        <tr>


          <td><span class='style22'><?php echo $order->billing_country; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_country; ?><?php }?></span></td>


        </tr>


        <tr>


          <td><span class='style22'><?php echo $order->billing_phone; ?></span></td>


          <td>&nbsp;</td>


          <td><span class='style22'><?php if( get_option( 'ec_option_use_shipping' ) ){?><?php echo $order->shipping_phone; ?><?php }?></span></td>


        </tr>


      </table></td>


  </tr>


  <tr>


    <td width='269' align='left'>&nbsp;</td>


    <td width='80' align='center'>&nbsp;</td>


    <td width='91' align='center'>&nbsp;</td>


    <td align='center'>&nbsp;</td>


  </tr>


  <tr>


    <td width='269' align='left' bgcolor='#F3F1ED' class='style20'><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_details_header_1" ); ?></td>


    <td width='80' align='center' bgcolor='#F3F1ED' class='style20'><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_details_header_2" ); ?></td>


    <td width='91' align='center' bgcolor='#F3F1ED' class='style20'><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_details_header_3" ); ?></td>


    <td align='center' bgcolor='#F3F1ED' class='style20'><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_details_header_4" ); ?></td>


  </tr>


  <?php for( $i=0; $i < count( $order_details); $i++){ 


	


			$unit_price = $GLOBALS['currency']->get_currency_display( $order_details[$i]->unit_price );


			$total_price = $GLOBALS['currency']->get_currency_display( $order_details[$i]->total_price );


		


		?>


  <tr>


    <td width='269' class='style22'>


		<table>


            <tr><td>


            <?php echo $order_details[$i]->title; ?>


            </td></tr>
            
            <?php
	
			if( $order_details[$i]->use_advanced_optionset ){
				
				$advanced_options = $mysqli->get_order_options( $order_details[$i]->orderdetail_id );
				
				foreach( $advanced_options as $advanced_option ){
					
					if( $advanced_option->option_type == "file" ){
						
						$file_split = explode( "/", $advanced_option->option_value );
						echo "<tr><td><span class=\"ec_option_label\">" . $advanced_option->option_name . ":</span> <span class=\"ec_option_name\">" . $file_split[1] . $advanced_option->option_price_change . "</span></td></tr>";

					}else if( $advanced_option->option_type == "grid" ){

						echo "<tr><td><span class=\"ec_option_label\">" . $advanced_option->option_name . ":</span> <span class=\"ec_option_name\">" . $advanced_option->optionitem_name . " (" . $advanced_option->option_value . ")" . $advanced_option->option_price_change . "</span></td></tr>";

					}else{

						echo "<tr><td><span class=\"ec_option_label\">" . $advanced_option->option_name . ":</span> <span class=\"ec_option_name\">" . $advanced_option->option_value . $advanced_option->option_price_change . "</span></td></tr>";

					}

				}

			}else{

				if( $order_details[$i]->optionitem_name_1 ){

					echo "<tr><td><span class=\"ec_option_label\">" . $order_details[$i]->optionitem_label_1 . "</span>: <span class=\"ec_option_name\">" . $order_details[$i]->optionitem_name_1;

					echo "</span></td></tr>";

				}

				if( $order_details[$i]->optionitem_name_2 ){

					echo "<tr><td><span class=\"ec_option_label\">" . $order_details[$i]->optionitem_label_2 . "</span>: <span class=\"ec_option_name\">" . $order_details[$i]->optionitem_name_2;

					echo "</span></td></tr>";

				}

				if( $order_details[$i]->optionitem_name_3 ){

					echo "<tr><td><span class=\"ec_option_label\">" . $order_details[$i]->optionitem_label_3 . "</span>: <span class=\"ec_option_name\">" . $order_details[$i]->optionitem_name_3;

					echo "</span></td></tr>";

				}

				if( $order_details[$i]->optionitem_name_4 ){

					echo "<tr><td><span class=\"ec_option_label\">" . $order_details[$i]->optionitem_label_4 . "</span>: <span class=\"ec_option_name\">" . $order_details[$i]->optionitem_name_4;

					echo "</span></td></tr>";

				}

				if( $order_details[$i]->optionitem_name_5 ){

					echo "<tr><td><span class=\"ec_option_label\">" . $order_details[$i]->optionitem_label_5 . "</span>: <span class=\"ec_option_name\">" . $order_details[$i]->optionitem_name_5;

					echo "</span></td></tr>";

				}

			}// Close basic options
			?>


        </table>


	</td>


    <td width='80' align='center' class='style22'><?php echo $order_details[$i]->quantity; ?></td>


    <td width='91' align='center' class='style22'><?php echo $unit_price; ?></td>


    <td align='center' class='style22'><?php echo $total_price; ?></td>


  </tr>


  <?php }//end for loop ?>


  <tr>


    <td width='269'>&nbsp;</td>


    <td width='80' align='center'>&nbsp;</td>


    <td width='91' align='center'>&nbsp;</td>


    <td>&nbsp;</td>


  </tr>


  <tr>


    <td width='269'>&nbsp;</td>


    <td width='80' align='center' class='style22'>&nbsp;</td>


    <td width='91' align='center' class='style22'><strong><?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_order_totals_grand_total" ); ?></strong></td>


    <td align='center' class='style22'><strong><?php echo $total; ?></strong></td>


  </tr>


  <tr>


    <td colspan='4' class='style22'><p><br>


		<?php if( get_option( 'ec_option_user_order_notes' ) && strlen( $order->order_customer_notes ) > 0 ){ ?>


            <hr />


            <h4><?php echo $GLOBALS['language']->get_text( 'cart_payment_information', 'cart_payment_information_order_notes_title' ); ?></h4>


            <p><?php echo nl2br( $order->order_customer_notes ); ?></p>


            <br>


            <hr />


        <?php }?>


        <?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_bottom_line_1" ); ?><br>


        <br>


        <?php echo $GLOBALS['language']->get_text( "cart_success", "cart_payment_complete_bottom_line_2" ); ?></p>


      <p>&nbsp;</p></td>


  </tr>


</table>


</body>


</html>