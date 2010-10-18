<?php
function wpdt_get_links_nodelist($args){	
	extract( $args, EXTR_SKIP );	
	$idcount = 1; 	
	$cats = get_terms('link_category', array(
		'name__like' => $category_name, 
		'include' => $category, 
		'exclude' => $exclude, 
		'orderby' => $catsorderby, 
		'order' => $order, 
		'hierarchical' => 0
	));	
	foreach($cats as $cat){					
		$nodelist[$idcount] = array( 
			'id' => -$cat->term_id,
			'pid' => $cat->parent,
			'url' => '', 
			'name' => ($showcount) ? strip_tags($cat->name ."&nbsp;({$cat->count})") : strip_tags($cat->name),
			'title' => $cat->description
		);
		$idcount++;							
		$bookmarks = get_bookmarks(array(
			'orderby' => $orderby, 
			'order' => $order,		
			'category' => $cat->term_id,			
			'hide_invisible' => $hide_invisible,
			'show_updated' => $show_updated, 
			//'search'
			//'include' => $include,			
			'exclude' => $exclude			
		));						
		foreach( $bookmarks as $bookmark ){											
			$the_link = '#';
			if(!empty($bookmark->link_url)){
				$the_link = esc_url($bookmark->link_url);
			}
			$name = esc_attr(sanitize_bookmark_field('link_name', $bookmark->link_name, $bookmark->link_id, 'display'));
			if($show_updated && '00' != substr($bookmark->link_updated_f, 0, 2)){				
				$name .= ' ('.sprintf(__('Last updated: %s'), date(get_option('links_updated_date_format'), $bookmark->link_updated_f + (get_option('gmt_offset') * 3600))).')';				
			}				
			$nodelist[$idcount] = array( 
				'id' => $bookmark->link_id,
				'pid' => -$cat->term_id,
				'url' => $the_link, 
				'name' => $name,
				'title' => esc_attr(sanitize_bookmark_field('link_description', $bookmark->link_description, $bookmark->link_id, 'display')),
				'target' => $bookmark->link_target				
			);
			$idcount++;			
		}			
	}		
	return $nodelist;
}
?>