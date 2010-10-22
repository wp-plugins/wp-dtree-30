<?php
function wpdt_get_archive_nodelist($args){ //get archive nodelist	
	global $month, $wpdb, $wp_locale;	
	extract( $args, EXTR_SKIP );			
	$isyearly = ($type == 'yearly');	
	$idcount = 1; //idcount (negated) is an index for the parent nodes. 
	$mpidcount = 0; //month's parent id index.	
	$pidcount = 0; //"parent id" index
	$postexclusions = wpdt_build_exclude_statement($exclude, $wpdb->posts.'.ID');		
	$arcresults = $wpdb->get_results(
		"SELECT YEAR(post_date) AS 'year', MONTH(post_date) AS 'month', count(ID) AS 'posts'
		 FROM {$wpdb->posts}
		 WHERE post_type = 'post' AND post_status = 'publish' 
		 {$postexclusions}
		 GROUP BY YEAR(post_date), MONTH(post_date)
		 ORDER BY post_date DESC"
	);		
	if(!$arcresults){
		return wpdt_build_tree(array(), 'arc'); //just create empty tree and bail
	}	
	$nodelist = array();
	$curyear = -1;
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
		$pidcount = -$idcount;
		$idcount++;

		if(!$listposts){
			continue; //nothing more to do, get back to the top
		}
		$startmonth = $arcresult->year."-".zeroise($arcresult->month, 2)."-01 00:00:00";
		$endmonth = wpdt_add_month($startmonth, 1);
		$postresults = $wpdb->get_results(
			"SELECT ID AS 'ID', post_date AS 'post_date', post_title AS 'post_title'
			 FROM {$wpdb->posts}
			 WHERE post_type = 'post' AND post_status = 'publish' 
				AND post_date > '{$startmonth}'
				AND post_date < '{$endmonth}'	
			 {$postexclusions}							
			 ORDER BY {$sortby} {$sort_order}"
		);
		if(!$postresults ){	continue;} 
		foreach ( $postresults as $postresult ){
			$text = strip_tags(apply_filters('the_title', $postresult->post_title));			
			$url = get_permalink($postresult->ID);
			$nodelist[$idcount] = array( 
				'id' => $postresult->ID, //add arbitrary letter, to avoid ID-collisions.
				'pid' => $pidcount, 
				'name' => $text,
				'url' => $url, 
				'title' => ''
			);
			$idcount++;
		}
		unset($postresults);			
	}	
	unset($arcresults);	
	return $nodelist;
}
function wpdt_add_month($datestring, $nummonths='1'){
	$curtimestamp = strtotime($datestring);
	$newdatestring = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $curtimestamp)+$nummonths, date("d", $curtimestamp),  date("Y", $curtimestamp)));
	return $newdatestring;
}
?>