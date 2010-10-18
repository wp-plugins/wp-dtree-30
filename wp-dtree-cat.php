<?php
function wpdt_get_category_nodelist($args){	
	global $wpdb;	
	extract( $args, EXTR_SKIP );			
	$idcount = 1;
	$nodelist = array();
	$catresults = get_categories(array(
		'orderby' => $sortby, 'order' => $sort_order, 'taxonomy' => $taxonomy,
		'hide_empty' => $hide_empty, 'include_last_update_time' => $include_last_update_time, 'hierarchical' => 1, 
		'exclude' => $exclude, 
		'include' => $include, 
		'number' => $number, 
		'pad_counts' => $pad_counts, 
		'child_of' => $child_of
//		'parent' => $parent
	));			
	foreach ($catresults as $cat){						
		$nodelist[$idcount] = array( 
			'id' => -$cat->cat_ID, 
			'pid' => -$cat->category_parent,					 
			'url' => get_category_link($cat->term_id),
			'name' => ($showcount) ? strip_tags($cat->name ."&nbsp;({$cat->count})") : strip_tags($cat->name),
			'title' => strip_tags($cat->description)			
		);
		$idcount++;		
	}
	//categories can be arranged arbitrarily, and with some creative exlusion/inclusion, you'll easily create a tree without a single page connecting to root or a even parent.
	//thus this step to fixup any orphans.	
	foreach($nodelist as $key => $node){
		if($node['pid'] == 0){continue;} //connected to root.
		$hasparent = false;			
		foreach($nodelist as $potential_parent){
			if($potential_parent['id'] == $node['pid']){					
				$hasparent = true; break;
			}
		}
		if(!$hasparent){$nodelist[$key]['pid'] = 0;	} //connect orphans to root.
	}		
		
	if(!$listposts || !count($nodelist)){ //it's either empty or we don't need to list posts. Either way - skip the rest.		
		return $nodelist;
	}	
	unset($catresults);
	$postexclusions = wpdt_build_exclude_statement($postexclude, $wpdb->posts.'.ID');		
	$catexclusions = wpdt_build_exclude_statement($exclude, $wpdb->terms.'.term_id');			
	$groupby = (!$allowdupes) ? " GROUP BY {$wpdb->posts}.ID ": '';	
	$query = "SELECT {$wpdb->posts}.ID AS 'ID', {$wpdb->posts}.post_title AS 'post_title', {$wpdb->terms}.term_id AS 'catid' 
				 FROM {$wpdb->posts}, {$wpdb->terms}, {$wpdb->term_relationships}, {$wpdb->term_taxonomy} 
				 WHERE {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID
				 AND {$wpdb->term_taxonomy}.taxonomy = 'category' 
				 AND {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id
				 AND {$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id
				 AND {$wpdb->posts}.post_status = 'publish' 
				 AND {$wpdb->posts}.post_type = 'post' 
				{$catexclusions} 				
				{$postexclusions} 
				{$checkPostType}
				{$groupby}
				ORDER BY {$wpdb->posts}.{$cpsortby} {$cpsortorder}";	
	
	$postresults = (array)$wpdb->get_results($query);		
	foreach($postresults as $postresult){
		$text = strip_tags(apply_filters('the_title', $postresult->post_title));		
		$url = esc_url(get_permalink($postresult->ID));
		$nodelist[$idcount] = array(
			'id' => $postresult->ID, 
			'pid' => -$postresult->catid, 
			'name' => $text, 
			'url' => $url, 
			'title' => ''
		);
		$idcount++;	
	}
	unset($postresults);
	return $nodelist;
}
?>