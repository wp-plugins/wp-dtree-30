<?php
//In WP 2.6, I suddenly got problems with global variables "dissapearing", so these getters are... Q&D.
function wp_dtree_get_table_name(){
	global $wpdb; return $wpdb->prefix . "dtree_cache";
}
function wp_dtree_get_table_version(){
	return 7;
}
function wp_dtree_get_error_msg(){
	return "WP-dTree-".wp_dtree_get_version().": cache table (".wp_dtree_get_table_name().") is either missing or outdated. Disable the plugin and re-install it again.";
}

function wp_dtree_install_cache(){	
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();
	$wp_dtree_db_version = wp_dtree_get_table_version();  
	$wpdb->show_errors();	
	if(!wp_dtree_table_exists()){	
		$charset_collate = '';
		if( version_compare(mysql_get_server_info(), '4.1.0', '>=') ){
			if( ! empty($wpdb->charset) ){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if( ! empty($wpdb->collate) ){
				$charset_collate .= " COLLATE $wpdb->collate";
			}
		}			
		$sql = "CREATE TABLE " . $wp_dtree_cache . " (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT, 
		treetype CHAR(3), 
		content MEDIUMTEXT,		
		UNIQUE KEY  id (id)		
		) $charset_collate;";		
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($wpdb->prepare($sql));		
		update_option("wp_dtree_db_version", $wp_dtree_db_version);
		wp_dtree_update_cache();
		
	} else{
		if(!wp_dtree_table_is_current()){					 
			wp_dtree_uninstall_cache();
			wp_dtree_install_cache();						
		}		
		wp_dtree_update_cache();			
	}	
}

function wp_dtree_uninstall_cache(){			
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();
	$wpdb->show_errors();	
	if(!wp_dtree_table_exists()){ 
		return false; 
	}   		
	$wpdb->query("DROP TABLE " . $wp_dtree_cache);	
	return true;
}

function wp_dtree_update_cache(){
	$wpdtreeopt = get_option('wp_dtree_options');	
	/*$excluded = $wpdtreeopt['genopt']['exclude']; //old dtree option	
	wp_dtree_clean_exclusion_list($excluded); //removes ID's from posts that has been deleted in the past.	*/		
	if(!$wpdtreeopt['lnkopt']['isdisabled']){
		wp_dtree_update_links();
	}
	if(!$wpdtreeopt['pgeopt']['isdisabled']){
		wp_dtree_update_pages();
	}
	if(!$wpdtreeopt['arcopt']['isdisabled']){
		wp_dtree_update_archives();	
	}
	if(!$wpdtreeopt['catopt']['isdisabled']){
		wp_dtree_update_categories();
	}
}

function wp_dtree_update_archives(){		
	wp_dtree_insert_tree_data(wp_dtree_get_archives_arr(), 'arc');
}								

function wp_dtree_update_links(){ //gets called directly from hooks, thus the redundant test here.
	$wpdtreeopt = get_option('wp_dtree_options');
	if(!$wpdtreeopt['lnkopt']['isdisabled']){	
		wp_dtree_insert_tree_data(wp_dtree_get_links_arr(), 'lnk');
	}
}

function wp_dtree_update_categories(){	      
	wp_dtree_insert_tree_data(wp_dtree_get_categories_arr(), 'cat');
}

function wp_dtree_update_pages(){	   
	wp_dtree_insert_tree_data(wp_dtree_get_pages_arr(), 'pge');	
}

function wp_dtree_insert_tree_data($treedata, $treetype){
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();	
	if(!wp_dtree_table_exists() || !wp_dtree_table_is_current()){		
		wp_dtree_install_cache();		
	}		
	$wpdb->show_errors();
	$wpdb->query("DELETE FROM ". $wp_dtree_cache . " WHERE treetype = '".$treetype."'");
	if(!isset($treedata) || $treedata == ""){
		return;
	}	
	
	$safeRow = $wpdb->escape($treedata); 
	$sql = 	"INSERT INTO ".$wp_dtree_cache
  			." (treetype, content)
  			VALUES ('".$treetype."','".$safeRow."')";		
	$wpdb->query($sql);
}

/*inserts each node on it's own node which is easier on MySQL, but also pretty much negates the use
of caching; we're back to the one-query-per-node situation we had before.*/
function wp_dtree_safe_insert($treedata, $treetype){
	global $wpdb; 
	$wp_dtree_cache = wp_dtree_get_table_name();	
	$safeRow = "";	
	foreach($treedata as $treerow){
		$safeRow = $wpdb->escape(serialize($treerow));// base64_encode
		$sql = "INSERT INTO ".$wp_dtree_cache
			  ." (treetype, content)
			   VALUES ('".$treetype."','".$safeRow."')";		
		$wpdb->query($sql);		
	}		
}

//remove those ID's that doesn't exist in the database anymore. (eg; has been deleted)
function wp_dtree_clean_exclusion_list($excluded){
	global $wpdb;
	$wpdb->show_errors();	
	if( !empty($excluded) ){
		$cleanlist = '';
		$exposts = preg_split('/[\s,]+/',$excluded);
		if( count($exposts) ){
			foreach ( $exposts as $expostID ){				
				$exists = $wpdb->query( "SELECT * FROM ".$wpdb->posts." WHERE ID = ". intval($expostID) );				
				if($exists)	{
					if(empty($cleanlist)) {
						$cleanlist = intval($expostID); 
					} else{		
						$cleanlist = $cleanlist . "," . intval($expostID);
					}				
				}				
			}			
			$wpdtreeopt['genopt']['exclude'] = $cleanlist;			
			update_option('wp_dtree_options', $wpdtreeopt);	
		}		
	}	  	
}

function wp_dtree_table_is_current(){
	return get_option('wp_dtree_db_version') == wp_dtree_get_table_version();
}

function wp_dtree_table_exists(){
	global $wpdb;
	$wp_dtree_cache = wp_dtree_get_table_name();
	return $wpdb->get_var("show tables like '".$wp_dtree_cache."'") == $wp_dtree_cache;
}

?>