<?php

function wp_dtree_build_tree($results, $treetype) {
	global $idtranspose, $_curid;

	$wpdtreeopt = get_option('wp_dtree_options');
	$opttype = $treetype."opt";
	$truncate = $wpdtreeopt[$opttype]['truncate'];
	$oclink = $wpdtreeopt[$opttype]['oclink'];
	$useLines = $wpdtreeopt[$opttype]['uselines'];
	$useIcons = $wpdtreeopt[$opttype]['useicons'];
	$cSameLevel = $wpdtreeopt[$opttype]['closelevels'];
	$folderLinks = $wpdtreeopt[$opttype]['folderlinks'];
	$useSelection = $wpdtreeopt[$opttype]['useselection'];
	$topnode = $wpdtreeopt[$opttype]['topnode'];
	$opentosel = $wpdtreeopt[$opttype]['opentosel'];
	$openlink = $wpdtreeopt['genopt']['openlink'];
	$closelink = $wpdtreeopt['genopt']['closelink'];	

	$tree = '';
	if ( $results ) {
		$t = $treetype{0}; //get the first char of the treetype.		
		$tree .= "\n<div id=\"dtree" . $treetype . "wrapper\">\n";
		if ( $oclink ) {
			$tree .= "<a href=\"javascript: " . $t . ".openAll();\">" . $openlink . "</a> | <a href=\"javascript: " . $t . ".closeAll();\">" . $closelink . "</a>\n";			
			$tree .= "<br /><br />"; //gives us some spacing from the oclinks. Not a good solution, varies across browsers and so on...
		}
		$tree .= "<script type=\"text/javascript\">\n";
		$tree .= "<!--\n";
		$tree .= "var " . $t . " = new dTree('" . $t . "', '".trailingslashit(get_bloginfo('url'))."');\n";
		$tree .= $t . ".config.useLines=" . $useLines . ";\n";
		$tree .= $t . ".config.useIcons=" . $useIcons . ";\n";
		$tree .= $t . ".config.closeSameLevel=" . $cSameLevel . ";\n";
		$tree .= $t . ".config.folderLinks=" . $folderLinks . ";\n";
		$tree .= $t . ".config.useSelection=" . $useSelection . ";\n";
		$tree .= $t . ".a(" . $idtranspose[$treetype] . ",-1,'" . $topnode . "');\n";		
		foreach ($results as $nodedata) {										
			$tree .= wp_dtree_build_node($treetype, $nodedata, $truncate);
		}		
		$tree .= "document.write(" . $t . ");\n";	
		return $tree;	
	}	
}

/*sets _curid to the currently selected node if any (to use if open to selection is on)*/
function wp_dtree_build_node($treetype, $nodedata, $truncate)
{
	$node = '';		
	if (!is_array($nodedata)) {
		return __("\n// WP-dTree WARNING: build_node failed.\n\n");		 		
	} 
	$wpdtreeopt = get_option('wp_dtree_options');
	$opttype = $treetype."opt";
	$t = $treetype{0};
	//$opentosel = $wpdtreeopt[$opttype]['opentosel'];	
	
	($wpdtreeopt[$opttype]['showcount'] && wp_dtree_get_count($nodedata, $treetype)	)	? $count = ",'".wp_dtree_get_count($nodedata, $treetype)."'" 	: $count = "";
	($wpdtreeopt[$opttype]['showrss'] 	&& wp_dtree_get_rss($nodedata, $treetype)	) 	? $rsspath = ",'".wp_dtree_get_rss($nodedata, $treetype)."'"	: $rsspath = "";	
	if($rsspath != "" && $count == ""){
		$count = ",''"; //add an empty parameter before the rsspath, or we'll get strange counts indeed... :)
	}
	
	global $_curid;				
	$path = str_replace(trailingslashit(get_bloginfo('url')), "", $nodedata['url']); 
	$node .= 	 $t.".a("
				.$nodedata['id'].","
				.$nodedata['pid'].","
				."'".addslashes(strip_tags($nodedata['title']))."',"
				."'".$path."'" 
				.$count 
				.$rsspath							
				.");\n";	
	/* Useless, since our entire tree is now statically stored. This must be handled  at the time that the tree is printed.*/
	  /*if ( wp_dtree_compare_to_uri($nodedata['url']) && $opentosel == 1 ) {		
		$_curid = $nodedata['id'];	
	}*/
	return $node;		
}

function wp_dtree_get_rss($result, $treetype) {	
	global $idtranspose;	
	$rsslink = '';
	$feedtype = "rss2";		
	if($result['id'] > $idtranspose[$treetype] && $result['id'] < $idtranspose[$treetype.'post'] ) {					 		
		if (get_option('permalink_structure') == '' ) {
			$rsslink = "?feed=".$feedtype."&".$treetype."=".($result['id']-$idtranspose[$treetype]);	 		
		} else {				
			$path = str_replace(trailingslashit(get_bloginfo('url')), "", $result['url']);			
			$rsslink = trailingslashit($path)."feed";			
		}		
	}
	return $rsslink;
}

function wp_dtree_get_count($nodedata, $treetype){	
	global $idtranspose, $wpdb;			
	$count = "";
	if($treetype == 'cat'){
		$catobj = get_category($nodedata['id']-$idtranspose['cat']);
		$count .= $catobj->category_count;
	} else if($treetype == 'arc'){
		$count .= $nodedata['post_count'];	
	}
	return $count; 
}

function wp_dtree_add_month($datestring, $nummonths='1') {
	$curtimestamp = strtotime($datestring);
	$newdatestring = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $curtimestamp)+$nummonths, date("d", $curtimestamp),  date("Y", $curtimestamp)));
	return $newdatestring;
}
?>