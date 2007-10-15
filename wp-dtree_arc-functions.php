<?php

function wp_dtree_get_archives_arr() {
	global $month, $wpdb, $idtranspose;
	$now = current_time('mysql');
	$results = array();
	$wpdtreeopt = get_option('wp_dtree_options');
	$arctype = $wpdtreeopt['arcopt']['arctype'];
	$listchildpost = $wpdtreeopt['arcopt']['listpost'];
	$postexclude = $wpdtreeopt['genopt']['exclude'];

	if ( !isset($idcount) ) {
		$idcount = 1;
	}
	if ( !isset($mpidcount) ) {
		$mpidcount = 0;
	}
	if ( !isset($pidcount) ) {
		$pidcount = 0;
	}

	$checkPostType = " AND post_type = 'post'"; //OR post_type = 'page'
	$postexclusions = '';
	if ( !empty($postexclude) ) {
		$exposts = preg_split('/[\s,]+/',$postexclude);
		if ( count($exposts) ) {
			foreach ( $exposts as $expost ) {
				$postexclusions .= ' AND ID <> ' . intval($expost) . ' ';
			}
		}
	}	  
	
	$arcresults = $wpdb->get_results(
		"SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts"
		. " FROM ".$wpdb->posts
		. " WHERE post_date < '".$now."'"
		. " AND post_status = 'publish'"
		.$postexclusions
		.$checkPostType
		. " GROUP BY YEAR(post_date), MONTH(post_date)"
		. " ORDER BY post_date DESC"
	);

	if ( $arcresults ) {
		$curyear = -1;
		foreach ( $arcresults as $arcresult ) {
			if ( 'yearly' == $arctype ) {
				if ( $curyear != $arcresult->year ) {
					$results[$idcount] = array( 
						'id' => $idcount + $idtranspose['arc'], 
						'pid' => 0 + $idtranspose['arc'], 
						'name' => $arcresult->year, 
						'url' => get_year_link($arcresult->year), 
						'title' => $arcresult->year
					);					
					$mpidcount = $idcount;
					$idcount++;
					$curyear = $arcresult->year;
				}
			}

			if ( 'yearly' != $arctype ) {
				$name_title = $month[zeroise($arcresult->month, 2)]." ".$arcresult->year;
			} else {
				$name_title = $month[zeroise($arcresult->month, 2)];
			}
			
			$results[$idcount] = array( 
				'id' => $idcount + $idtranspose['arc'], 
				'pid' => $mpidcount + $idtranspose['arc'], 
				'name' => $name_title, 
				'url' => get_month_link($arcresult->year, $arcresult->month), 
				'title' => $name_title
			);
			$pidcount = $idcount;
			$idcount++;

			if ( $listchildpost ) {
				$startmonth = $arcresult->year."-".zeroise($arcresult->month, 2)."-01 00:00:00";
				$endmonth = wp_dtree_add_month($startmonth, 1);
				$postresults = $wpdb->get_results(
							"SELECT ID AS `ID`, post_date AS `post_date`, post_title AS `post_title`"
							. " FROM ".$wpdb->posts
							. " WHERE post_date > '".$startmonth."'"
							. " AND post_date < '".$endmonth."'"
							. " AND post_status = 'publish'"
							.$postexclusions
							.$checkPostType
							. " ORDER BY post_date DESC"
				);

				if ( $postresults ) {
					foreach ( $postresults as $postresult ) {
						$results[$idcount] = array( 
							'id' => $idcount + $idtranspose['arcpost'], 
							'pid' => $pidcount + $idtranspose['arc'], 
							'name' => $postresult->post_title, 
							'url' => get_permalink($postresult->ID), 
							'title' => $postresult->post_title
						);
						$idcount++;
					}
				}
			}
		}
	}	 
	return wp_dtree_build_tree($results, 'arc');
}

function wp_dtree_get_archives() {	
	global $wpdb, $wp_dtree_cache;	
 	$arcresults = $wpdb->get_var("SELECT content FROM ". $wp_dtree_cache . " WHERE treetype = 'arc' ORDER BY id");	
 	echo $arcresults; 	
}
?>