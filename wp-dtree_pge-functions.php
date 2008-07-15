<?php
function wp_dtree_get_pages_arr() {
	$idtranspose = wp_dtree_get_id_transpose();
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
			$results[$idcount] = array( 'id' => $pageresult->ID + $idtranspose['pge'], 'pid' => $pageresult->post_parent + $idtranspose['pge'], 'url' => get_permalink($pageresult->ID), 'title' => $pageresult->post_title);
			$idcount++;
		}
	}	 
	return wp_dtree_build_tree($results, 'pge');
}

function wp_dtree_get_pages() {
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name(); 
	$wpdtreeopt = get_option('wp_dtree_options');  		
	$pgeresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'pge' ORDER BY id");	
	print("\n<!-- WP-dTree 3.4, pge tree: " . strlen($pgeresults) . " chars. -->");
	if(!strlen($pgeresults)){return;}	
	echo $pgeresults;	
	if($wpdtreeopt['pgeopt']['opentosel'] && isset($_SERVER['REQUEST_URI'])){	
		echo wp_dtree_open_pages_to($pgeresults);	
	} 		
	echo "//-->\n";	
	echo "</script>\n";	
	echo "</span>\n";				
}

function wp_dtree_open_pages_to($pgestring) {
	$ruri = $_SERVER['REQUEST_URI']; 
	$path = str_replace(get_bloginfo('url'), "", $ruri);	
	$path = ltrim($path, '/');
	$ruri = ltrim($ruri, '/');	
	if($path == "/" || empty($path) || empty($ruri)) {
		return ""; 
	}
	$strings = explode(";", $pgestring); //lots of cat.a('','','',''); statements
	foreach ($strings as $string){
		if(substr_count ($string, $path)){ //we know that this line holds the node id of our request.
			$params = explode(",", $string); //split it at parameter seperators 
			$number = str_replace('p.a(', "", $params[0]); //remove the leading arc.a( to find the number.		
			return 'p.openTo(' . $number . ', true);';			
		}
	}
	return '';	
}
?>