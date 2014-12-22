<?php
function wpdt_get_menu_nodelist($args){	
	extract($args, EXTR_SKIP);		
	$idcount = 1;
	$nodelist = array();	
	$menu_items = wp_get_nav_menu_items($menuslug);
	if(!$menu_items){
		return $nodelist;
	}
	foreach((array)$menu_items as $key => $menu_item){
		$pid = $menu_item->menu_item_parent;
		$hasparent = false;			
		foreach($menu_items as $key => $potential_parent){
			if($potential_parent->ID == $pid){					
				$hasparent = true; break;
			}
		}
		if(!$hasparent){$pid = 0;} //connect orphans to root.
		$nodelist[$idcount] = array(
			'id' => $menu_item->ID, 
			'pid' => $pid, 
			'url' => esc_url($menu_item->url), 
			'name' => strip_tags(apply_filters('the_title', $menu_item->title)), 
			'title' => strip_tags($menu_item->description)
		);
		$idcount++;		
	}	
	return $nodelist;	
}
/*if(($locations = get_nav_menu_locations()) && isset($locations[$menu_name])){
		$menu = wp_get_nav_menu_object($locations[$menu_name]);*/
?>