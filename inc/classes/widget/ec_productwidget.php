<?php 
class ec_productwidget extends WP_Widget{
	
	function ec_productwidget(){
		$widget_ops = array('classname' => 'ec_productwidget', 'description' => 'Displays a single product from WP EasyCart' );
		$this->WP_Widget('ec_productwidget', 'WP EasyCart Single Product Display', $widget_ops);
	}
	
	function form($instance){
		if( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}else {
			$title = __( 'Hot Item', 'text_domain' );
		}
		
		if( isset( $instance[ 'model_number' ] ) ) {
			$model_number = $instance[ 'model_number' ];
		}else {
			$model_number = __( '', 'text_domain' );
		}
		
		echo "<p><label for=\"" . $this->get_field_name( 'title' ) . "\">" . _e( 'Title:' ) . "</label><input class=\"widefat\" id=\"" . $this->get_field_id( 'title' ) . "\" name=\"" . $this->get_field_name( 'title' ) . "\" type=\"text\" value=\"" . esc_attr( $title ) . "\" /></p>";
		
		$mysqli = new ec_db( );
		$product_list = $mysqli->get_product_list( " WHERE product.activate_in_store = 1", " ORDER BY product.title ASC", "", "" );
		
		echo "<p><label for=\"" . $this->get_field_name( 'model_number' ) . "\">" . _e( 'Product:' ) . "</label><select class=\"widefat\" id=\"" . $this->get_field_id( 'model_number' ) . "\" name=\"" . $this->get_field_name( 'model_number' ) . "\">";
		
		foreach( $product_list as $product ){
			echo "<option value=\"" . $product['model_number'] . "\"";
			if( esc_attr( $model_number ) == $product['model_number'] )
				echo " selected=\"selected\"";
			echo ">" . $product['title'] . "</option>";
		}
		echo "</select>";
		
		$defaults = array();
		$instance = wp_parse_args( (array) $instance, $defaults);
	}
	
	function update($new_instance, $old_instance){
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['model_number'] = ( !empty( $new_instance['model_number'] ) ) ? strip_tags( $new_instance['model_number'] ) : '';

		return $instance;
	}
	
	
	function widget($args, $instance){
	
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$model_number = apply_filters( 'widget_model_number', $instance['model_number'] );
		
		// Translate if Needed
		$title = $GLOBALS['language']->convert_text( $title );
	
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
			
		// WIDGET CODE GOES HERE
		$mysqli = new ec_db( );
		$products = $mysqli->get_product_list( " WHERE product.model_number = '" . $model_number . "'", "", "", "" );
		if( count( $products ) > 0 ){
			$product = new ec_product( $products[0], 0, 0, 1 );
			
			if( file_exists( WP_PLUGIN_DIR . '/wp-easycart-data/design/layout/' . get_option( 'ec_option_base_layout' ) . '/ec_product_widget.php' ) )	
				include( WP_PLUGIN_DIR . "/wp-easycart-data/design/layout/" . get_option( 'ec_option_base_layout' ) . "/ec_product_widget.php");
			else
				include( WP_PLUGIN_DIR . "/" . EC_PLUGIN_DIRECTORY . "/design/layout/" . get_option( 'ec_option_latest_layout' ) . "/ec_product_widget.php");
		}
		
		echo $after_widget;
	}
 
}
?>