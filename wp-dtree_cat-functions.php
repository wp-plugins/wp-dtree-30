<?php
function wp_dtree_get_categories_arr(){
	global $wpdb, $wp_version;
	$idtranspose = wp_dtree_get_id_transpose(); 
	$wpdtreeopt = get_option('wp_dtree_options');
	$sort_column = $wpdtreeopt['catopt']['sortby']; //ID or name
	$sort_order = $wpdtreeopt['catopt']['sortorder']; //ASC or DESC
	$cpsortby = $wpdtreeopt['catopt']['cpsortby']; //ID, post_name, post_date
	$cpsortorder = $wpdtreeopt['catopt']['cpsortorder']; //ID or name
	$excludedcats = $wpdtreeopt['catopt']['exclude']; //Excluded category id's
	$hide_empty = $wpdtreeopt['catopt']['hideempty']; //true or false (eg. 0 / 1)
	$listchildpost = $wpdtreeopt['catopt']['listpost']; //show posts under category
	$countSubCatsPost = true;
	$allowDupes = true; //wether posts are allowed to show up under more than one category.
	$postexclude = $wpdtreeopt['catopt']['postexclude']; //excluded post's ID.

	if( !isset($idcount) ){ $idcount = 1; }	
	( !empty($excludedcats) ) ? $catexclusions = $excludedcats : $catexclusions = '';	
	
	$catresults = get_categories(
		array(
			'type' => 'post', 
			'child_of' => 0, 
			'orderby' => $sort_column, 
			'order' => $sort_order, 
			'hide_empty' => $hide_empty, 
			'include_last_update_time' => false,
			'hierarchical' => 1, 
			'exclude' => $catexclusions, 
			'include' => '', 
			'number' => '', 
			'pad_counts' => $countSubCatsPost
		)	
	);						
						
	foreach ($catresults as $catresult){		
		if(!$hide_empty || $catresult->category_count){			
			$results[$idcount] = array( 
				'id' => $catresult->cat_ID + $idtranspose['cat'], 
				'pid' => $catresult->category_parent + $idtranspose['cat'],					 
				'url' => get_category_link($catresult->cat_ID), 
				'title' => __($catresult->cat_name)					
			);
			$idcount++;
		}		
	}
	
	if(!$listchildpost || !count($catresults)){ //it's either empty or we don't need to list posts. Either way - skip the rest.		
		return wp_dtree_build_tree($results, 'cat');
	}	
	
	$postexclusions = '';	
	$exposts = preg_split('/[\s,]+/',$postexclude);
	if( count($exposts) ){
		foreach ( $exposts as $expost ){
			$postexclusions .= ' AND '.$wpdb->posts.'.ID != ' . intval($expost) . ' ';
		}
	}	
	
	$catexclusions = '';
	$excats = preg_split('/[\s,]+/', $excludedcats);
	if(count($excats)){
		foreach($excats as $excat){
			$catexclusions .= " AND ".$wpdb->terms.".term_id != ".intval($excat) . ' ';
		}
	}
			
	$checkPostType = " AND ".$wpdb->posts.".post_type = 'post'"; //OR ".$wpdb->posts.".post_type = 'page'  
	
	if($wp_version < '2.3') {
		$query = "SELECT ".$wpdb->posts.".ID AS `id`, ".$wpdb->posts.".post_title AS `title`, ".$wpdb->post2cat.".category_id AS `catid`"
				." FROM ".$wpdb->posts.", ".$wpdb->post2cat
				." WHERE ".$wpdb->post2cat.".post_id = ".$wpdb->posts.".ID"
				." AND ".$wpdb->posts.".post_status = 'publish'"
				.$postexclusions
				.$checkPostType
				." ORDER BY ".$wpdb->posts.".$cpsortby $cpsortorder";
	} else{			
		$query = "SELECT ".$wpdb->posts.".ID AS 'id', ".$wpdb->posts.".post_title AS 'title', ".$wpdb->terms.".term_id AS 'catid'" 
				." FROM ".$wpdb->posts.", ".$wpdb->terms.", ".$wpdb->term_relationships.", ".$wpdb->term_taxonomy 
				." WHERE ".$wpdb->term_relationships.".object_id = ".$wpdb->posts.".ID"
				." AND ".$wpdb->term_taxonomy.".taxonomy = 'category' "
				." AND ".$wpdb->term_relationships.".term_taxonomy_id = ".$wpdb->term_taxonomy.".term_taxonomy_id"
				." AND ".$wpdb->term_taxonomy.".term_id = ".$wpdb->terms.".term_id"					
				." AND ".$wpdb->posts.".post_status = 'publish'"
				.$catexclusions										
				.$postexclusions
				.$checkPostType
				." ORDER BY ".$wpdb->posts.".$cpsortby $cpsortorder";	
	}
	$postresults = (array)$wpdb->get_results($query);	

	//NOTE: $allowDupes and $unique is a super ugly hack to filter out posts in multiple categories.
	//TODO: find out how to build the SQL-query to perform this for us.	
	$unique = array();
	foreach($postresults as $postresult){
		if($allowDupes || !isset($unique[$postresult->id])){
			$results[$idcount] = array('id' => $postresult->id + $idtranspose['catpost'], 'pid' => $postresult->catid + $idtranspose['cat'], 'name' => __($postresult->title), 'url' => get_permalink($postresult->id), 'title' => __($postresult->title));
			$idcount++;
		}
		$unique[$postresult->id] = $postresult->id;
	}
	
	return wp_dtree_build_tree($results, 'cat'); //a single humongus string, making up the entire tree.
}

function wp_dtree_get_categories(){	
	$wpdtreeopt = get_option('wp_dtree_options');
	if($wpdtreeopt['catopt']['isdisabled']){
		print('<p> WP-dTree '. wp_dtree_get_version() .'; the category tree has been <font color="orange">DISABLED</font> from admin. Did you forget to unload the widget? </p>');		
		return;
	}
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();			
	$catresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'cat' ORDER BY id");	
	print("\n<!-- WP-dTree ". wp_dtree_get_version() .", cat tree: " . strlen($catresults) . " chars. -->");	
	if(!strlen($catresults)){return;}
	echo $catresults;	
	if($wpdtreeopt['catopt']['opentosel'] && isset($_SERVER['REQUEST_URI'])){
		echo wp_dtree_open_tree_to($_SERVER['REQUEST_URI'], 'cat', $catresults);
	} 	
	echo "//-->\n";		
	echo "</script>\n";		
	echo "</span>\n";	
}



?>