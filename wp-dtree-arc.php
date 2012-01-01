<?php
function wpdt_get_archive_nodelist($args){ //get archive nodelist	
	global $month, $wpdb, $wp_locale;	
	extract( $args, EXTR_SKIP );			
	$isyearly = ($type == 'yearly');	
	$idcount = 1; 
	$mpidcount = 0; 	
	$count = ($showcount) ? ", count({$wpdb->posts}.ID) AS 'posts' ": '';
	$limit = ($limit_posts > 0) ? " LIMIT {$limit_posts}" : '';
	$postexclusions = wpdt_build_exclude_statement($exclude, $wpdb->posts.'.ID');
	$from = " FROM {$wpdb->posts} ";
	$catexclusions = '';
	if($exclude_cats){
		$catexclusions = " AND {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID
		 AND {$wpdb->term_taxonomy}.taxonomy = 'category' 
		 AND {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id 
		 AND {$wpdb->term_taxonomy}.term_id NOT IN ({$exclude_cats}) ";
		 $from .= ", {$wpdb->term_relationships}, {$wpdb->term_taxonomy} ";
	}	
	$query = "SELECT YEAR({$wpdb->posts}.post_date) AS 'year', MONTH({$wpdb->posts}.post_date) AS 'month' {$count}
		 $from  
		 WHERE {$wpdb->posts}.post_type = 'post' AND {$wpdb->posts}.post_status = 'publish' 
		 {$postexclusions}		 
		 {$catexclusions} 
		 GROUP BY year, month 
		 ORDER BY {$wpdb->posts}.post_date DESC";
	$arcresults = $wpdb->get_results($query);		

	if(!$arcresults){
		return array(); //just create empty tree and bail
	}	
	
	$nodelist = array();
	$curyear = -1;
	$query = array();	
	//var_dump($arcresults);
	foreach($arcresults as $arcresult){		
		if($isyearly){			
			if($arcresult->year != $curyear){ //prepare the year as a parent node, countings it's children etc.
				$postcount = 0;					
				if($showcount){ //avoid this loop if not needed!					
					foreach($arcresults as $temp){ if($temp->year == $arcresult->year){$postcount +=  $temp->posts; } }
				}
				$nodelist[$idcount] = array( 
					'id' => -$idcount, 
					'pid' => 0,						 
					'url' => get_year_link($arcresult->year), 
					'name' => ($showcount) ? $arcresult->year ."&nbsp;($postcount)" : $arcresult->year,
					'title' => ''									
				);					
				$mpidcount = -$idcount;
				$idcount++;
				$curyear = $arcresult->year;
			}			
		}		
		$nodelist[$idcount] = array( 
			'id' => -$idcount, 
			'pid' => $mpidcount,				 
			'url' => get_month_link($arcresult->year, $arcresult->month), 
			'name' => sprintf(__('%1$s %2$d'), $wp_locale->get_month($arcresult->month), $arcresult->year),
			'title' => ''
		);		
		if($showcount){
			$nodelist[$idcount]['name'] .= "&nbsp;({$arcresult->posts})";
		}		
		$pidcount = -$idcount;//by keeping parent ID's < 0, we're effeciently avoiding ID-trampling between posts and our generated year/month ID's
		$idcount++;
		if(!$listposts){
			continue; //nothing more to do, get back to the top
		}
		$startmonth = $arcresult->year."-".zeroise($arcresult->month, 2)."-01 00:00:00";
		$endmonth = wpdt_add_month($startmonth, 1);		
		$query[] = "(SELECT ID AS 'ID', post_date AS 'post_date', post_title AS 'post_title', {$pidcount} AS 'pID'  
			 {$from}
			 WHERE post_type = 'post' AND post_status = 'publish' 
				AND post_date > '{$startmonth}'
				AND post_date < '{$endmonth}'	
			 {$postexclusions}	
			 {$catexclusions} 						
			 $limit)";				
	}
	unset($arcresults);	
	if($listposts && count($query)){
		$query = (count($query) > 1) ? implode(' UNION ALL ', $query)." ORDER BY {$sortby} {$sort_order}" : $query[0]." ORDER BY {$sortby} {$sort_order}";		
		if($postresults = $wpdb->get_results($query)){
			foreach($postresults as $postresult){			
				$text = strip_tags(apply_filters('the_title', $postresult->post_title));			
				$url = get_permalink($postresult->ID);
				$nodelist[$idcount] = array( 
					'id' => $postresult->ID,
					'pid' => $postresult->pID, 
					'name' => $text,
					'url' => $url, 
					'title' => ''
				);
				$idcount++;
			}		
			unset($postresults);		
		}				
	}	
	return $nodelist;
}
function wpdt_add_month($datestring, $nummonths='1'){
	$curtimestamp = strtotime($datestring);
	$newdatestring = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $curtimestamp)+$nummonths, date("d", $curtimestamp),  date("Y", $curtimestamp)));
	return $newdatestring;
}
?>