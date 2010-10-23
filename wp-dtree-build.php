<?php
function wpdt_build_tree($nodelist, $args){ //internal
	if(!$nodelist || count($nodelist) < 1){ return '';	}	
	$wpdtreeopt = get_option('wpdt_options');	
	extract($args, EXTR_SKIP);
	unset($args);
	global $wpdt_tree_ids;  
	$openlink_title = esc_attr__($wpdtreeopt['openlink'], 'wpdtree');
	$closelink_title = esc_attr__($wpdtreeopt['closelink'], 'wpdtree');
	$openlink = esc_html__($wpdtreeopt['openlink'], 'wpdtree');
	$closelink = esc_html__($wpdtreeopt['closelink'], 'wpdtree');
	$blogpath = trailingslashit(get_bloginfo('url'));
	$tree = '';				
	$t = $treetype.$wpdt_tree_ids[$treetype]; //a unique handle for the tree.	
	if($oclinks){
		$tree .= "<span class='oclinks oclinks_{$treetype}' id='oclinks_{$t}'><a href='javascript:{$t}.openAll();' title='{$openlink_title}'>{$openlink}</a> | <a href='javascript:{$t}.closeAll();' title='{$closelink_title}'>{$closelink}</a></span>\n";			
	}
	$tree .= $wpdtreeopt['openscript'];	
	$title = esc_js($title);
	$tree .= "if(document.getElementById && document.getElementById('oclinks_{$t}')){document.getElementById('oclinks_{$t}').style.display = 'block';}\n";
	$tree .= "var {$t} = new wpdTree('{$t}', '{$blogpath}','{$truncate}');
{$t}.config.useLines={$uselines};
{$t}.config.useIcons={$useicons};
{$t}.config.closeSameLevel={$closelevels};
{$t}.config.folderLinks={$folderlinks};
{$t}.config.useSelection={$showselection};
{$t}.a(0,'root','{$title}','','','','');\n";		
	foreach($nodelist as $nodedata){		
		$nodedata['url'] = str_replace($blogpath, '', esc_url($nodedata['url'])); //make all path's relative, to save space.																
		$target = ($nodedata['target']) ? esc_js(esc_attr($nodedata['target'])) : '';
		$rsspath = ($showrss) ? esc_js(esc_url(wpdt_get_rss($nodedata, $treetype))) : '';				
		if((!$nodedata['title']) || ($nodedata['name'] == $nodedata['title'])){
			$nodedata['name'] = esc_js(esc_html($nodedata['name']));
			$nodedata['title'] = ''; //save space, let the javascript default title to name.
		}else{
			$nodedata['name'] = esc_js(esc_html(wpdt_truncate($nodedata['name'], $truncate)));
			$nodedata['title'] = esc_js(esc_attr($nodedata['title']));
		}		
		$tree .= "{$t}.a({$nodedata['id']},{$nodedata['pid']},'{$nodedata['name']}','{$nodedata['title']}','{$nodedata['url']}','{$target}','{$rsspath}');\n";		
	}		
	$tree .= "document.write({$t});\n";	
	if(strlen($opento)){//force open to
		$tree .= wpdt_force_open_to($opento, $t, $tree);			
	}
	$tree .= $wpdtreeopt['closescript'];
	unset($wpdtreeopt);
	unset($nodelist);
	return $tree; 
}

function wpdt_truncate($string, $max = 16, $replacement = '...'){
    if ($max < 1 || strlen($string) <= $max){ return $string; }
    $leave = $max - strlen($replacement);
    return substr_replace($string, $replacement, $leave);
}

function wpdt_get_rss($nodedata, $treetype){		
	$rsslink = '';
	$feedtype = 'rss2';		
	if($nodedata['id'] <= 0){					 		
		if(get_option('permalink_structure') == ''){
			$rsslink = '?feed='.$feedtype.'&'.$treetype.'='.($nodedata['id']-$idtranspose[$treetype]);	 		
		} else{				
			$path = str_replace(trailingslashit(get_bloginfo('url')), '', $nodedata['url']);			
			$rsslink = trailingslashit($path).'feed';			
		}		
	}
	return $rsslink;
}

function wpdt_force_open_to($opento, $tree_id, $treestring){ 
	$result = '';
	if(trim($opento) == 'all'){
		$result = $tree_id.".openAll();\n";
	} else {
		$requests = explode(',', $opento);		
		foreach($requests as $request){
			$result .= wpdt_open_tree_to($request, $tree_id, $treestring);
		}
		$result .= "\n/*WP-dTree: force open to: {$opento} */\n";					
	}	
	return $result;				
}

/* 	This function is hairy. It helps if you take a look at the JS-source in the HTML first. Here's one typical line:
		arc1.a(4695,2,'Post Title','','2010/10/post-title/','','');
	We're trying to find the node-ID (4695 in this case) corresponding to the requested URL. */
function wpdt_open_tree_to($request, $tree_id, $treestring){	
	if(strlen($treestring) < 1){return '';}
	if($tree_id == ''){	
		$opt = get_option('wpdt_options');		
		$s = stripos($treestring, 'var ', strlen($opt['openscript']))+4; // 4 = strlen('var ')
		$e = stripos($treestring, ' = new wpdTree', $s); //var {id} = new wpdTree
		$tree_id = substr($treestring, $s, $e-$s);		
	}
	if(is_numeric($request)){ //assume request was a node ID.
		return "$tree_id.openTo('$request', true);\n";
	}	
	
	//Okay, we were fed an URL. Let's clean it up to look like it would in the JS-source. 		
	$path = ltrim($request, '/'); 					//REQUEST_URI should be '/blog/category/post/' or somesuch. Remove leading slash.								  
	$blogurl = get_bloginfo('url'); 				//yields: http://blog.server.com, http://server.com/~userdir - you get the picture. 							
	if(empty($path) || $path == $blogurl){ 			//we've probably requested "home", so let's close the tree.
		return "{$tree_id}.closeAll();\n";
	}else if(strpos($path, $blogurl) === 0){		//REQUEST_URI included http://server.com/ (happens on some hosts)			
		$path = str_replace($blogurl, '', $path);	//all URLs are relative in the JS source (to save space), so let's get rid of the blog url.
	} else { 										//some servers (with userdir) gives us: '~userdir/blog/category/post/'				
		$segments = explode('/', $path); 			//$segments[0] could be '~userdir' or 'blog' now
		if(strpos($blogurl, $segments[0])!== false){//REQUEST_URI gave us the userdir - this is included in the blog url, so lets remove it. 
			$path = ltrim(str_replace($segments[0], '', $path));
		}		
	}
	if(empty($path)){return '';} 			//this should never happen, so let's handle it. :P	
	$path = "'".$path."'";					//the JS parameters are surrounded by '', so let's be explicit (avoid 2010/10 match with 2010/10/post-title)
	//Now to isolate the ID. First we find the line where it appears
	$parts = explode($path, $treestring); 	//split the script around the path, to immedietly narrow the search. (thus we know line is at the end of the first part)
	if(count($parts) < 2){
		return "/*WP-dTree: $tree_id request was {$path} */\n";	;
	}	
	$parts = $parts[0]; 					//we know line is at the end of the first part
	$needle = $tree_id.'.a(';
	$ls = false;
	if(version_compare(PHP_VERSION, '5.0.0', '<')) { //php 4.
		$ls = strlen($parts) - strpos(strrev($parts), strrev($needle)); //strrpos for PHP4 only supports single char needles... - strlen($needle)
	}else{
		$ls = strrpos($parts, $needle)+strlen($needle); //count backwards to the start of the line. will require only a dozen steps no matter how large the tree was
	}
	if($ls === false){return '';} 			//no linestart? preposterous.
	$le = stripos($parts, ',', $ls); 		//start at the 'tree#.a(' and find the first ',' (denoting end of the first parameter)
	if($le === false){return '';}			//no parameter list? wierd.
	$number = substr($parts, $ls, $le-$ls); //et voila! we have isolated the ID-parameter in the javascript.
	unset($parts);
	if(is_numeric($number)){		
		return "/*WP-dTree: $tree_id request was {$path}. I found: '".esc_js($number)."'*/\n\n{$tree_id}.openTo('{$number}', true);\n";
	}	 
	echo PHP_VERSION;
	return "/*WP-dTree: {PHP_VERSION} $tree_id request was {$path}. I found: ".esc_js($number)." */\n";	//if we get down here something was wrong. output some debug-info.
}

?>