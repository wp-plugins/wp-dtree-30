<?php
function wp_dtree_get_categories_arr() {
	global $wpdb, $idtranspose, $wp_version;
	$wpdtreeopt = get_option('wp_dtree_options');
	$sort_column = $wpdtreeopt['catopt']['sortby']; //ID or name
	$sort_order = $wpdtreeopt['catopt']['sortorder']; //ASC or DESC
	$exclude = $wpdtreeopt['catopt']['exclude']; //Excluded category id's
	$hide_empty = $wpdtreeopt['catopt']['hideempty']; //true or false (eg. 0 / 1)
	$listchildpost = $wpdtreeopt['catopt']['listpost']; //show posts under category
	$postexclude = $wpdtreeopt['genopt']['exclude']; //excluded post's ID.

	if ( !isset($idcount) ) {
		$idcount = 1;
	} 
	
	$exclusions = '';
	if ( !empty($exclude) ) {
		$exclusions = $exclude;	
	}	
	
	$catresults = get_categories(
		array(
			'type' => 'post', 
			'child_of' => 0, 
			'orderby' => $sort_column, 
			'order' => $sort_order, 
			'hide_empty' => true, 
			'include_last_update_time' => false,
			'hierarchical' => 1, 
			'exclude' => $exclusions, 
			'include' => '', 
			'number' => '', 
			'pad_counts' => false
		)	
	);						
						
	foreach ( $catresults as $catresult ) {		
		if ( !$hide_empty || $catresult->category_count ) {			
			$results[$idcount] = array( 
				'id' => $catresult->cat_ID + $idtranspose['cat'], 
				'pid' => $catresult->category_parent + $idtranspose['cat'],					 
				'url' => get_category_link($catresult->cat_ID), 
				'title' => $catresult->cat_name					
			);
			$idcount++;
		}		
	}
	
	if ( $listchildpost) {
		$postexclusions = '';
		if ( !empty($postexclude) ) {
			$exposts = preg_split('/[\s,]+/',$postexclude);
			if ( count($exposts) ) {
				foreach ( $exposts as $expost ) {
					$postexclusions .= ' AND '.$wpdb->posts.'.ID != ' . intval($expost) . ' ';
				}
			}
		}		
		
		$checkPostType = " AND ".$wpdb->posts.".post_type = 'post'"; //OR ".$wpdb->posts.".post_type = 'page'  
		
		if ((float)$wp_version < 2.3)  { 
			$query = "SELECT ".$wpdb->posts.".ID AS `id`, ".$wpdb->posts.".post_title AS `title`, ".$wpdb->post2cat.".category_id AS `catid`"
					." FROM ".$wpdb->posts.", ".$wpdb->post2cat
					." WHERE ".$wpdb->post2cat.".post_id = ".$wpdb->posts.".ID"
					." AND ".$wpdb->posts.".post_status = 'publish'"
					.$postexclusions
					.$checkPostType
					." ORDER BY ".$wpdb->posts.".post_date DESC";
		} else {			
			$query = "SELECT ".$wpdb->posts.".ID AS 'id', ".$wpdb->posts.".post_title AS 'title', ".$wpdb->terms.".term_id AS 'catid'" 
					." FROM ".$wpdb->posts.", ".$wpdb->terms.", ".$wpdb->term_relationships.", ".$wpdb->term_taxonomy 
					." WHERE ".$wpdb->term_relationships.".object_id = ".$wpdb->posts.".ID"
					." AND ".$wpdb->term_taxonomy.".taxonomy = 'category' "
					." AND ".$wpdb->term_relationships.".term_taxonomy_id = ".$wpdb->term_taxonomy.".term_taxonomy_id"
					." AND ".$wpdb->term_taxonomy.".term_id = ".$wpdb->terms.".term_id"
					." AND ".$wpdb->terms.".term_id != ".$exclusions
					." AND ".$wpdb->posts.".post_status = 'publish'"					
					.$postexclusions
					.$checkPostType
					." ORDER BY ".$wpdb->posts.".post_date DESC";	
		}
		$postresults  = (array)$wpdb->get_results($query);			
		foreach ( $postresults as $postresult ) {
			$results[$idcount] = array( 'id' => $postresult->id + $idtranspose['catpost'], 'pid' => $postresult->catid + $idtranspose['cat'], 'name' => $postresult->title, 'url' => get_permalink($postresult->id), 'title' => $postresult->title);
			$idcount++;
		}
	}
	return wp_dtree_build_tree($results, 'cat'); //a single humongus string, making up the entire tree.
}

function wp_dtree_get_categories() {	
	global $wpdb, $wp_dtree_cache;		
	$wpdtreeopt = get_option('wp_dtree_options');
	$catresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'cat' ORDER BY id");	
	if($catresults){
		print("\n<!-- cat tree: " . strlen($catresults) . " chars. -->");
		echo $catresults;	
		if($wpdtreeopt['catopt']['opentosel'] && isset($_SERVER['REQUEST_URI'])){
			echo wp_dtree_open_cat_to($catresults);
		} 	
		echo "//-->\n";
		echo "</script>\n";
		echo "</div>\n";
	}	
}

function wp_dtree_open_cat_to($catstring) {
	$ruri = $_SERVER['REQUEST_URI']; 
	$path = str_replace(get_bloginfo('url'), "", $ruri);	
	$path = ltrim($path, '/');
	$ruri = ltrim($ruri, '/');	
	if($path == "/" || empty($path) || empty($ruri)) {
		return ""; 
	}
	$strings = explode(";", $catstring); //lots of cat.a('','','',''); statements
	foreach ($strings as $string){
		if(substr_count ($string, $path)){ //we know that this line holds the node id of our request.
			$params = explode(",", $string); //split it at parameter seperators 
			$number = str_replace('c.a(', "", $params[0]); //remove the leading cat.a( to find the number.		
			return 'c.openTo('.$number.', true);';			
		}
	}
	return '';	
}

?>