<?php
function silpstream_wp_dtree_get_categories_arr() {
	global $wpdb, $idtranspose; //why do i need wp_query?

	$wpdtreeopt = get_option('wp_dtree_options');

	$sort_column = $wpdtreeopt['catopt']['sortby'];
	$sort_order = $wpdtreeopt['catopt']['sortorder'];
	$exclude = $wpdtreeopt['catopt']['exclude'];
	$hide_empty = $wpdtreeopt['catopt']['hideempty'];
	$listchildpost = $wpdtreeopt['catopt']['listpost'];

	$postexclude = $wpdtreeopt['genopt']['exclude'];

	if ( !isset($idcount) ) {
		$idcount = 1;
	}

	$exclusions = '';
	if ( !empty($exclude) ) {
		$excats = preg_split('/[\s,]+/',$exclude);
		if ( count($excats) ) {
			foreach ( $excats as $excat ) {
				$exclusions .= ' AND cat_ID <> ' . intval($excat) . ' ';
			}
		}
	}

	$sort_column = 'cat_'.$sort_column;

	$catresults = $wpdb->get_results("SELECT cat_ID, cat_name, category_parent, category_count"
							. " FROM ".$wpdb->categories
							. " WHERE cat_ID > 0 ".$exclusions
							. " ORDER BY ".$sort_column." ".$sort_order);

	foreach ( $catresults as $catresult ) {
		if ( !$hide_empty || $catresult->category_count ) {
			$results[$idcount] = array( 'id' => $catresult->cat_ID + $idtranspose['cat'], 'pid' => $catresult->category_parent + $idtranspose['cat'], 'name' => $catresult->cat_name, 'url' => get_category_link($catresult->cat_ID), 'title' => $catresult->cat_name);
			$idcount++;
		}
	}
	if ( $listchildpost ) {
		$postexclusions = '';
		if ( !empty($postexclude) ) {
			$exposts = preg_split('/[\s,]+/',$postexclude);
			if ( count($exposts) ) {
				foreach ( $exposts as $expost ) {
					$postexclusions .= ' AND '.$wpdb->posts.'.ID <> ' . intval($expost) . ' ';
				}
			}
		}

		$postresults = $wpdb->get_results("SELECT ".$wpdb->posts.".ID AS `id`, ".$wpdb->posts.".post_title AS `title`, ".$wpdb->post2cat.".category_id AS `catid`"
									. " FROM ".$wpdb->posts.", ".$wpdb->post2cat
									. " WHERE ".$wpdb->post2cat.".post_id = ".$wpdb->posts.".ID"
									. " AND ".$wpdb->posts.".post_status = 'publish'".$postexclusions
									. " ORDER BY ".$wpdb->posts.".post_date DESC");

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