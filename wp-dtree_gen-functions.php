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

function silpstream_wp_compare_to_uri($inuri) {
	$ruri = $_SERVER['REQUEST_URI'];        
	$server_url = "http://".$_SERVER['SERVER_NAME'];	
	$inuri = str_replace($server_url, "", $inuri);
	
	$inuri = trailingslashit($inuri); //this adds a trailing slash to the query, so for a proper compare
	$ruri = trailingslashit($ruri); //we've got to add one to ruri to.
	
	if ( $ruri == $inuri ) {
		return true;
	} else {
		return false;
	}
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