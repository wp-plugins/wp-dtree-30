<?php
	/*
	Plugin Name: WP-dTree
	Plugin URI: http://wordpress.org/extend/plugins/wp-dtree-30/
	Description: A fork of <a href="http://www.silpstream.com/blog/wp-dtree/">Christopher Hwang's WP-dTree</a>, improving performance and adding useful features.
	Version: 3.4.3
	Author: Ulf Benjaminsson
	Author URI: http://www.ulfben.com
	
	WP-dTree - Creates a JS navigation tree for your blog archives	
	Copyright (C) 2007 Ulf Benjaminsson (email: ulf at ulfben.com)	
	Copyright (C) 2006 Christopher Hwang (email: chris@silpstream.com)	
	
	This is a plugin created for Wordpress in order to generate JS navigation trees
	for your archives. It uses the (somewhat modified) JS engine dTree that was created by Geir Landr?
	at http://www.destroydrop.com/javascripts/tree/.
	
	Christopher Hwang wrapped the wordpress APIs around it so that we can use it as
	a plugin. He handled all development of WP-dTree up to version 2.2.

	Changes in v3.5 (2008-11-14)
	Fixed the issue with RSS-icons not showing in IE if post count is on
	Replaced p for span in widgets - still XHTML compliant, but fixes some ugly padding
	Clean up on the admin screen backend
	updated the css to be more robust. 
	improved admin screen interface
	excluding posts from the category tree
	moved config screen from 'design' to 'settings'
	I18N
	Sorting posts archive tree
	Sorting posts in category tree
	Counting posts in sub categories
	List empty category bugs
	Quotes "" in post names messes with alt-text
	Link tree keeps target attribute.
	Tested for WP 2.7	
	
	Changes in v3.4.2 (2008-10-19)
	Bug: incorrect WP version detection. (thanks: StMD)
	
	Changes in v3.4.1 (ulfben 2008-07-20)
	Validates: both CSS and XHTML 1.0 Transitional (many thanks: ar-jar)
	
	Changes in v3.4 (ulfben 2008-07-12)
	Added support for link trees. (needs testing!)
	Fixed breakage in WP 2.5 & 2.6
	Fixed invalid XHTML output. (props: jberghem)
	Fixed a CSS-issue. (props: wenzlerm)	
	Renamed the dTree script to avoid collisions with plugins using an unmodified version.
	
	Changes in v3.3.2 (ulfben - 2007-11-26)
	Fixed bug with excluding multiple categories.
	
	Changes in v3.3.1 (ulfben - 20071102)
	Removed redundant <li>-tags from widgets. (props: Alexey Zamulla) 
	Properly encoded ampersands (&) in javascript URLs.
	Added CHARACTER SET and COLLATION to the tables. (props: michuw)
		
	Changes in v3.3 (ulfben - 20071026)
	Optimized dtree, **~40% less data** is stored and transfered! 
	New option: Show RSS icon for archives
	New option: Show post count for archives
	Fix: Open to requested node
	Fix: images URL not working on some servers ([props: Zarquod](http://wordpress.org/support/topic/136547))
	Fix: somewhat more IE compatible...
	Known issues: RSS icons wont show **in IE** if`post count` is on.

	Changes in v3.2 (ulfben - 20071015)
	1. Support for WP's bundled scriptacolous library - no need to download wp-scriptacolous plugin for cool effects.	
	2. Entirely new cache structure reducing cache size with ~33% compared to previous implementation.	 
	3. New option: Show RSS icon for categories
	4. New option: Show post count for categories
	5. New option: Effect duration
	Regressions: "open to selection" is broken again. It'll be back in the next version, but ifit's vital for you, stay with 3.1

	Changes in v3.1 (ulfben - 20071006)
	1. Updated to comply with WordPress 2.3's new taxonomy tables for categories. (should be backwards compatible)
	2. Widgetized! You'll no longer need to edit your sidebar manually
	3. Fixed "open to selection" and "highlight selection".
		
	Changes in v3.0 (ulfben)
	1. Added chaching to reduce the database load.
	
	Changes in v2.2
	1. Added support for generating page trees
	2. Added support for excluding specific posts from tree
	3. Updated option menu
	4. Rewrite of base code
	5. Fixed support for tooltips on non-linked folders
	6. Added option for not displaying posts in archive tree
	
	Changes in v2.1
	1. Patch to work with Regulus theme
	2. Ability to change open/close all link
	3. Set folders as links option
	4. Highlight current position in blog
	
	Changes in v2.0
	1. Support for scriptaculous effects added
	2. Category based menu added
	3. Option menu added to admin panel
	4. Support for dTree options was built in
	*/
	
	function wp_dtree_get_id_transpose(){//In WP 2.6, I suddenly got problems with global variables "dissapearing", so this getter is... Q&D.
		$idtranspose = array(
			'arc' => 0,
			'arcpost' => 10000,
			'cat' => 20000,
			'catpost' => 30000,
			'pge' => 40000,
			'pgepost' => 50000,
			'lnk' => 60000,
			'lnkpost' => 70000
		);
		return $idtranspose;
	}
	
	function wp_dtree_get_version(){
		if(function_exists('get_plugin_data')){
			$plugin_data = get_plugin_data(__FILE__);
			return "".$plugin_data['Version'];
		}
		return "3.4.3";
	}
	
	require_once('wp-dtree_lnk-functions.php');
	require_once('wp-dtree_arc-functions.php');
	require_once('wp-dtree_cat-functions.php');
	require_once('wp-dtree_pge-functions.php');
	require_once('wp-dtree_gen-functions.php');
	require_once('wp-dtree_cache.php'); 
		
	if(function_exists('register_activation_hook') && function_exists('register_deactivation_hook')){	
		register_activation_hook(__FILE__, 'wp_dtree_install');	
		register_deactivation_hook(__FILE__, 'wp_dtree_uninstall');
	} else{			
		add_action('activate_wp-dtree-30/wp-dtree.php','wp_dtree_install', 5);		
		add_action('deactivate_wp-dtree-30/wp-dtree.php','wp_dtree_uninstall', 10); 
	}
	add_action('init', 				'wp_dtree_load_javascripts');		//load scriptacolous if we're using effects.
	add_action('plugins_loaded', 	'wp_dtree_init_widgets');			//init widgets after the plugin has loaded.	
	add_action('wp_head', 			'wp_dtree_add2head');
	add_action('admin_menu', 		'wp_dtree_add_option_page');	
	add_action('deleted_post', 		'wp_dtree_update_cache'); 
	add_action('publish_post', 		'wp_dtree_update_cache'); //should use ${new_status}_$post->post_type since WP 2.3. How? 
	add_action('edit_post', 		'wp_dtree_update_cache');	
	add_action('edited_category', 	'wp_dtree_update_cache'); //should use 'created_$taxonomy' / 'edited_$taxonomy' since WP 2.3, but can find no docs on them. :(
	add_action('delete_category', 	'wp_dtree_update_cache');
	add_action('publish_page', 		'wp_dtree_update_cache');	
	add_action('update_option_permalink_structure', 'wp_dtree_update_cache'); //to get the right RSS, links and so on.
	add_action('add_link', 			'wp_dtree_update_links');
	add_action('delete_link', 		'wp_dtree_update_links');
	add_action('edit_link', 		'wp_dtree_update_links');
	
	//TODO: show this to first time users. 
	function wp_dtree_first_run_notice(){
		echo "<div id='wp-dtree-warning' class='updated fade'><p><strong>WP-dTree: remember to <a href='widgets.php'>activate the widgets!</strong></p></div>";
	}
	//add_action('admin_notices', 'wp_dtree_first_run_notice');
	
	function wp_dtree_install(){		
		wp_dtree_set_options();
		wp_dtree_install_cache();			
	}
	
	function wp_dtree_uninstall(){
		//wp_dtree_delete_options(); //uncomment if you want to clean your tables out
		wp_dtree_uninstall_cache();
	}
		
	function wp_dtree_load_javascripts(){	
		if(!function_exists('wp_enqueue_script') || is_admin()){
			return;
		}
		$wpdtreeopt = get_option('wp_dtree_options');
		if($wpdtreeopt['effopt']['effon']){
			wp_enqueue_script('prototype');
			wp_enqueue_script('scriptaculous-effects');
		}	
	}
	
	function wp_dtree_init_widgets(){
		if(!function_exists('register_sidebar_widget')){
			return;	
		}
		
		function widget_wp_dtree_get_links($args){
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  	    	
	        echo $before_widget; 
	        echo $before_title . __($wpdtreeopt['lnkopt']['topnode']). $after_title . "<span>";
	        if(function_exists('wp_dtree_get_links')){				
			    wp_dtree_get_links();
			}else{
				wp_list_bookmarks(); 
			} 
	        echo "</span>" . $after_widget;	
		}
		
		function widget_wp_dtree_get_archives($args){
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  		    	
	        echo $before_widget; 
	        echo $before_title . __($wpdtreeopt['arcopt']['topnode']) . $after_title . "<span>";
	        if(function_exists('wp_dtree_get_archives')){				
			    wp_dtree_get_archives();
			}else{
				wp_get_archives('type=monthly'); 
			} 
	        echo "</span>" . $after_widget;	
		}
		
		function widget_wp_dtree_get_categories($args){
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  		    	
	        echo $before_widget; 
	        echo $before_title . __($wpdtreeopt['catopt']['topnode']) . $after_title . "<span>";
			if(function_exists('wp_dtree_get_categories')){
				wp_dtree_get_categories();
			} else{
				wp_list_categories('show_count=1');
			} 
	        echo "</span>" . $after_widget;	
		}
		
		function widget_wp_dtree_get_pages($args){
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  	    	
	        echo $before_widget; 
	        echo $before_title . __($wpdtreeopt['pgeopt']['topnode']) . $after_title . "<span>";
	        if(function_exists('wp_dtree_get_pages')){
				wp_dtree_get_pages();
			} else{
				wp_list_pages();				
			} 
	        echo "</span>" . $after_widget;	
		}		
		register_sidebar_widget('WP-dTree Links', 'widget_wp_dtree_get_links');
		register_sidebar_widget('WP-dTree Pages', 'widget_wp_dtree_get_pages');
		register_sidebar_widget('WP-dTree Archives', 'widget_wp_dtree_get_archives');
		register_sidebar_widget('WP-dTree Categories', 'widget_wp_dtree_get_categories');
	}		
	
	function wp_dtree_add_admin_footer(){ //shows some plugin info at the footer of the config screen.
		$plugin_data = get_plugin_data(__FILE__);
		printf('%1$s plugin | Version %2$s | by %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}
	
	function wp_dtree_add_plugin_actions($links, $file){ //add's a "Settings"-link to the entry on the plugin screen
		static $this_plugin;
		if(!$this_plugin){
			$this_plugin = plugin_basename(__FILE__);
		}	
		if($file == $this_plugin){				
			$settings_link = $settings_link = '<a href="options-general.php?page=wp-dtree.php">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link );	
		}
		return $links;		
	}
								
	function wp_dtree_add_option_page(){		
		if(function_exists('add_options_page')){
			 add_options_page('WP-dTree Settings', 'WP-dTree', 8, basename(__FILE__), 'wp_dtree_option_page');			
			 add_filter('plugin_action_links', 'wp_dtree_add_plugin_actions', 10, 2 );
		}
	}
	
	function wp_dtree_add2head(){
		$wpurl = get_bloginfo('wpurl');
		$wpdtreeopt = get_option('wp_dtree_options');		
		$rssicon = urlencode($wpurl . '/wp-content/plugins/wp-dtree-30/dtree-img/feed-icon.png');	//normal
		$rssicon2 = urlencode($wpurl . '/wp-content/plugins/wp-dtree-30/dtree-img/feed-icon_h.png'); //higlight				
		$cd = '<script type="text/javascript" src="'.$wpurl.'/wp-content/plugins/wp-dtree-30/dtree.php?witheff='.$wpdtreeopt['effopt']['effon'].'&amp;eff='.$wpdtreeopt['effopt']['efftype'].'&amp;effdur='.$wpdtreeopt['effopt']['duration'].'"></script>'."\n";
		$cd .= '<link rel="stylesheet" href="' 
		. $wpurl 
		. '/wp-content/plugins/wp-dtree-30/style.php?'
		.'fontsize='.$wpdtreeopt['cssopt']['fontsize']
		.'&amp;fontf='.$wpdtreeopt['cssopt']['fontf']
		.'&amp;sfontdecor='.$wpdtreeopt['cssopt']['sfontdecor']
		.'&amp;mfontcolor='.$wpdtreeopt['cssopt']['mfontcolor']
		.'&amp;lfontcolor='.$wpdtreeopt['cssopt']['lfontcolor']
		.'&amp;lfontdecor='.$wpdtreeopt['cssopt']['lfontdecor']
		.'&amp;hfontcolor='.$wpdtreeopt['cssopt']['hfontcolor']
		.'&amp;hfontdecor='.$wpdtreeopt['cssopt']['hfontdecor']
		.'&amp;rssgfx='.$rssicon
		.'&amp;rssgfxh='.$rssicon2
		.'" type="text/css" media="screen" />';
		echo $cd;
	}
	
	function wp_dtree_delete_options(){
		delete_option('wp_dtree_options');
	}
	
	function wp_dtree_set_options(){				
		$lnkoptions = array(
			'isdisabled' => 0,
			'sortby' => 'name',
			'sortorder' => 'ASC',		
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '1',
			'folderlinks' => '0',
			'useselection' => '0',			
			'topnode' => __('Links'),			
			'show_updated' => 0,
			'catsorder' => 'name',
			'truncate' => '16'
		);			
		
		$arcoptions = array(
			'isdisabled' => 0,
			'sortby' => 'post_date',
			'sortorder' => 'DESC',
			'arctype' => 'monthly',
			'listpost' => '1',
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '1',
			'folderlinks' => '0',
			'useselection' => '0',
			'opentosel' => '0',
			'topnode' => __('Archives'),
			'showrss' => '0',
			'showcount' => '1',
			'truncate' => '16'
		);
	
		$catoptions = array(
			'isdisabled' => 0,
			'sortby' => 'ID',
			'sortorder' => 'ASC',
			'cpsortby' => 'post_date',
			'cpsortorder' => 'DESC',			
			'hideempty' => '1',
			'exclude' => '1',
			'postexclude' => '',
			'listpost' => '1',			
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '1',
			'folderlinks' => '0',
			'useselection' => '0',
			'opentosel' => '0',
			'topnode' => __('Categories'),
			'showrss' => '0',
			'showcount' => '1',
			'truncate' => '16'
		);
	
		$pgeoptions = array(
			'exclude' => '',
			'isdisabled' => 0,
			'sortby' => 'ID',
			'sortorder' => 'ASC',		
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '1',
			'folderlinks' => '0',
			'useselection' => '0',
			'opentosel' => '0',
			'topnode' => __('Pages'),
			'truncate' => '16' 
		);
	
		$effoptions = array(
			'effon' => '1',
			'efftype' => 'blind',
			'duration' => '0.5'
		);
	
		$cssoptions = array(
			'fontsize' 	 => '11',
			'fontf'		 => urlencode('Verdana, Geneva, Arial, Helvetica, sans-serif'),			
			'mfontcolor' => '000000',
			'lfontcolor' => '06c',
			'lfontdecor' => 'none',
			'hfontcolor' => 'CCCCCC',
			'hfontdecor' => 'underline',
			'sfontdecor' => 'underline'
		);
	
		$genoptions = array(			
			'openlink' => __('open all'),
			'closelink' => __('close all'),	
			'version' => wp_dtree_get_version()		
		);				
		
		$old = get_option('wp_dtree_options');		
		$new = array(
			'arcopt' => wp_dtree_merge_arrays($old['arcopt'], $arcoptions),
			'catopt' => wp_dtree_merge_arrays($old['catopt'], $catoptions),
			'pgeopt' => wp_dtree_merge_arrays($old['pgeopt'], $pgeoptions),
			'effopt' => wp_dtree_merge_arrays($old['effopt'], $effoptions),
			'cssopt' => wp_dtree_merge_arrays($old['cssopt'], $cssoptions),
			'genopt' => wp_dtree_merge_arrays($old['genopt'], $genoptions),
			'lnkopt' => wp_dtree_merge_arrays($old['lnkopt'], $lnkoptions)
		);							
		update_option('wp_dtree_options', $new);		
	}
	
	function wp_dtree_merge_arrays($old, $new){
		if(!empty($old)) {
			foreach($new as $key => $option){
				if(isset($old[$key])){
					$new[$key] = $old[$key];
				}
			}
		}	
		return $new;		
	}	

	function wp_dtree_option_page(){
		if(function_exists('current_user_can') && !current_user_can('manage_options') ){
			die(__('Cheatin&#8217; uh?'));
		}
		add_action('in_admin_footer', 'wp_dtree_add_admin_footer');		
		$catexc_default = 'Cat IDs';		
		$catpostexc_default = 'Post IDs';				
		$wpdtreeopt = get_option('wp_dtree_options');		
		if(!isset($wpdtreeopt['version']) || $wpdtreeopt['version'] != wp_dtree_get_version()){
			wp_dtree_set_options(); //update options if the user forgot to disable the plugin prior to upgrading.
			$wpdtreeopt = get_option('wp_dtree_options');			
		}				
		if(isset($_POST['submit'])){						
			$lnkoptions = array(
				'isdisabled' => $_POST['lnk_isdisabled'],
				'truncate' => $_POST['lnktruncate'],
				'sortby' => $_POST['lsortby'],
				'sortorder' => $_POST['lsortorder'],		
				'oclink' => $_POST['loclink'],
				'uselines' => (isset($_POST['luselines'])) ? 1 : 0,
				'useicons' => (isset($_POST['luseicons'])) ? 1 : 0,
				'closelevels' => (isset($_POST['lcloselevels'])) ? 1 : 0,
				'folderlinks' => (isset($_POST['lfolderlinks'])) ? 1 : 0,
				'useselection' => (isset($_POST['luseselection'])) ? 1 : 0,			
				'topnode' => __($_POST['ltopnode']),			
				'show_updated' => 0,
				'catsorder' => $_POST['lcatsorder']
			);						
			$arcoptions = array(
				'isdisabled' => $_POST['arc_isdisabled'],
				'truncate' => $_POST['arctruncate'],
				'sortby' => $_POST['asortby'],
				'sortorder' => $_POST['asortorder'],	
				'arctype' => $_POST['arctype'],
				'listpost' => $_POST['alistpost'],				
				'oclink' => $_POST['aoclink'],
				'exclude' => $_POST['exclude_posts'],
				'uselines' => (isset($_POST['auselines'])) ? 1 : 0,
				'useicons' => (isset($_POST['auseicons'])) ? 1 : 0,
				'closelevels' => (isset($_POST['acloselevels'])) ? 1 : 0,
				'folderlinks' => (isset($_POST['afolderlinks'])) ? 1 : 0,
				'useselection' => (isset($_POST['auseselection'])) ? 1 : 0,
				'opentosel' => $_POST['aopentosel'],
				'topnode' => __($_POST['atopnode']),
				'showcount' => $_POST['ashowcount'],
				'showrss' => $_POST['ashowrss']
			);				
			$catoptions = array(
				'isdisabled' => $_POST['cat_isdisabled'],
				'truncate' => $_POST['cattruncate'],
				'sortby' => $_POST['csortby'],				
				'sortorder' => $_POST['csortorder'],
				'cpsortby' => $_POST['cpsortby'], 
				'cpsortorder' => $_POST['cpsortorder'],
				'hideempty' => $_POST['chideempty'],
				'exclude' => $_POST['cexclude'],
				'postexclude' => $_POST['cpostexclude'],
				'listpost' => $_POST['clistpost'],				
				'oclink' => $_POST['coclink'],
				'uselines' => (isset($_POST['cuselines'])) ? 1 : 0,
				'useicons' => (isset($_POST['cuseicons'])) ? 1 : 0,
				'closelevels' => (isset($_POST['ccloselevels'])) ? 1 : 0,
				'folderlinks' => (isset($_POST['cfolderlinks'])) ? 1 : 0,
				'useselection' => (isset($_POST['cuseselection'])) ? 1 : 0,
				'opentosel' => $_POST['copentosel'],
				'topnode' => __($_POST['ctopnode']),
				'showcount' => $_POST['cshowcount'],
				'showrss' => $_POST['showrss']
			);			
			$pgeoptions = array(
				'isdisabled' => $_POST['pge_isdisabled'],
				'truncate' => $_POST['pgetruncate'],
				'sortby' => $_POST['psortby'],
				'sortorder' => $_POST['psortorder'],				
				'oclink' => $_POST['poclink'],
				'exclude' => $_POST['exclude_pages'],
				'uselines' => (isset($_POST['puselines'])) ? 1 : 0,
				'useicons' => (isset($_POST['puseicons'])) ? 1 : 0,
				'closelevels' => (isset($_POST['pcloselevels'])) ? 1 : 0,
				'folderlinks' => (isset($_POST['pfolderlinks'])) ? 1 : 0,
				'useselection' => (isset($_POST['puseselection'])) ? 1 : 0,
				'opentosel' => $_POST['popentosel'],
				'topnode' => __($_POST['ptopnode'])
			);			
			$effoptions = array(
				'effon' => ($_POST['efftype'] != 'none') ? 1 : 0,
				'efftype' => $_POST['efftype'],
				'duration' => $_POST['duration']
			);
			$cssoptions = array(
				'fontsize' 	 => $_POST['fontsize'],
				'fontf'		 => urlencode($_POST['fontf']),
				'mfontcolor' => $_POST['mfontcolor'],
				'lfontcolor' => $_POST['lfontcolor'],
				'lfontdecor' => $_POST['lfontdecor'],
				'hfontcolor' => $_POST['hfontcolor'],
				'hfontdecor' => $_POST['hfontdecor'],
				'sfontdecor' => $_POST['sfontdecor']
			);
			$genoptions = array(				
				'openlink' => __($_POST['openlink']),
				'closelink' => __($_POST['closelink']),				
				'version' => wp_dtree_get_version()							
			);	
			$wpdtreeopt = array(
				'catopt' => $catoptions,
				'arcopt' => $arcoptions,
				'pgeopt' => $pgeoptions,
				'effopt' => $effoptions,
				'cssopt' => $cssoptions,
				'genopt' => $genoptions,
				'lnkopt' => $lnkoptions
			);						
			//removes any number signs from the colours, or our GET-query for style.php will break.
			$wpdtreeopt['cssopt']['fontsize'] 	= str_replace('#', '', $wpdtreeopt['cssopt']['fontsize']); 
			$wpdtreeopt['cssopt']['mfontcolor'] = str_replace('#', '', $wpdtreeopt['cssopt']['mfontcolor']);
			$wpdtreeopt['cssopt']['lfontcolor'] = str_replace('#', '', $wpdtreeopt['cssopt']['lfontcolor']);
			$wpdtreeopt['cssopt']['hfontcolor'] = str_replace('#', '', $wpdtreeopt['cssopt']['hfontcolor']);
			if(!is_numeric($wpdtreeopt['effopt']['duration']) || ($wpdtreeopt['effopt']['duration'] <= 0)){
				$wpdtreeopt['effopt']['duration'] = 0.5;
			}
			if($wpdtreeopt['catopt']['exclude'] == $catexc_default){ //make sure we don't save the default-texts.
				$wpdtreeopt['catopt']['exclude'] = '';
			}
			if($wpdtreeopt['catopt']['postexclude'] == $catpostexc_default){
				$wpdtreeopt['catopt']['postexclude'] = '';
			}			
			update_option('wp_dtree_options', $wpdtreeopt);
			echo '<div id="message" class="updated fade"><p>';
			echo '<font color="black">'.__('WP-dTree settings updated...').'</font><br />';
			if($wpdtreeopt['arcopt']['isdisabled']){ echo '<font color="black">'.__('The archive tree is ').'<font color="orange">disabled.</font></font><br />';}
			if($wpdtreeopt['catopt']['isdisabled']){ echo '<font color="black">'.__('The category tree is ').'<font color="orange">disabled.</font></font><br />';}
			if($wpdtreeopt['lnkopt']['isdisabled']){ echo '<font color="black">'.__('The link tree is ').'<font color="orange">disabled.</font></font><br />';}
			if($wpdtreeopt['pgeopt']['isdisabled']){ echo '<font color="black">'.__('The page tree is ').'<font color="orange">disabled.</font></font><br />';}			
			if(!$wpdtreeopt['effopt']['effon']){ echo '<font color="black">'.__('Scriptaculous Effects are ').'<font color="orange">disabled...</font></font><br />';}
			echo '</p></div>';			
			wp_dtree_update_cache(); //update cache when we edit plugin settings.			
		}
		$alt = true;
	?>
	
	<form method="post">	
	<div class="wrap">	
		<h2>WP-dTree Settings</h2>		
		<table class="optiontable">		
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td></td>
				<td><strong>Archive</strong></td>
				<td><strong>Category</strong></td>
				<td><strong>Page</strong></td>
				<td><strong>Link</strong></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Disable tree<font color='orange'>*</font></td>
				<td><input type="checkbox" name="arc_isdisabled" value="1" <?php if($wpdtreeopt['arcopt']['isdisabled']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cat_isdisabled" value="1" <?php if($wpdtreeopt['catopt']['isdisabled']){ echo "checked";} ?> /></td>				
				<td><input type="checkbox" name="pge_isdisabled" value="1" <?php if($wpdtreeopt['pgeopt']['isdisabled']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="lnk_isdisabled" value="1" <?php if($wpdtreeopt['lnkopt']['isdisabled']){ echo "checked";} ?> /></td>				
			</tr>	
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Top node name</td>
				<td><input type="text" value="<?php echo $wpdtreeopt['arcopt']['topnode']; ?>" name="atopnode" size="10" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['catopt']['topnode']; ?>" name="ctopnode" size="10" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['pgeopt']['topnode']; ?>" name="ptopnode" size="10" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['lnkopt']['topnode']; ?>" name="ltopnode" size="10" /></td>
			</tr>		
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>				
				<td>					
				<label>Characters to display</label>					
				</td>				
				<td><input type="text" value="<?php echo $wpdtreeopt['arcopt']['truncate']; ?>" name="arctruncate" size="5" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['catopt']['truncate']; ?>" name="cattruncate" size="5" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['pgeopt']['truncate']; ?>" name="pgetruncate" size="5" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['lnkopt']['truncate']; ?>" name="lnktruncate" size="5" /></td>
			</tr>	
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Exclude IDs<font color='blue'>*</font></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['arcopt']['exclude']; ?>" name="exclude_posts" size="10" /></td>
				<td>
					<input type="text" value="<?php echo empty($wpdtreeopt['catopt']['exclude']) ? $catexc_default : $wpdtreeopt['catopt']['exclude']; ?>" name="cexclude" size="5" />
					<input type="text" value="<?php echo empty($wpdtreeopt['catopt']['postexclude']) ? $catpostexc_default : $wpdtreeopt['catopt']['postexclude']; ?>" name="cpostexclude" size="10" />
				</td>
				<td><input type="text" value="<?php echo $wpdtreeopt['pgeopt']['exclude']; ?>" name="exclude_pages" size="10" /></td>
				<td></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Sort by</td>
				<td>
					<select name="asortby">
						<option value="post_title"<?php if($wpdtreeopt['arcopt']['sortby'] == 'post_title'){ echo(' selected="selected"');}?>>Title</option>						
						<option value="post_date"<?php if($wpdtreeopt['arcopt']['sortby'] == 'post_date'){ echo(' selected="selected"');}?>>Date</option>
						<option value="ID"<?php if($wpdtreeopt['arcopt']['sortby'] == 'ID'){ echo(' selected="selected"');}?>>ID</option>
					</select>
					<select name="asortorder">
						<option value="ASC"<?php if($wpdtreeopt['arcopt']['sortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['arcopt']['sortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>
				</td>
				<td>
					<select name="csortby">
						<option value="ID"<?php if($wpdtreeopt['catopt']['sortby'] == 'ID'){ echo(' selected="selected"');}?>>ID</option>
						<option value="name"<?php if($wpdtreeopt['catopt']['sortby'] == 'name'){ echo(' selected="selected"');}?>>Name</option>
					</select>
					<select name="csortorder">
						<option value="ASC"<?php if($wpdtreeopt['catopt']['sortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['catopt']['sortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>
				</td>
				<td>
					<select name="psortby">					
						<option value="post_title"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_title'){ echo(' selected="selected"');}?>>Title</option>
						<option value="menu_order"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'menu_order'){ echo(' selected="selected"');}?>>Menu Order</option>
						<option value="post_date"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_date'){ echo(' selected="selected"');}?>>Date</option>
						<option value="ID"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'ID'){ echo(' selected="selected"');}?>>ID</option>
						<option value="post_modified"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_modified'){ echo(' selected="selected"');}?>>Modified</option>
						<option value="post_author"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_author'){ echo(' selected="selected"');}?>>Author</option>
						<option value="post_name"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_name'){ echo(' selected="selected"');}?>>Slug</option>					
					</select>
					<select name="psortorder">
						<option value="ASC"<?php if($wpdtreeopt['pgeopt']['sortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['pgeopt']['sortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>
				</td>
				<td>
					<select name="lsortby">					
						<option value="id"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'id'){ echo(' selected="selected"');}?>>ID</option>
						<option value="name"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'name'){ echo(' selected="selected"');}?>>Name</option>						
						<option value="description"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'description'){ echo(' selected="selected"');}?>>Descript.</option>
						<option value="owner"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'owner'){ echo(' selected="selected"');}?>>Owner</option>
						<option value="rating"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'rating'){ echo(' selected="selected"');}?>>Rating</option>
						<option value="updated"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'updated'){ echo(' selected="selected"');}?>>Updated</option>
						<option value="length"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'length'){ echo(' selected="selected"');}?>>Length</option>
						<option value="rand"<?php if($wpdtreeopt['lnkopt']['sortby'] == 'rand'){ echo(' selected="selected"');}?>>Random</option>
					</select>
					<select name="lsortorder">
						<option value="ASC"<?php if($wpdtreeopt['lnkopt']['sortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['lnkopt']['sortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>
				</td>
			</tr>		
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Show Open-/Close all</td>
				<td><input type="checkbox" name="aoclink" value="1" <?php if($wpdtreeopt['arcopt']['oclink']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="coclink" value="1" <?php if($wpdtreeopt['catopt']['oclink']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="poclink" value="1" <?php if($wpdtreeopt['pgeopt']['oclink']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="loclink" value="1" <?php if($wpdtreeopt['lnkopt']['oclink']){ echo "checked";} ?> /></td>
			</tr>										
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Draw lines</td>
				<td><input type="checkbox" name="auselines" value="1" <?php if($wpdtreeopt['arcopt']['uselines']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cuselines" value="1" <?php if($wpdtreeopt['catopt']['uselines']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="puselines" value="1" <?php if($wpdtreeopt['pgeopt']['uselines']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="luselines" value="1" <?php if($wpdtreeopt['lnkopt']['uselines']){ echo "checked";} ?> /></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Display icons</td>
				<td><input type="checkbox" name="auseicons" value="1" <?php if($wpdtreeopt['arcopt']['useicons']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cuseicons" value="1" <?php if($wpdtreeopt['catopt']['useicons']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="puseicons" value="1" <?php if($wpdtreeopt['pgeopt']['useicons']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="luseicons" value="1" <?php if($wpdtreeopt['lnkopt']['useicons']){ echo "checked";} ?> /></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Close same levels</td>
				<td><input type="checkbox" name="acloselevels" value="1" <?php if($wpdtreeopt['arcopt']['closelevels']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="ccloselevels" value="1" <?php if($wpdtreeopt['catopt']['closelevels']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="pcloselevels" value="1" <?php if($wpdtreeopt['pgeopt']['closelevels']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="lcloselevels" value="1" <?php if($wpdtreeopt['lnkopt']['closelevels']){ echo "checked";} ?> /></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Folders are links</td>
				<td><input type="checkbox" name="afolderlinks" value="1" <?php if($wpdtreeopt['arcopt']['folderlinks']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cfolderlinks" value="1" <?php if($wpdtreeopt['catopt']['folderlinks']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="pfolderlinks" value="1" <?php if($wpdtreeopt['pgeopt']['folderlinks']){ echo "checked";} ?> /></td>
				<td></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Open to selection</td>
				<td><input type="checkbox" name="aopentosel" value="1" <?php if($wpdtreeopt['arcopt']['opentosel']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="copentosel" value="1" <?php if($wpdtreeopt['catopt']['opentosel']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="popentosel" value="1" <?php if($wpdtreeopt['pgeopt']['opentosel']){ echo "checked";} ?> /></td>
				<td></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Highlight selection</td>
				<td><input type="checkbox" name="auseselection" value="1" <?php if($wpdtreeopt['arcopt']['useselection']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cuseselection" value="1" <?php if($wpdtreeopt['catopt']['useselection']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="puseselection" value="1" <?php if($wpdtreeopt['pgeopt']['useselection']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="luseselection" value="1" <?php if($wpdtreeopt['lnkopt']['useselection']){ echo "checked";} ?> /></td>
			</tr>										
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>List posts</td>
				<td><input type="checkbox" name="alistpost" value="1" <?php if($wpdtreeopt['arcopt']['listpost']){ echo "checked";} ?> /></td>
				<td>
					<input type="checkbox" name="clistpost" value="1" <?php if($wpdtreeopt['catopt']['listpost']){ echo "checked";} ?> />
					<span> by: </span>
					<select name="cpsortby">
						<option value="post_title"<?php if($wpdtreeopt['catopt']['cpsortby'] == 'post_title'){ echo(' selected="selected"');}?>>Title</option>						
						<option value="post_date"<?php if($wpdtreeopt['catopt']['cpsortby'] == 'post_date'){ echo(' selected="selected"');}?>>Date</option>
						<option value="ID"<?php if($wpdtreeopt['catopt']['cpsortby'] == 'ID'){ echo(' selected="selected"');}?>>ID</option>
					</select>
					<select name="cpsortorder">
						<option value="ASC"<?php if($wpdtreeopt['catopt']['cpsortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['catopt']['cpsortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>				
				</td>
				<td></td>
				<td></td>
			</tr>					
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Show postcount</td>
				<td><input type="checkbox" name="ashowcount" value="1" <?php if($wpdtreeopt['arcopt']['showcount']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cshowcount" value="1" <?php if($wpdtreeopt['catopt']['showcount']){ echo "checked";} ?> /></td>				
				<td></td>
				<td></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
				<td>Show RSS-icons</td>
				<td><input type="checkbox" name="ashowrss" value="1" <?php if($wpdtreeopt['arcopt']['showrss']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="showrss" value="1" <?php if($wpdtreeopt['catopt']['showrss']){ echo "checked";} ?> /></td>
				<td></td>
				<td></td>
			</tr>
			<?php echo ($alt = !$alt) ? '<tr class="alternate">' : '<tr>'; ?>
			<td>Misc</td>
			<td>				
				<select name="arctype">
					<option value="monthly"<?php if($wpdtreeopt['arcopt']['arctype'] == 'monthly'){ echo(' selected="selected"');}?>>Monthly</option>
					<option value="yearly"<?php if($wpdtreeopt['arcopt']['arctype'] == 'yearly'){ echo(' selected="selected"');}?>>Yearly</option>
				</select>
				<label> Tree type</label>
			</td>
			<td>				
				<input type="checkbox" name="chideempty" value="1" <?php if($wpdtreeopt['catopt']['hideempty']){ echo "checked";} ?> />
				<label> Hide empty</label>
			</td>
			<td></td>			
			<td>				
				<select name="lcatsorder">
					<option value="id"<?php if($wpdtreeopt['lnkopt']['catsorder'] == 'id'){ echo(' selected="selected"');}?>>ID</option>
					<option value="slug"<?php if($wpdtreeopt['lnkopt']['catsorder'] == 'slug'){ echo(' selected="selected"');}?>>Slug</option>
					<option value="name"<?php if($wpdtreeopt['lnkopt']['catsorder'] == 'name'){ echo(' selected="selected"');}?>>Name</option>
					<option value="count"<?php if($wpdtreeopt['lnkopt']['catsorder'] == 'count'){ echo(' selected="selected"');}?>>Count</option>
				</select>
				<label> Sort categories by</label>
			</td>
			</tr>				
		</table>
		
		<table class="optiontable">
			<tr>				
				<td>					
					<input type="text" value="<?php echo $wpdtreeopt['genopt']['openlink']; ?>" name="openlink" size="10" />
					<label>Open all link</label>
					<br />
					<input type="text" value="<?php echo $wpdtreeopt['genopt']['closelink']; ?>" name="closelink" size="10" />
					<label>Close all link</label>
					</p>
				</td>
				<td>
					Set the name of what you want the open/close all links to be.
				</td>				
			</tr>			
			<tr>
			<td colspan="3"><font color='blue'>*</font> Enter the page/post IDs to exclude as a comma-separated list, like so: "1,2,3". The ID can be seen in the URL when you edit your posts/pages.<br>
							<font color='orange'>*</font> Disable trees you're not using; it is a huge performance gain.</td>
			</tr>
		</table>
	</div>	
	<div class="wrap">
		<h2>Scriptaculous Effects</h2>
		<table class="optiontable">
			<tr>
				<td width="280">
					<fieldset class="options">					
						<tr><td>						
							<select name="efftype">
								<option value="none"<?php if($wpdtreeopt['effopt']['efftype'] == 'none'){ echo(' selected="selected"');}?>>None (disable)</option>							
								<option value="blind"<?php if($wpdtreeopt['effopt']['efftype'] == 'blind'){ echo(' selected="selected"');}?>>Default (Blind)</option>
								<option value="slide"<?php if($wpdtreeopt['effopt']['efftype'] == 'slide'){ echo(' selected="selected"');}?>>Slide</option>
								<option value="appear"<?php if($wpdtreeopt['effopt']['efftype'] == 'appear'){ echo(' selected="selected"');}?>>Appear</option>
								<option value="grow"<?php if($wpdtreeopt['effopt']['efftype'] == 'grow'){ echo(' selected="selected"');}?>>Grow</option>
							</select>
							<label>Effect type</label>
						</td><td>
							<input type="text" value="<?php echo $wpdtreeopt['effopt']['duration']; ?>" name="duration" size="10" />
							<label>Duration (sec)</label>
						</td></tr>
					</fieldset>
				</td>				
			</tr>
			<tr>
			<td colspan="3"></td>
			<tr>
		</table>
	</div>
	<div class="wrap">
		<h2>CSS Properties</h2>
		<table class="optiontable">
			<tr>
				<td>
					<fieldset class="options">
						<p>
						<input type="text" value="<?php echo $wpdtreeopt['cssopt']['fontsize']; ?>" name="fontsize" size="8" />
						Font size
						<br />
						<input type="text" value="<?php echo $wpdtreeopt['cssopt']['mfontcolor']; ?>" name="mfontcolor" size="8" />
						Normal font colour
						<br />
						<input type="text" value="<?php echo urldecode($wpdtreeopt['cssopt']['fontf']); ?>" name="fontf" size="30" />
						Font family
						</p>
						<p>
						<input type="text" value="<?php echo $wpdtreeopt['cssopt']['lfontcolor']; ?>" name="lfontcolor" size="8" />
						Link font colour
						<br />
						<select name="lfontdecor">
							<option value="none"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'none'){ echo(' selected="selected"');}?>>None</option>							
							<option value="underline"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'underline'){ echo(' selected="selected"');}?>>Underline</option>
							<option value="overline"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'overline'){ echo(' selected="selected"');}?>>Overline</option>
							<option value="line-through"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'line-through'){ echo(' selected="selected"');}?>>Line-through</option>
							<option value="blink"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'blink'){ echo(' selected="selected"');}?>>Blink</option>
						</select>
						Link font decoration
						</p>
						<p>
						<input type="text" value="<?php echo $wpdtreeopt['cssopt']['hfontcolor']; ?>" name="hfontcolor" size="8" />
						Mouse over font colour
						<br />
						<select name="hfontdecor">
							<option value="none"<?php if($wpdtreeopt['cssopt']['hfontdecor'] == 'none'){ echo(' selected="selected"');}?>>None</option>
							<option value="underline"<?php if($wpdtreeopt['cssopt']['hfontdecor'] == 'underline'){ echo(' selected="selected"');}?>>Underline</option>
							<option value="overline"<?php if($wpdtreeopt['cssopt']['hfontdecor'] == 'overline'){ echo(' selected="selected"');}?>>Overline</option>
							<option value="line-through"<?php if($wpdtreeopt['cssopt']['hfontdecor'] == 'line-through'){ echo(' selected="selected"');}?>>Line-through</option>
							<option value="blink"<?php if($wpdtreeopt['cssopt']['hfontdecor'] == 'blink'){ echo(' selected="selected"');}?>>Blink</option>
						</select>
						Mouse over decoration
						</p>
						<select name="sfontdecor">
							<option value="none"<?php if($wpdtreeopt['cssopt']['sfontdecor'] == 'none'){ echo(' selected="selected"');}?>>None</option>
							<option value="underline"<?php if($wpdtreeopt['cssopt']['sfontdecor'] == 'underline'){ echo(' selected="selected"');}?>>Underline</option>
							<option value="overline"<?php if($wpdtreeopt['cssopt']['sfontdecor'] == 'overline'){ echo(' selected="selected"');}?>>Overline</option>
							<option value="line-through"<?php if($wpdtreeopt['cssopt']['sfontdecor'] == 'line-through'){ echo(' selected="selected"');}?>>Line-through</option>
							<option value="blink"<?php if($wpdtreeopt['cssopt']['sfontdecor'] == 'blink'){ echo(' selected="selected"');}?>>Blink</option>
						</select>
						Selected node decoration (see: 'highlight selection')
						</p>
					</fieldset>
				</td>
				<td>
					<p>This area sets the CSS properties for the links that are displayed in your tree.</p>
				</td>
			</tr>
			<tr>
			<td colspan="3"><p class="submit"><input type="submit" name="submit" value="<?php _e('Update Settings &raquo;') ?>" /></p></td>
			</tr>
		</table>
	</div>
	</form>
	<?php
}
?>