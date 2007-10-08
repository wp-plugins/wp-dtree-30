<?php
/* 
Methods for setting up, updating and removing the cache table for wp-dtree. 
ulfben 070816
*/
global $wpdb;
$wp_dtree_cache = $wpdb->prefix . "dtree_cache";

function silpstream_wp_dtree_install_cache()
{	
	global $wpdb, $wp_dtree_cache;  
	$wpdb->show_errors();	
	if($wpdb->get_var("show tables like '$wp_dtree_cache'") != $wp_dtree_cache)  
	{     
			$sql = "CREATE TABLE " . $wp_dtree_cache . "(   
			id mediumint(9) DEFAULT '0' NOT NULL,	  
			archives_arr MEDIUMTEXT,
			categories_arr MEDIUMTEXT,	
			pages_arr MEDIUMTEXT,
			UNIQUE KEY id (id)	  
			);";
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);     	
	
		//base64 encoding - a quick and dirty way to ensure no illegal characters enters the DB query
		$arcresults = base64_encode( serialize( silpstream_wp_dtree_get_archives_arr() ));
		$catresults = base64_encode( serialize( silpstream_wp_dtree_get_categories_arr() ));
		$pgeresults = base64_encode( serialize( silpstream_wp_dtree_get_pages_arr() ));
		
		$insert = "INSERT INTO " . $wp_dtree_cache .  
		" (archives_arr, categories_arr, pages_arr) " .
		"VALUES ('" . $arcresults . "','" . $catresults . "', '" . $pgeresults . "')";        
		$wpdb->query( $insert );
	}
	else
	{
		/*categories_arr and pages_arr were of type "text" in previous versions. 
		That type is too small for our purpose so we have to re-install the table 
		if these fields are found (ie; use didn't disable plugin before updating.)*/
		$tableinfo = $wpdb->get_results("DESCRIBE " .$wp_dtree_cache);		
		foreach($tableinfo as $row) {		
			if(strtolower($row->Field) == "text") { 
				if(silpstream_wp_dtree_uninstall_cache()){
					silpstream_wp_dtree_install_cache();
				} else {
					wp_die( __("wp-dtree-3.0: Unable to DROP TABLE $wp_dtree_cache.<br> Disable the plugin, drop the table manually and then install the plugin again."));
				}
			}
		}		
		wp_dtree_update_cache();			
	}
	
}

function silpstream_wp_dtree_uninstall_cache()
{			
	global $wpdb, $wp_dtree_cache;   
	$wpdb->show_errors();	
	if($wpdb->get_var("show tables like '$wp_dtree_cache'") != $wp_dtree_cache)  { return false; }
	else {  $wpdb->query("DROP TABLE " . $wp_dtree_cache); }
	return true;
}

function wp_dtree_update_cache()
{	
	global $wpdb, $wp_dtree_cache; 			
	if($wpdb->get_var("show tables like '$wp_dtree_cache'") != $wp_dtree_cache) {silpstream_wp_dtree_install_cache();} 		
	wp_dtree_update_pages_arr();
	wp_dtree_update_archives_arr();	
	wp_dtree_update_categories_arr();
}

function wp_dtree_update_categories_arr()
{
	global $wpdb, $wp_dtree_cache;   	
	$catresults = base64_encode( serialize(silpstream_wp_dtree_get_categories_arr() ));      
	$update = "UPDATE " . $wp_dtree_cache . " SET categories_arr='".$catresults."' WHERE id=0";
	$wpdb->query( $update );
}

function wp_dtree_update_pages_arr()
{
	global $wpdb, $wp_dtree_cache;   
	$pgeresults = base64_encode( serialize(silpstream_wp_dtree_get_pages_arr() ));     
	$update = "UPDATE " . $wp_dtree_cache . " SET pages_arr='".$pgeresults."' WHERE id=0";
	$wpdb->query( $update );
}

/*post_id is set if this function is called for delete_post*/
function wp_dtree_update_archives_arr($post_ID = -1)
{
	global $wpdb, $wp_dtree_cache;   
	if($wpdb->get_var("show tables like '$wp_dtree_cache'") != $wp_dtree_cache) {
		silpstream_wp_dtree_install_cache();
	} 	
	
	/*since delete_post is hooked _prior_ to deleting the post we have to hide it from the tree
		thus we add the ID of the (to be) deleted post to our exclude list.*/		
	if($post_ID > 0) {		
		$wpdtreeopt = get_option('wp_dtree_options');
		$excluded = $wpdtreeopt['genopt']['exclude']; 
		if(empty($excluded))  {
			$excluded = $post_ID; 
		} else {
			$excluded = $excluded . "," . $post_ID;
		}
		$wpdtreeopt['genopt']['exclude'] = $excluded;
		update_option('wp_dtree_options', $wpdtreeopt);
		wp_dtree_update_categories_arr(); //we must update categories as well, or the deleted post will still be visible there.
		wp_dtree_update_pages_arr(); //for good measure...
	}	
	$arcresults = base64_encode( serialize(silpstream_wp_dtree_get_archives_arr() )) ;      
	$update = "UPDATE " . $wp_dtree_cache . " SET archives_arr='".$arcresults."' WHERE id=0";
	$wpdb->query( $update );
}								
		

?>