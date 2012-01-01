<?php
//In WP 2.6, I suddenly got problems with global variables "dissapearing", so these getters are... Q&D.
function wpdt_get_table_name(){
	global $wpdb; return $wpdb->prefix . "dtree_cache";
}

function wpdt_install_cache(){
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;
	$wpdt_cache = wpdt_get_table_name();
	wpdt_uninstall_cache();		
	$charset_collate = '';
	if(version_compare(mysql_get_server_info(), '4.1.0', '>=')){
		if(!empty($wpdb->charset)){
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if(!empty($wpdb->collate)){
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
	}			
	$sql = "CREATE TABLE {$wpdt_cache} (
	hash BINARY(16) NOT NULL, 
	content MEDIUMTEXT NOT NULL,				
	UNIQUE KEY  hash (hash)		
	) {$charset_collate};";	
	dbDelta($sql);					
}

function wpdt_uninstall_cache(){			
	global $wpdb;
	$wpdt_cache = wpdt_get_table_name();	
	if($wpdb->get_var("SHOW TABLES LIKE '$wpdt_cache'") == $wpdt_cache) {
		$wpdb->query("DROP TABLE " . $wpdt_cache); 	
	}	
}

/*we no longer have a single monoholotic cache.
when the blog changes, we simply invalidate all stored cache rows.*/
function wpdt_update_cache(){ 
	global $wpdb;		
	$wpdt_cache = wpdt_get_table_name();
	$wpdb->query("DELETE FROM {$wpdt_cache}");
}

function wpdt_get_seed($args){
	global $wpdb;
	return $wpdb->escape(serialize($args));
}

function wpdt_insert_tree_data($treedata, $seed){
	if(!isset($treedata) || $treedata == ""){
		return;
	}	
	global $wpdb;
	$wpdt_cache = wpdt_get_table_name();			
	$safeRow = $wpdb->escape($treedata); 
	$sql = 	"INSERT INTO ".$wpdt_cache
  			." (hash, content)
  			VALUES (UNHEX(MD5('{$seed}')),'".$safeRow."')";		
	$wpdb->query($sql);	
}

function wpdt_get_cached_data($seed){
	global $wpdb;
	$wpdt_cache = wpdt_get_table_name();	
	$results = $wpdb->get_var("SELECT content FROM {$wpdt_cache} WHERE hash = UNHEX(MD5('{$seed}')) LIMIT 1");	
	return ($results) ? $results : '';
}

function wpdt_clear_cache($seed){ /*args = settings array for a tree*/
	global $wpdb;
	$wpdt_cache = wpdt_get_table_name();	
	$wpdb->query("DELETE FROM {$wpdt_cache} WHERE hash = UNHEX(MD5('{$seed}'))");
}

function wpdt_clean_exclusion_list($excluded){	
	$cleanlist = '';
	if(empty($excluded)){ return $cleanlist; }
	$exposts = preg_split('/[\s,]+/',$excluded);	
	if(!count($exposts)){ return $cleanlist; }
	$exposts = array_unique($exposts);
	foreach($exposts as $expostID){
		if(!is_numeric($expostID)){continue;}
		if(empty($cleanlist)){
			$cleanlist = intval($expostID); 
		} else{		
			$cleanlist = $cleanlist . "," . intval($expostID);
		}						
	}				
	return $cleanlist;
}

function wpdt_build_exclude_statement($excluded, $field = 'ID'){
	return ($excluded) ? " AND {$field} NOT IN ($excluded) " : '';
}
?>