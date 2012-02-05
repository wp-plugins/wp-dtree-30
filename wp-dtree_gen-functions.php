<?php
function silpstream_wp_dtree_create($results, $treetype) {
	global $idtranspose;

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
	
	if ( $results ) {
		echo "\n<div id=\"dtree" . $treetype . "wrapper\">\n";
		if ( $oclink ) {
			echo "<a href=\"javascript: " . $treetype . ".openAll();\">" . $openlink . "</a> | <a href=\"javascript: " . $treetype . ".closeAll();\">" . $closelink . "</a><br />\n";
			echo "<br />\n";
		}
		echo "<script type=\"text/javascript\">\n";
		echo "<!--\n";
		echo $treetype . " = new dTree('" . $treetype . "');\n";
		echo $treetype . ".config.useLines=" . $useLines . ";\n";
		echo $treetype . ".config.useIcons=" . $useIcons . ";\n";
		echo $treetype . ".config.closeSameLevel=" . $cSameLevel . ";\n";
		echo $treetype . ".config.folderLinks=" . $folderLinks . ";\n";
		echo $treetype . ".config.useSelection=" . $useSelection . ";\n";
		echo $treetype . ".add(" . $idtranspose[$treetype] . ",-1,'" . $topnode . "');\n";

		$curid = -1;

		foreach ( $results as $result ) {
			echo $treetype . ".add(" . $result['id'] . "," . $result['pid'] . ",'" . silpstream_truncate_string(addslashes(strip_tags($result['name'])), $truncate) . "','" . $result['url'] . "','" . addslashes(strip_tags($result['title'])) . "');\n";
			if ( silpstream_wp_compare_to_uri($result['url']) && $opentosel ) {
				$curid = $result['id'];				
			}
		}
		echo "document.write(" . $treetype . ");\n";
		if ( $curid >= 0 && $opentosel == 1 ) {
			echo $treetype . ".openTo(" . $curid . ", true);\n";
		}
		echo "//-->\n";
		echo "</script>\n";
		echo "</div>\n";
	}
}

/*
There are server setups where $_SERVER['SERVER_NAME'] has nothing to do with the URI of the resource a user is looking for. 
To deal with those special cases I added the explode-fix after the first comparison. What is does is simply strip out
the entire domain-part of the URL. Since I don't trust it entirely I've left the original checks intact.

The comments serve to illustrate such a special case.
*/	
function silpstream_wp_compare_to_uri($inuri) {
	$ruri = $_SERVER['REQUEST_URI'];        
	$server_url = "http://".$_SERVER['SERVER_NAME']; // http://phpbb3.gaurangapada.com
	$inuri = str_replace($server_url, "", $inuri);
	
	$inuri = trailingslashit($inuri); // http://nitaai.com/blog/index.php/2007/09/24/nitaaicom-portal-with-no-pageload/ 
	$ruri = trailingslashit($ruri); // 					  /blog/index.php/2007/09/24/nitaaicom-portal-with-no-pageload/ 
	if ( $ruri == $inuri ) {
		return true;
	} 
	
	// split the URL four times at the character "/". The third part should be "domain.com", and thus the fourth is the "domain less" part of the URI
	$pathparts = explode("/",$inuri, 4);  	
	//echo $pathparts[3]; // blog/index.php/2007/09/24/nitaaicom-portal-with-no-pageload/
	
	if ( $ruri == "/".$pathparts[3] ) {	
		return true; 
	} 	
	return false;	
}

function silpstream_truncate_string($str, $len='16') {
	if ( strlen($str) > $len ) {
		$str = substr($str, 0, $len)."...";
	}
	return $str;
}

function silpstream_add_month($datestring, $nummonths='1') {
	$curtimestamp = strtotime($datestring);
	$newdatestring = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m", $curtimestamp)+$nummonths, date("d", $curtimestamp),  date("Y", $curtimestamp)));
	return $newdatestring;
}
?>