<?php
function wp_dtree_get_pages_arr(){
	$idtranspose = wp_dtree_get_id_transpose();
	$wpdtreeopt = get_option('wp_dtree_options');
	$sortby = $wpdtreeopt['pgeopt']['sortby'];
	$sortorder = $wpdtreeopt['pgeopt']['sortorder']; //'post_title', 'menu_order', 'post_date', 'post_modified', 'ID', 'post_author', 'post_name' (name == slug)
	$listchildpost = $wpdtreeopt['pgeopt']['listpost'];
	$postexclude = $wpdtreeopt['pgeopt']['exclude'];
	$authors = '';
	$include = '';
	$child_of = 0;
	$meta_key = '';
	$meta_value = '';  
	if(!isset($idcount)){
		$idcount = 1;
	}
	
	$pageresults = &get_pages(array(
		'child_of' => $child_of, 'sort_order' => $sortorder,
		'sort_column' => $sortby, 'hierarchical' => 0,
		'exclude' => $postexclude, 'include' => $include,
		'meta_key' => $meta_key, 'meta_value' => $meta_value,
		'authors' => $authors)
	);			
	if( $pageresults ){
		foreach ( $pageresults as $pageresult ){
			$results[$idcount] = array( 'id' => $pageresult->ID + $idtranspose['pge'], 'pid' => $pageresult->post_parent + $idtranspose['pge'], 'url' => get_permalink($pageresult->ID), 'title' => addslashes(__($pageresult->post_title)));
			$idcount++;
		}
	}	 
	return wp_dtree_build_tree($results, 'pge');
}

function wp_dtree_get_pages(){
	$wpdtreeopt = get_option('wp_dtree_options');
	if($wpdtreeopt['pgeopt']['isdisabled']){
		print('<p> WP-dTree '. wp_dtree_get_version() .'; the page tree has been <font color="orange">DISABLED</font> from admin. Did you forget to unload the widget? </p>');		
		return;
	}
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name(); 
	$wpdtreeopt = get_option('wp_dtree_options');  		
	$pgeresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'pge' ORDER BY id");	
	print("\n<!-- WP-dTree ". wp_dtree_get_version() .", pge tree: " . strlen($pgeresults) . " chars. -->");
	if(!strlen($pgeresults)){return;}	
	echo $pgeresults;	
	if($wpdtreeopt['pgeopt']['opentosel'] && isset($_SERVER['REQUEST_URI'])){	
		echo wp_dtree_open_tree_to($_SERVER['REQUEST_URI'],'pge',$pgeresults);	
	} 		
	echo "//-->\n";	
	echo "</script>\n";	
	echo "</span>\n";				
}

?>