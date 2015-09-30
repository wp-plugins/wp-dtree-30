<?php
function wpdt_build_tree($nodelist, $args){ //internal
	if(!$nodelist || count($nodelist) < 1){ return '';	}	
	$wpdtreeopt = get_option('wpdt_options');	
	extract($args, EXTR_SKIP);
	unset($args);
	global $wpdt_tree_ids;  
	$openlink_title = esc_attr__($openlink, 'wp-dtree-30');
	$closelink_title = esc_attr__($closelink, 'wp-dtree-30');
	$openlink = esc_html__($openlink, 'wp-dtree-30');
	$closelink = esc_html__($closelink, 'wp-dtree-30');
	$separator = esc_html__($oclink_sep, 'wp-dtree-30');
	$blogpath = trailingslashit(get_bloginfo('url'));	 
	$addhome = false; //seriously stupid idea. just add a filter to the widget-output instead.
	if($addhome){		
		$home_node = array( 'id' => 3.14, 'pid' => 0, 'url' => esc_url(get_home_url()), 
				'name' => esc_js(esc_html(get_bloginfo('name'))), 
				'title' => esc_js(esc_attr(get_bloginfo('description'))));
		array_unshift($nodelist, $home_node);
	}
	$tree = '';				
	$t = $treetype.$wpdt_tree_ids[$treetype]; //a unique handle for the tree.		
	$tree .= ($openlink || $closelink) ? "<span class='oclinks oclinks_{$treetype}' id='oclinks_{$t}'>" : '';
	$tree .= ($openlink) ? "<a href='javascript:{$t}.openAll();' title='{$openlink_title}'>{$openlink}</a>" : '';
	$tree .= ($separator && $openlink && $closelink)? "<span class='oclinks_sep oclinks_sep_{$treetype}' id='oclinks_sep_{$t}'>{$separator}</span>" : '';
	$tree .= ($closelink)? "<a href='javascript:{$t}.closeAll();' title='{$closelink_title}'>{$closelink}</a>" : '';			
	$tree .= ($openlink || $closelink) ? "</span>\n" : '';
	$tree .= ($wpdtreeopt['openscript']) ? $wpdtreeopt['openscript'] : "<script type='text/javascript'>"; //this happens for some reason?
	$tree .= "if(document.getElementById && document.getElementById('oclinks_{$t}')){document.getElementById('oclinks_{$t}').style.display = 'block';}\n";
	$tree .= "var {$t} = new wpdTree('{$t}', '{$blogpath}','{$truncate}');
{$t}.config.useLines={$uselines};
{$t}.config.useIcons={$useicons};
{$t}.config.closeSameLevel={$closelevels};
{$t}.config.folderLinks={$folderlinks};
{$t}.config.useSelection={$showselection};
{$t}.a(0,'root','','','','','');\n";

	foreach($nodelist as $nodedata){		
		$nodedata['url'] = str_replace($blogpath, '', esc_url($nodedata['url'])); //make all path's relative, to save space.																
		$target = (!empty($nodedata['target'])) ? esc_js(esc_attr($nodedata['target'])) : '';
		$rsspath = (isset($showrss) && ($showrss)) ? esc_js(wpdt_get_rss($nodedata, $treetype)) : '';				
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
	$tree .= ($wpdtreeopt['closescript']) ? $wpdtreeopt['closescript'] : '</script>' ;
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
			$rsslink = '?feed='.$feedtype.'&'.$treetype.'='.$nodedata['id'];	 		
		} else{				
			//$path = str_replace(trailingslashit(get_bloginfo('url')), '', $nodedata['url']);			
			$rsslink = trailingslashit($nodedata['url']).'feed';			
		}		
	}
	return $rsslink;
}

function wpdt_force_open_to($opento, $tree_id, $treestring, $listposts = true){ 
	$result = "\n/*WP-dTree: force open to: '{$opento}' */\n";
	if(trim($opento) == 'all'){
		$result .= $tree_id.".openAll();\n";
	} else {
		$requests = explode(',', $opento);		
		foreach($requests as $request){
			$result .= wpdt_open_tree_to($request, $tree_id, $treestring, true, $listposts);
		}					
	}	
	return $result;				
}

function wpdt_escape_js($unsafe){
	if(function_exists('json_encode')){ //php 4.
		return json_encode($unsafe);
	}	
	return str_replace('/', '&#x2F;', htmlspecialchars($unsafe, ENT_QUOTES, 'UTF-8')); //escape &<>"' and /	
}

/* 	This function is hairy. It helps if you take a look at the JS-source in the HTML first. Here's one typical line:
		arc1.a(4695,2,'Post Title','','2010/10/post-title/','','');
	We're trying to find the node-ID (4695 in this case) corresponding to the requested URL. 
	$listposts is a Q&D fix to let category trees open to the right node even if not displaying posts
	*/
function wpdt_open_tree_to($request, $tree_id, $treestring, $forced = false, $listposts = true){
	global $wp_query;	
	$opt = get_option('wpdt_options');	
	$prefix = is_category() ? '-' : ''; //category IDs are negated to avoid ID-trampling in the tree		
	if(is_numeric($request)){ //assume request was a node ID.				
		return "$tree_id.openTo('{$prefix}{$request}', true); /*was numeric*/\n";
	}
	if(!$forced){ //don't allow shortcutting when forcing a node open.
		if(is_home()){
			return "$tree_id.closeAll(); /*is home*/\n";
		}
		if($wp_query){
			$maybe_id = $wp_query->get_queried_object_id();//If the request is a category, author, permalink or page
			if($maybe_id){				
				if($listposts == false && !$prefix && strpos($tree_id, 'cat') === 0){//we're a category tree without posts, and not in category view
					$catObj = get_the_category($maybe_id); //let's grab a category from the requested post and open that
					if($catObj && $catObj[0]){
						return  "$tree_id.openTo('-{$catObj[0]->cat_ID}', true); /*get the category*/\n";
					}				
				}
				if(strpos($treestring, "({$prefix}{$maybe_id},") === false){ //
					return "/*wp_query object id = {$prefix}{$maybe_id}. invalid id.*/\n"; 
				}
				return "$tree_id.openTo('{$prefix}{$maybe_id}', true); /*wp_query object id*/\n";
			}					
			$maybe_id = (isset($wp_query->post->ID) && $wp_query->found_posts == 1) ? $wp_query->post->ID : false;
			if($maybe_id !== false){//if more than one post, ignore the id (will be top-post but we want the category/archive view)
				return  "$tree_id.openTo('{$maybe_id}', true); /*wp_query post ID*/\n";
			}
			$paged = $wp_query->query_vars['paged'];
			if($paged > 0){//the dtree is unaware of paging, so remove it from request.			
				$request = str_replace("?paged={$paged}", '', $request);
				$request = str_replace("/page/{$paged}", '', $request);	
			}
		}
	}
	//Okay, we were fed an URL. Let's clean it up to look like it would in the JS-source. 
	$path = ltrim($request, '/'); 					//REQUEST_URI should be '/blog/category/post/' or somesuch. Remove leading slash.	
		/*NOTE: $request is untrusted data submitted from user! Thanks to Patrick Riggs for reporting! */
	$blogurl = get_bloginfo('url'); 				//yields: http://blog.server.com, http://server.com/~userdir - you get the picture. 							
	if(empty($path) || $path == $blogurl || $path == '/'){ 			//we've probably requested "home", so let's do nothing
		return "/*\nWP-dTree: {$tree_id} request seems to be home.\n*/";	
	}else if(strpos($path, $blogurl) === 0){		//REQUEST_URI included http://server.com/ (happens on some hosts)			
		$path = str_replace($blogurl, '', $path);	//all URLs are relative in the JS source (to save space), so let's get rid of the blog url.
	} else { 										//some servers (with userdir) gives us: '~userdir/blog/category/post/'				
		$segments = explode('/', $path); 			//$segments[0] could be '~userdir' or 'blog' now
		if(strpos($blogurl, $segments[0])!== false){//REQUEST_URI gave us the userdir - this is included in the blog url, so lets remove it. 
			$path = ltrim(str_replace($segments[0], '', $path));
		}		
	}
	$path = ltrim($path, '/'); 					//REQUEST_URI should be '/blog/category/post/' or somesuch. Remove leading slash.								  
	if(empty($path)){return "/*WP-dTree: requested path was empty*/";} //this should never happen, so let's handle it. :P	
	$path = "'".$path."'";					//the JS parameters are surrounded by '', so let's be explicit (avoid 2010/10 match with 2010/10/post-title)
	//Now to isolate the ID. First we find the line where it appears
	$parts = explode($path, $treestring); 	//split the script around the path, to immedietly narrow the search. (thus we know line is at the end of the first part)
	if(count($parts) < 2){
		return "/*WP-dTree: {$tree_id} request was ". wpdt_escape_js($path) ." Couldn't find it.*/\n";
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
		return "/*WP-dTree: {$tree_id} request was {".wpdt_escape_js($path)."} I found: '".esc_js($number)."'*/\n\n{$tree_id}.openTo('{$number}', true);\n";
	}	 	
	return "/*WP-dTree: {PHP_VERSION} $tree_id request was {".wpdt_escape_js($path)."}. I found: ".esc_js($number)."*/\n";	//if we get down here something was wrong. output some debug-info.
}

function wpdt_get_tree_id($treestring){
	if(!$treestring){return false;}
	$s = stripos($treestring, 'var ')+4; // 4 = strlen('var ')
	$e = stripos($treestring, ' = new wpdTree', $s); //var {id} = new wpdTree
	return substr($treestring, $s, $e-$s);		
}

?>