<div class="ec_category_widget">
  
	<?php 
	for($i=0; $i<count( $categories ); $i++){
		if( $categories[$i][2] > 0 ){ ?>
			<div>
            	<a href="<?php echo $categories[$i][3]; ?>" class="menu_link"><?php if( ( $level == 0 && $menu_id == $categories[$i][0] ) || ( $level == 1 && $submenu_id == $categories[$i][0] ) || ( $level == 2 && $subsubmenu_id == $categories[$i][0] ) ){ echo "<b>"; } ?><?php echo $categories[$i][1]; ?><?php if( ( $level == 0 && $menu_id == $categories[$i][0] ) || ( $level == 1 && $submenu_id == $categories[$i][0] ) || ( $level == 2 && $subsubmenu_id == $categories[$i][0] ) ){ echo "</b>"; } ?></a>
            </div>
  <?php 
  		}
  	}
  
  	if( $level == 1 ){
		echo "<div><a href=\"" . $store_page . "\" class=\"menu_link\">" . $up_level_text . "</a></div>";
	}else if( $level == 2 ){
		if( $subsubmenu_id > 0 )
			$submenu_id = $mysqli->get_menulevel2_id_from_menulevel3( $subsubmenu_id );
		
		$menu_id = $mysqli->get_menulevel1_id_from_menulevel2( $submenu_id );
		
		$menurow = $mysqli->get_menu_row( $menu_id, 1 );
		$menu_permalink = get_permalink( $menurow->post_id );
		
		echo "<div><a href=\"" . $menu_permalink . "\" class=\"menu_link\">" . $up_level_text . "</a></div>";
	}
  ?>
  
</div>