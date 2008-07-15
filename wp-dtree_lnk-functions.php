<?php
function wp_dtree_get_links_arr() {
	global $wpdb, $wp_version;
	$idtranspose = wp_dtree_get_id_transpose(); 
	$wpdtreeopt = get_option('wp_dtree_options');
	$sort_column = $wpdtreeopt['lnkopt']['sortby']; //ID, name, rating etc.
	$sort_order = $wpdtreeopt['lnkopt']['sortorder']; //ASC or DESC
	$cats_orderby = (!isset($wpdtreeopt['lnkopt']['catsorder']) ? 'name' : $wpdtreeopt['lnkopt']['catsorder']);
	
	if ( !isset($idcount) ) { $idcount = 1; }	
	( !empty($excludedlnks) ) ? $lnkexclusions = $excludedlnks : $lnkexclusions = '';	
	
	$cats = get_terms('link_category', array(
		'name__like' => '', 
		'include' => '', 
		'exclude' => '', 
		'orderby' => $cats_orderby, //catsorder: 'name', 'slug', 'id', 'count',
		'order' => $sort_order, 
		'hierarchical' => 0)
	);
	
	foreach($cats as $cat){			
		$results[$idcount] = array( 
			'id' => $cat->term_id + $idtranspose['lnk'], 
			'pid' => $cat->parent+$idtranspose['lnk'],					 
			'url' => "", 
			'title' => $cat->name					
		);
		$idcount++;
		$lnkresults = get_bookmarks(array(
			'orderby' => $sort_column, 
			'order' => $sort_order,
			'limit' => -1, 
			'category' => $cat->term_id,
			'category_name' => $cat->name, 
			'hide_invisible' => 1,
			'show_updated' => 0, 
			'include' => '',
			'exclude' => '')
		);						
		foreach( $lnkresults as $lnkresult ) {		
			if ( !$hide_empty || $lnkresult->lnkegory_count ) {			
				$results[$idcount] = array( 
					'id' => $lnkresult->link_id + $idtranspose['lnkpost'], 
					'pid' => $cat->term_id + $idtranspose['lnk'],					 
					'url' => $lnkresult->link_url, 
					'title' => $lnkresult->link_name					
				);
				$idcount++;
			}				
		}			
	}		
	return wp_dtree_build_tree($results, 'lnk');
}

function wp_dtree_get_links() {	
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();		
	$wpdtreeopt = get_option('wp_dtree_options');
	$lnkresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'lnk' ORDER BY id");	
	print("\n<!-- WP-dTree 3.4, lnk tree: " . strlen($lnkresults) . " chars. -->");	
	if(!strlen($lnkresults)){return;}
	echo $lnkresults;	
	echo "//-->\n";		
	echo "</script>\n";		
	echo "</span>\n";	
}
?>