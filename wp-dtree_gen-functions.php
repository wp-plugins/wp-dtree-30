<?php
$_curid = -1; //the currently open node

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
		$tree .= "\n<div id=\"dtree" . $treetype . "wrapper\">\n";
		if ( $oclink ) {
			$tree .= "<a href=\"javascript: " . $treetype . ".openAll();\">" . $openlink . "</a> | <a href=\"javascript: " . $treetype . ".closeAll();\">" . $closelink . "</a><br />\n";
			$tree .= "<br />\n";
		}
		$tree .= "<script type=\"text/javascript\">\n";
		$tree .= "<!--\n";
		$tree .= $treetype . " = new dTree('" . $treetype . "');\n";
		$tree .= $treetype . ".config.useLines=" . $useLines . ";\n";
		$tree .= $treetype . ".config.useIcons=" . $useIcons . ";\n";
		$tree .= $treetype . ".config.closeSameLevel=" . $cSameLevel . ";\n";
		$tree .= $treetype . ".config.folderLinks=" . $folderLinks . ";\n";
		$tree .= $treetype . ".config.useSelection=" . $useSelection . ";\n";
		$tree .= $treetype . ".add(" . $idtranspose[$treetype] . ",-1,'" . $topnode . "');\n";		
		foreach ($results as $nodedata) {										
			$tree .= wp_dtree_build_node($treetype, $nodedata, $truncate);
		}		
		$tree .= "document.write(" . $treetype . ");\n";
		if ( $_curid >= 0 && $opentosel == 1 ) {
			$tree .= $treetype . ".openTo(" . $_curid . ", true);\n";
		}
		$tree .= "//-->\n";		
		$tree .= "</script>\n";		
		$tree .= "</div>\n";		
		return $tree;	
	}	
}

/*sets _curid to the currently selected node if any (to use if open to selection is on)*/
function wp_dtree_build_node($treetype, $nodedata, $truncate)
{
	$node = '';		
	if (!is_array($nodedata)) {
		return __("\n// WP-dTree WARNING: print_node failed.\n\n");		 		
	} 
	$wpdtreeopt = get_option('wp_dtree_options');
	$opttype = $treetype."opt";
	$opentosel = $wpdtreeopt[$opttype]['opentosel'];
	global $_curid;	
	$rssicon = wp_dtree_get_cat_rss($nodedata);
	$shorttitle = wp_dtree_truncate_string(addslashes(strip_tags($nodedata['name'])), $truncate);				
	$node .= 	 $treetype.".add("
				.$nodedata['id'].","
				.$nodedata['pid'].","
				."'".$shorttitle . wp_dtree_get_cat_count($nodedata['id']) . $rssicon."',"
				."'".$nodedata['url']."',"
				."'".addslashes(strip_tags($nodedata['title']))
				."');\n";	
	/* Useless, since our entire tree is now statically stored. This must be handled  at the time that the tree is printed.*/
	  /*if ( wp_dtree_compare_to_uri($nodedata['url']) && $opentosel == 1 ) {		
		$_curid = $nodedata['id'];	
	}*/
	return $node;		
}

/*decides what feed we want, wether the link needs a picture and so on and so forth.
id is the transposed ID we've stored, not a WP-valid ID. Valid feed tyes: rss, rss2, rdf, atom*/
function wp_dtree_get_cat_rss($result)
{
	global $idtranspose;	
	$rsslink = '';
	$wpdtreeopt = get_option('wp_dtree_options');	
	if($wpdtreeopt['catopt']['showrss'])
	{	
		if($result['id'] > $idtranspose['cat'] && $result['id'] < $idtranspose['catpost'] ){						
			$feedtype = "rss2";			 		
			if (get_option('permalink_structure') == '' ) {
				$rsslink = "<a class=\"catrss\" style=\"padding-right:15px\" href=\"".get_option('home')."?feed=".$feedtype."&cat=".($result['id']-$idtranspose['cat'])."\"></a>";	 		
			} else {				
				$rsslink = "<a class=\"catrss\" style=\"padding-right:15px\" href=\"".trailingslashit($result['url'])."feed\"></a>";	//Depends on URL holding a trailing /		
			}		
		}
	}
	return $rsslink;
}


function wp_dtree_get_cat_count($id)
{
	global $idtranspose;
	$wpdtreeopt = get_option('wp_dtree_options');		
	$count = '';
	if($wpdtreeopt['catopt']['showcount']) { //IF SHOW COUNTS
		$catobj = get_category($id-$idtranspose['cat']);
		if($catobj->category_count)	{
			$count = "</a><div id=\"postcount\">"." (".$catobj->category_count.")</div><a>"; //closes the cat-name link, and opens a new tag to match the one outputed in the javatree.
		}
	}
	return $count;
}

/*
There are server setups where $_SERVER['SERVER_NAME'] has nothing to do with the URI of the resource a user is looking for. 
To deal with those special cases I added the explode-fix after the first comparison. What is does is simply strip out
the entire domain-part of the URL. Since I don't trust it entirely I've left the original checks intact.

The comments serve to illustrate such a special case.
*/	
function wp_dtree_compare_to_uri($inuri) {
	$ruri = $_SERVER['REQUEST_URI'];        
	$server_url = "http://".$_SERVER['SERVER_NAME']; // http://phpbb3.gaurangapada.com
	$inuri = str_replace($server_url, "", $inuri);	
	$inuri = trailingslashit($inuri); // http://nitaai.com/blog/index.php/2007/09/24/nitaaicom-portal-with-no-pageload/ 
	$ruri = trailingslashit($ruri); // 					  /blog/index.php/2007/09/24/nitaaicom-portal-with-no-pageload/ 	
	if ( $ruri == $inuri ) {
		return true; //original test. 
	} 	
	// split the URL four times at the character "/". The third part should be "domain.com", and thus the fourth is the "domain less" part of the URI
	$pathparts = explode("/",$inuri, 4);  	
	//echo $pathparts[3]; // blog/index.php/2007/09/24/nitaaicom-portal-with-no-pageload/	
	if ( isset($pathparts[3]) && $pathparts[3] != '' && $ruri == "/".$pathparts[3] ) {			
		return true; 
	} 	
	return false;	
}

function wp_dtree_truncate_string($str, $len='16') {
	if ( strlen($str) > $len ) {
		$str = substr($str, 0, $len)."...";
	}
	return $str;
}

function wp_dtree_add_month($datestring, $nummonths='1') {
	$curtimestamp = strtotime($datestring);
	$newdatestring = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $curtimestamp)+$nummonths, date("d", $curtimestamp),  date("Y", $curtimestamp)));
	return $newdatestring;
}
?>