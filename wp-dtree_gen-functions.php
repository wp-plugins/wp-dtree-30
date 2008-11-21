<?php

function wp_dtree_build_tree($results, $treetype){
	global $_curid;
	$idtranspose = wp_dtree_get_id_transpose();
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
	$blogpath = trailingslashit(get_bloginfo('url'));
	$tree = '';		
	
	$t = $treetype{0}; //get the first char of the treetype.		
	$tree .= "\n<span id=\"dtree" . $treetype . "wrapper\">\n";
	if( $oclink ){
		$tree .= "<span class=\"oclink\"><a href=\"javascript: " . $t . ".openAll();\">" . $openlink . "</a> | <a href=\"javascript: " . $t . ".closeAll();\">" . $closelink . "</a></span>\n";			
	}
	$tree .= "<script type=\"text/javascript\">\n";
	$tree .= "<!--\n"; //closed in the "gettreetype" functions, in case we need to add to the JS.	
	if( $results ){
		$tree .= "var " . $t . " = new wp_dTree('" . $t . "', '".$blogpath."','".$truncate."');\n";
		$tree .= $t . ".config.useLines=" . $useLines . ";\n";
		$tree .= $t . ".config.useIcons=" . $useIcons . ";\n";
		$tree .= $t . ".config.closeSameLevel=" . $cSameLevel . ";\n";
		$tree .= $t . ".config.folderLinks=" . $folderLinks . ";\n";
		$tree .= $t . ".config.useSelection=" . $useSelection . ";\n";
		$tree .= $t . ".a(" . $idtranspose[$treetype] . ",-1,'" . $topnode . "');\n";		
		foreach ($results as $nodedata){
			$nodedata['url'] = str_replace($blogpath, '', $nodedata['url']);										
			$tree .= wp_dtree_build_node($treetype, $nodedata);
		}		
		$tree .= "document.write(" . $t . ");\n";		
	}
	return $tree; 
}

function wp_dtree_build_node($treetype, $nodedata){			
	if(!is_array($nodedata)){
		return __('\n// WP-dTree WARNING: build_node failed.\n\n');		 		
	} 
	$wpdtreeopt = get_option('wp_dtree_options');
	$opttype = $treetype.'opt';
	$t = $treetype{0};	
	$count = "";
	$target = "";
	$rsspath = "";	
	
	if($wpdtreeopt[$opttype]['showcount']){				
		$count = wp_dtree_get_count($nodedata, $treetype);		
		if($count != ''){		
			$count = ",'".$count."'";
		}
	} 
	if($wpdtreeopt[$opttype]['showrss']){
		$rsspath = wp_dtree_get_rss($nodedata, $treetype);
		if($rsspath != ''){
			$rsspath = ",'".wp_dtree_get_rss($nodedata, $treetype)."'";
		}
	}	
	if($t == 'l' && $nodedata['target'] != ''){
		$target = ",'".$nodedata['target']."'"; //only keep a target attribute if we're building a link tree.
	}	
	
	if(($count != "" || $rsspath != "") && $target == ""){
		$target = ",''"; //keep track of empty parameters so we don't break the JS. 
	}	
	if($rsspath != "" && $count == ""){
		$count = ",''"; 
	}	
	$node = 	 $t.".a("
				.$nodedata['id'].","
				.$nodedata['pid'].","
				."'".addslashes(strip_tags($nodedata['title']))."',"
				."'".$nodedata['url']."'"
				.$target 
				.$count 
				.$rsspath							
				.");\n";
		
	return $node;		
}

function wp_dtree_get_rss($result, $treetype){	
	$idtranspose = wp_dtree_get_id_transpose();	
	$rsslink = '';
	$feedtype = "rss2";		
	if($result['id'] > $idtranspose[$treetype] && $result['id'] < $idtranspose[$treetype.'post'] ){					 		
		if(get_option('permalink_structure') == '' ){
			$rsslink = "?feed=".$feedtype."&".$treetype."=".($result['id']-$idtranspose[$treetype]);	 		
		} else{				
			$path = str_replace(trailingslashit(get_bloginfo('url')), "", $result['url']);			
			$rsslink = trailingslashit($path)."feed";			
		}		
	}
	return $rsslink;
}

function wp_dtree_get_count($nodedata, $treetype){	
	global $wpdb;		
	$idtranspose = wp_dtree_get_id_transpose();	
	$count = '';
	if($treetype == 'cat'){
		$catid = $nodedata['id']-$idtranspose['cat']; //DONT put this calculation in the parameter list. http://wordpress.org/support/topic/148638?replies=3
		$catobj = get_category($catid);
		$count = $catobj->category_count;
		$children = get_categories( //a roundabout way to get the padded count of this category...
			array(
				'type' => 'post', 
				'child_of' => $catid, 
				'orderby' => 'ID', 
				'order' => 'DESC', 
				'hide_empty' => false, 
				'include_last_update_time' => false,
				'hierarchical' => 1, 
				'exclude' => '', 
				'include' => '', 
				'number' => '', 
				'pad_counts' => 1
			)
		);										
		foreach($children as $child){
			$count += $child->category_count;
		}				
	} else if($treetype == 'arc'){
		$count .= $nodedata['post_count'];	
	}
	return $count; 
}

function wp_dtree_add_month($datestring, $nummonths='1'){
	$curtimestamp = strtotime($datestring);
	$newdatestring = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $curtimestamp)+$nummonths, date("d", $curtimestamp),  date("Y", $curtimestamp)));
	return $newdatestring;
}

//NOTE: since we save all permalinks without the blog url part, we must make sure any remnants of the blog url is cleaned from the incomming request.
function wp_dtree_open_tree_to($treetype, $treestring){			
	$path = ltrim($_SERVER['REQUEST_URI'], '/'); //REQUEST_URI should be '/blog/category/post/' or somesuch. Remove leading slash.
												//NOTE: some servers (with userdir) gives us: '/~userdir/blog/category/post/'	
	if(empty($path)){return '';} //quick bail
	$blogurl = get_bloginfo('url'); //http://blog.server.com, http://server.com/~userdir - you get the picture.									
	if(strpos($path, $blogurl) === 0){//REQUEST_URI included http://server.com/ (happens on some hosts)			
		$path = str_replace($blogurl, '', $path);//remove http://server.com/ from the request-path
	} else { 	//some servers (with userdir) gives us: '~userdir/blog/category/post/'				
		$segments = explode('/', $path); //$segments[0] could be '~userdir' or 'blog' now
		if(strpos($blogurl, $segments[0]) !== false){//REQUEST_URI gave us the userdir - this is included in the blog url, so lets remove it. 
			$path = ltrim(str_replace($segments[0], '', $path), '/');
		}		
	}
	$t = $treetype{0};	//first letter, from 'arc', 'cat', 'pge' or 'lnk'	
	$strings = explode(';', $treestring); //lots of cat.a('','','',''); statements
	foreach($strings as $string){
		if(substr_count($string, $path)){ //we know that this line holds the node id of our request.
			$params = explode(',', $string); //split it at parameter seperators 
			$number = str_replace($t.'.a(', '', $params[0]); //remove the leading c.a( to find the number.		
			return $t.'.openTo('.$number.', true);';						
		}
	}	
	return '//WP-dTree: request was '.$path;	
}

?>