<?php
function wp_dtree_get_links_arr(){
	global $wpdb, $wp_version;
	$idtranspose = wp_dtree_get_id_transpose(); 
	$wpdtreeopt = get_option('wp_dtree_options');
	$sort_column = $wpdtreeopt['lnkopt']['sortby']; //ID, name, rating etc.
	$sort_order = $wpdtreeopt['lnkopt']['sortorder']; //ASC or DESC
	$cats_orderby = (!isset($wpdtreeopt['lnkopt']['catsorder']) ? 'name' : $wpdtreeopt['lnkopt']['catsorder']);//Can be name, count, or nothing (will use term_id).
	$exclude = '';//$wpdtreeopt['lnkopt']['exclude'];
	$include = '';
	$name_like = '';
	if( !isset($idcount) ){ $idcount = 1; }		
	
	
	
	$cats = get_terms('link_category', array(
		'name__like' => $name_like, 
		'include' => $include, 
		'exclude' => $exclude, 
		'orderby' => $cats_orderby, 
		'order' => $sort_order, 
		'hierarchical' => 0)
	);
	
	foreach($cats as $cat){			
		$results[$idcount] = array( 
			'id' => $cat->term_id + $idtranspose['lnk'], 
			'pid' => $cat->parent+$idtranspose['lnk'],					 
			'url' => "", 
			'title' => __($cat->name)					
		);
		$idcount++;
		$lnkresults = get_bookmarks(array(
			'orderby' => $sort_column, //catsorder: 'name', 'slug', 'id', 'count',
			'order' => $sort_order,
			'limit' => -1, 
			'category' => $cat->term_id,
			'category_name' => $cat->name, 
			'hide_invisible' => 1,
			'show_updated' => 0, 
			'include' => $include,
			'exclude' => $exclude)
		);						
		foreach( $lnkresults as $lnkresult ){		
			if( !$hide_empty || $lnkresult->lnkegory_count ){			
				$results[$idcount] = array( 
					'id' => $lnkresult->link_id + $idtranspose['lnkpost'], 
					'pid' => $cat->term_id + $idtranspose['lnk'],					 
					'url' => $lnkresult->link_url, 
					'title' => __($lnkresult->link_name),
					'target' => $lnkresult->link_target				
				);
				$idcount++;
			}				
		}			
	}		
	return wp_dtree_build_tree($results, 'lnk');
}

function wp_dtree_get_links(){
	$wpdtreeopt = get_option('wp_dtree_options');
	if($wpdtreeopt['lnkopt']['isdisabled']){
		print('<p> WP-dTree '. wp_dtree_get_version() .'; the link tree has been <font color="orange">DISABLED</font> from admin. Did you forget to unload the widget? </p>');		
		return;
	}	
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();			
	$lnkresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'lnk' ORDER BY id");	
	print("\n<!-- WP-dTree ". wp_dtree_get_version() .", lnk tree: " . strlen($lnkresults) . " chars. -->");	
	if(!strlen($lnkresults)){return;}
	echo $lnkresults;	
	echo "//-->\n";		
	echo "</script>\n";		
	echo "</span>\n";	
}
?>