<?php
function wp_dtree_get_pages_arr() {
	global $idtranspose;

	$wpdtreeopt = get_option('wp_dtree_options');
	$sortby = $wpdtreeopt['pgeopt']['sortby'];
	$sortorder = $wpdtreeopt['pgeopt']['sortorder'];
	$listchildpost = $wpdtreeopt['pgeopt']['listpost'];
	$postexclude = $wpdtreeopt['genopt']['exclude'];

	$args = "sort_column=".$sortby;
	$args .= "&sort_order=".$sortorder;
	if ( !empty($postexclude) ) {
		$args .= "&exclude=".$postexclude;
	}

	if ( !isset($idcount) ) {
		$idcount = 1;
	}

	$pageresults = &get_pages($args);
	if ( $pageresults ) {
		foreach ( $pageresults as $pageresult ) {
			$results[$idcount] = array( 'id' => $pageresult->ID + $idtranspose['pge'], 'pid' => $pageresult->post_parent + $idtranspose['pge'], 'name' => $pageresult->post_title, 'url' => get_permalink($pageresult->ID), 'title' => $pageresult->post_title);
			$idcount++;
		}
	}	 
	return wp_dtree_build_tree($results, 'pge');
}

function wp_dtree_get_pages() {
	global $wpdb, $wp_dtree_cache;   		
	$pgeresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'pge' ORDER BY id");	
	echo $pgeresults; 		
}
?>