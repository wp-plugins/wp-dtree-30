<?php

function wp_dtree_get_archives_arr(){
	global $month, $wpdb;
	$idtranspose = wp_dtree_get_id_transpose();
	$now = current_time('mysql');
	$results = array();
	$wpdtreeopt = get_option('wp_dtree_options');
	$arctype = $wpdtreeopt['arcopt']['arctype'];
	$listchildpost = $wpdtreeopt['arcopt']['listpost'];
	$sort_column = $wpdtreeopt['arcopt']['sortby']; //ID, post_name, post_date
	$sort_order = $wpdtreeopt['arcopt']['sortorder']; //ASC or DESC
	$postexclude = $wpdtreeopt['arcopt']['exclude'];

	if( !isset($idcount) ){
		$idcount = 1;
	}
	if( !isset($mpidcount) ){
		$mpidcount = 0;
	}
	if( !isset($pidcount) ){
		$pidcount = 0;
	}

	$checkPostType = " AND post_type = 'post'"; //OR post_type = 'page'
	$postexclusions = '';
	if( !empty($postexclude) ){
		$exposts = preg_split('/[\s,]+/',$postexclude);
		if( count($exposts) ){
			foreach ( $exposts as $expost ){
				$postexclusions .= ' AND ID <> ' . intval($expost) . ' ';
			}
		}
	}	  
	
	$arcresults = $wpdb->get_results(
		"SELECT DISTINCT YEAR(post_date) AS 'year', MONTH(post_date) AS 'month', count(ID) AS 'posts'"
		. " FROM ".$wpdb->posts
		. " WHERE post_date < '".$now."'"
		. " AND post_status = 'publish'"
		.$postexclusions
		.$checkPostType
		. " GROUP BY YEAR(post_date), MONTH(post_date)"
		. " ORDER BY post_date DESC"
	);

	if($arcresults){
		$curyear = -1;
		foreach($arcresults as $arcresult){
			if( $arctype == 'yearly' ){
				if( $arcresult->year != $curyear ){
					$postcount = 0;
					foreach($arcresults as $temp){
						if($temp->year == $arcresult->year){
							$postcount +=  $temp->posts;
						}
					}
					
					$results[$idcount] = array( 
						'id' => $idcount + $idtranspose['arc'], 
						'pid' => 0 + $idtranspose['arc'],						 
						'url' => get_year_link($arcresult->year), 
						'title' => __($arcresult->year),
						'post_count' => $postcount
					);					
					$mpidcount = $idcount;
					$idcount++;
					$curyear = $arcresult->year;
				}
			}

			if( $arctype != 'yearly' ){
				$name_title = $month[zeroise($arcresult->month, 2)]." ".$arcresult->year;
			} else{
				$name_title = $month[zeroise($arcresult->month, 2)];
			}
			
			$results[$idcount] = array( 
				'id' => $idcount + $idtranspose['arc'], 
				'pid' => $mpidcount + $idtranspose['arc'],				 
				'url' => get_month_link($arcresult->year, $arcresult->month), 
				'title' => __($name_title),
				'post_count' => $arcresult->posts
			);
			$pidcount = $idcount;
			$idcount++;

			if( $listchildpost ){
				$startmonth = $arcresult->year."-".zeroise($arcresult->month, 2)."-01 00:00:00";
				$endmonth = wp_dtree_add_month($startmonth, 1);
				$postresults = $wpdb->get_results(
							"SELECT ID AS 'ID', post_date AS 'post_date', post_title AS 'post_title'"
							. " FROM ".$wpdb->posts
							. " WHERE post_date > '".$startmonth."'"
							. " AND post_date < '".$endmonth."'"
							. " AND post_status = 'publish'"
							.$postexclusions
							.$checkPostType
							. " ORDER BY $sort_column $sort_order"
				);

				if( $postresults ){
					foreach ( $postresults as $postresult ){
						$results[$idcount] = array( 
							'id' => $idcount + $idtranspose['arcpost'], 
							'pid' => $pidcount + $idtranspose['arc'], 
							'name' => __($postresult->post_title), 
							'url' => get_permalink($postresult->ID), 
							'title' => __($postresult->post_title)
						);
						$idcount++;
					}
				}
			}
		}
	}	
	return wp_dtree_build_tree($results, 'arc');
}
	
function wp_dtree_get_archives(){
	$wpdtreeopt = get_option('wp_dtree_options');
	if($wpdtreeopt['arcopt']['isdisabled']){
		print('<p> WP-dTree '. wp_dtree_get_version() .'; the archive tree has been <font color="orange">DISABLED</font> from admin. Did you forget to unload the widget? </p>');		
		return;
	}	
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();	
	$wpdtreeopt = get_option('wp_dtree_options');	
	$arcresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'arc' ORDER BY id");		 	
	print("\n<!-- WP-dTree ". wp_dtree_get_version() .", arc tree: " . strlen($arcresults) . " chars. -->");
	if(!strlen($arcresults)){return;}		
 	echo $arcresults;	
 	if($wpdtreeopt['arcopt']['opentosel'] && isset($_SERVER['REQUEST_URI'])){
		echo wp_dtree_open_arc_to($arcresults);
 	}	
	echo "//-->\n";
	echo "</script>\n";
	echo "</span>\n";	
	
}

function wp_dtree_open_arc_to($arcstring){
	$ruri = $_SERVER['REQUEST_URI']; 
	$path = str_replace(get_bloginfo('url'), "", $ruri);	
	$path = ltrim($path, '/');
	$ruri = ltrim($ruri, '/');	
	if($path == '/' || empty($path) || empty($ruri)){
		return ''; 
	}
	$strings = explode(";", $arcstring); //lots of arc.a('','','',''); statements
	foreach ($strings as $string){
		if(substr_count ($string, $path)){ //we know that this line holds the node id of our request.
			$params = explode(",", $string); //split it at parameter seperators 
			$number = str_replace('a.a(', "", $params[0]); //remove the leading arc.a( to find the number.		
			return 'a.openTo(' . $number . ', true);';			
		}
	}
	return '';	
}
?>