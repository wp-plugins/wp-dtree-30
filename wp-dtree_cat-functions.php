<?php
function silpstream_wp_dtree_get_categories_arr() {
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
		
	$params = array('type' => 'post', 'child_of' => 0, 'orderby' => $sort_column, 'order' => $sort_order, 'hide_empty' => true, 'include_last_update_time' => false, 'hierarchical' => 1, 'exclude' => $exclusions, 'include' => '', 'number' => '', 'pad_counts' => false);
	$catresults = get_categories($params);						
							
	foreach ( $catresults as $catresult ) {
		if(strtolower($catresult->cat_name) != "uncategorized")	{
			if ( !$hide_empty || $catresult->category_count ) {			
				$results[$idcount] = array( 'id' => $catresult->cat_ID + $idtranspose['cat'], 'pid' => $catresult->category_parent + $idtranspose['cat'], 'name' => $catresult->cat_name, 'url' => get_category_link($catresult->cat_ID), 'title' => $catresult->cat_name);
				$idcount++;
			}
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
		
		if ((float)$wp_version < 2.3)  {
			$query = "SELECT ".$wpdb->posts.".ID AS `id`, ".$wpdb->posts.".post_title AS `title`, ".$wpdb->post2cat.".category_id AS `catid`"
					." FROM ".$wpdb->posts.", ".$wpdb->post2cat
					." WHERE ".$wpdb->post2cat.".post_id = ".$wpdb->posts.".ID"
					." AND ".$wpdb->posts.".post_status = 'publish'"
					.$postexclusions
					//. " AND ".$wpdb->posts.".post_type != 'page'" //hide pages
					." ORDER BY ".$wpdb->posts.".post_date DESC";
		} else {			
			$query = "SELECT ".$wpdb->posts.".ID AS 'id', ".$wpdb->posts.".post_title AS 'title', ".$wpdb->terms.".term_id AS 'catid'" 
					." FROM ".$wpdb->posts.", ".$wpdb->terms.", ".$wpdb->term_relationships.", ".$wpdb->term_taxonomy 
					." WHERE ".$wpdb->term_relationships.".object_id = ".$wpdb->posts.".ID"
					." AND ".$wpdb->term_taxonomy.".taxonomy = 'category' "
					." AND ".$wpdb->term_relationships.".term_taxonomy_id = ".$wpdb->term_taxonomy.".term_taxonomy_id"
					." AND ".$wpdb->term_taxonomy.".term_id = ".$wpdb->terms.".term_id"
					." AND ".$wpdb->posts.".post_status = 'publish'"
					//. " AND ".$wpdb->posts.".post_type != 'page'" //hide pages
					.$postexclusions
					." ORDER BY ".$wpdb->posts.".post_date DESC";	
		}
		$postresults  = (array)$wpdb->get_results($query);		
		
		foreach ( $postresults as $postresult ) {
			$results[$idcount] = array( 'id' => $postresult->id + $idtranspose['catpost'], 'pid' => $postresult->catid + $idtranspose['cat'], 'name' => $postresult->title, 'url' => get_permalink($postresult->id), 'title' => $postresult->title);
			$idcount++;
		}
	}	
	return $results;
}

function silpstream_wp_dtree_get_categories() {	
	global $wpdb, $wp_dtree_cache;	
	$catresults = $wpdb->get_var("SELECT categories_arr FROM ". $wp_dtree_cache . " WHERE id=0");	
	silpstream_wp_dtree_create(unserialize(base64_decode($catresults)), 'cat');			
}
?>