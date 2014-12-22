<?php
function wpdt_get_pages_nodelist($args){	
	extract($args, EXTR_SKIP);		
	$idcount = 1;
		
	$pageresults = get_pages(array(
		'child_of' => $child_of, 
		'parent'	=> $parent,
		'sort_order' => $sort_order,
		'sort_column' => $sort_column, 
		'hierarchical' => $hierarchical,
		'exclude_tree' => $exclude_tree,
		'exclude' => $exclude, 
		'include' => $include,
		'meta_key' => $meta_key, 
		'meta_value' => $meta_value,
		'authors' => $authors
	));				
	$nodelist = array();	
	if($pageresults){
		foreach($pageresults as $pageresult){			
			$nodelist[$idcount] = array('id' => $pageresult->ID, 'pid' => $pageresult->post_parent, 'url' => esc_url(get_permalink($pageresult->ID)), 'name' => strip_tags(apply_filters('the_title', $pageresult->post_title)), 'title' => '');
			$idcount++;
		}
		//pages can be arranged arbitrarily, and with some creative exlusion/inclusion, you'll easily create a tree without a single page connecting to root or a even parent.
		//thus this step to fixup any orphans.
		foreach($nodelist as $key => $node){
			if($node['pid'] == 0){continue;} //connected to root.
			$hasparent = false;			
			foreach($nodelist as $potential_parent){
				if($potential_parent['id'] == $node['pid']){					
					$hasparent = true; break;
				}
			}
			if(!$hasparent){$nodelist[$key]['pid'] = 0;} //connect orphans to root.
		}					
		
	}	
	return $nodelist;
}
?>