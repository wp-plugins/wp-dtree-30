<?php
	/*
	Plugin Name: WP-dTree 3.3.2
	Plugin URI: http://wordpress.org/extend/plugins/wp-dtree-30/
	Description: A fork of <a href="http://www.silpstream.com/blog/wp-dtree/">Christopher Hwang's WP-dTree</a>, improving performance and adding useful features.
	Version: 3.3.2
	Author: Ulf Benjaminsson
	
	WP-dTree - Creates a JS navigation tree for your blog archives	
	Copyright (C) 2007 Ulf Benjaminsson (email: ulf at ulfben.com)	
	Copyright (C) 2006 Christopher Hwang (email: chris@silpstream.com)	
	
	This is a plugin created for Wordpress in order to generate JS navigation trees
	for your archives. It uses the JS engine dTree that was created by Geir Landr�
	at http://www.destroydrop.com/javascripts/tree/.
	
	Christopher Hwang wrapped the wordpress APIs around it so that we can use it as
	a plugin. He handled all development of wp-dtree up to version 2.2.

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
	Known issues: RSS icons wont show **in IE** if `post count` is on.

	Changes in v3.2 (ulfben - 20071015)
	1. Support for WP's bundled scriptacolous library - no need to download wp-scriptacolous plugin for cool effects.	
	2. Entirely new cache structure reducing cache size with ~33% compared to previous implementation.	 
	3. New option: Show RSS icon for categories
	4. New option: Show post count for categories
	5. New option: Effect duration
	Regressions: "open to selection" is broken again. It'll be back in the next version, but if it's vital for you, stay with 3.1

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
	
	require_once("wp-dtree_arc-functions.php");
	require_once("wp-dtree_cat-functions.php");
	require_once("wp-dtree_pge-functions.php");
	require_once("wp-dtree_gen-functions.php");
	require_once("wp-dtree_cache.php"); 
		
	if(function_exists('register_activation_hook') && function_exists('register_deactivation_hook')) {	
		register_activation_hook(__FILE__, 'wp_dtree_set_options');
		register_activation_hook(__FILE__, 'wp_dtree_install_cache');
		register_deactivation_hook(__FILE__, 'wp_dtree_delete_options');
		register_deactivation_hook(__FILE__, 'wp_dtree_uninstall_cache');
	} else {			
		add_action('activate_wp-dtree-30/wp-dtree.php','wp_dtree_set_options', 5);
		add_action('activate_wp-dtree-30/wp-dtree.php','wp_dtree_install_cache', 10); //install the cache as soon as all options are set.
		add_action('deactivate_wp-dtree-30/wp-dtree.php','wp_dtree_delete_options', 5);
		add_action('deactivate_wp-dtree-30/wp-dtree.php','wp_dtree_uninstall_cache', 10); 
	}
	add_action('init', 				'wp_dtree_load_javascripts');		//load scriptacolous if we're using effects.
	add_action('plugins_loaded', 	'wp_dtree_init_widgets');			//init widgets after the plugin has loaded.	
	add_action('wp_head', 			'wp_dtree_add2head');
	add_action('admin_menu', 		'wp_dtree_add_option_page');	
	add_action('delete_post', 		'wp_dtree_update_archives'); 	//called specifically so we can add the deleted post to our exlclude list.
	add_action('delete_category', 	'wp_dtree_update_cache');
	add_action('publish_post', 		'wp_dtree_update_cache');
	add_action('publish_page', 		'wp_dtree_update_cache');	
	add_action('update_option_permalink_structure', 'wp_dtree_update_cache'); //to get the right RSS, links and so on.
	
	//Declare some GLOBALS
	$idtranspose = array(
		'arc' => 0,
		'arcpost' => 10000,
		'cat' => 20000,
		'catpost' => 30000,
		'pge' => 40000,
		'pgepost' => 50000
	);
	
	
	function wp_dtree_load_javascripts() {	
		if ( !function_exists('wp_enqueue_script') || is_admin() ) {
			return;
		}
		$wpdtreeopt = get_option('wp_dtree_options');
		if($wpdtreeopt['effopt']['effon'])	{
			wp_enqueue_script('prototype');
			wp_enqueue_script('scriptaculous-effects');
		}	
	}
	
	function wp_dtree_init_widgets()
	{
		if ( !function_exists('register_sidebar_widget') ) {
			return;	
		}
		
		function widget_wp_dtree_get_archives($args) {
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  	
	    	
	        echo $before_widget; 
	        echo $before_title . $wpdtreeopt['arcopt']['topnode'] . $after_title . "<ul>";
	        if (function_exists('wp_dtree_get_archives')){				
			    wp_dtree_get_archives();
			}else{
				wp_get_archives('type=monthly'); 
			} 
	        echo "</ul>" . $after_widget;	
		}
		
		function widget_wp_dtree_get_categories($args) {
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  	
	    	
	        echo $before_widget; 
	        echo $before_title . $wpdtreeopt['catopt']['topnode'] . $after_title . "<ul>";
			if (function_exists('wp_dtree_get_categories')){
				wp_dtree_get_categories();
			} else {
				wp_list_categories('show_count=1');
			} 
	        echo "</ul>" . $after_widget;	
		}
		
		function widget_wp_dtree_get_pages($args) {
	    	extract($args);
	    	$wpdtreeopt = get_option('wp_dtree_options');  	
	    	
	        echo $before_widget; 
	        echo $before_title . $wpdtreeopt['pgeopt']['topnode'] . $after_title . "<ul>";
	        if (function_exists('wp_dtree_get_pages')) {
				wp_dtree_get_pages();
			} else {
				wp_list_pages();				
			} 
	        echo "</ul>" . $after_widget;	
		}
		
		register_sidebar_widget('WP-dTree Pages', 'widget_wp_dtree_get_pages');
		register_sidebar_widget('WP-dTree Archives', 'widget_wp_dtree_get_archives');
		register_sidebar_widget('WP-dTree Categories', 'widget_wp_dtree_get_categories');
	}		
								
	function wp_dtree_add_option_page() {
		if ( function_exists('add_options_page') ) {
			 add_submenu_page('themes.php', 'WP-dTree Settings', 'WP-dTree', 8, __FILE__, 'wp_dtree_option_page');
		}
	}
	
	function wp_dtree_add2head() {
		$wpdtreeopt = get_option('wp_dtree_options');
		
		$rssicon = get_bloginfo('wpurl') . "/wp-content/plugins/wp-dtree-30/dtree-img/feed-icon.png";	//normal
		$rssicon2 = get_bloginfo('wpurl') . "/wp-content/plugins/wp-dtree-30/dtree-img/feed-icon_h.png"; //higlight				
		$cd = "<script type=\"text/JavaScript\" src=\"" . get_bloginfo('wpurl') . "/wp-content/plugins/wp-dtree-30/dtree.php?witheff=".$wpdtreeopt['effopt']['effon']."&amp;eff=".$wpdtreeopt['effopt']['efftype']."&amp;effdur=".$wpdtreeopt['effopt']['duration']."&amp;trunc=".$wpdtreeopt['genopt']['truncate']."\" language=\"javascript\"></script>\n";
		$cd .= "<link rel=\"stylesheet\" href=\"" 
		. get_bloginfo('wpurl') 
		. "/wp-content/plugins/wp-dtree-30/style.php?"
		."fontsize=".$wpdtreeopt['cssopt']['fontsize']
		."&amp;mfontcolor=".$wpdtreeopt['cssopt']['mfontcolor']
		."&amp;lfontcolor=".$wpdtreeopt['cssopt']['lfontcolor']
		."&amp;lfontdecor=".$wpdtreeopt['cssopt']['lfontdecor']
		."&amp;hfontcolor=".$wpdtreeopt['cssopt']['hfontcolor']
		."&amp;hfontdecor=".$wpdtreeopt['cssopt']['hfontdecor']
		."&amp;rssgfx=".$rssicon
		."&amp;rssgfxh=".$rssicon2
		."\" type=\"text/css\" media=\"screen\" />\n";
		echo $cd;
	}
	
	function wp_dtree_delete_options() {
		delete_option('wp_dtree_options');
	}
	
	function wp_dtree_set_options() {
		$arcoptions = array(
			'arctype' => 'monthly',
			'listpost' => '1',
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '0',
			'folderlinks' => '1',
			'useselection' => '0',
			'opentosel' => '0',
			'topnode' => 'Archives',
			'showrss' => '0',
			'showcount' => '1'
		);
	
		$catoptions = array(
			'sortby' => 'ID',
			'sortorder' => 'ASC',
			'hideempty' => '0',
			'exclude' => '1',
			'listpost' => '1',			
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '0',
			'folderlinks' => '0',
			'useselection' => '0',
			'opentosel' => '0',
			'topnode' => 'Categories',
			'showrss' => '0',
			'showcount' => '1'
		);
	
		$pgeoptions = array(
			'sortby' => 'ID',
			'sortorder' => 'ASC',		
			'oclink' => '1',
			'uselines' => '1',
			'useicons' => '0',
			'closelevels' => '0',
			'folderlinks' => '0',
			'useselection' => '0',
			'opentosel' => '0',
			'topnode' => 'Pages'
		);
	
		$effoptions = array(
			'effon' => '0',
			'efftype' => 'blind',
			'duration' => '0.5'
		);
	
		$cssoptions = array(
			'fontsize' => '11',
			'mfontcolor' => '000000',
			'lfontcolor' => '06c',
			'lfontdecor' => 'none',
			'hfontcolor' => 'CCCCCC',
			'hfontdecor' => 'underline'
		);
	
		$genoptions = array(
			'truncate' => '16',
			'openlink' => 'open all',
			'closelink' => 'close all',
			'exclude' => ''			
		);
	
		$wpdtreeopt = array(
			'arcopt' => $arcoptions,
			'catopt' => $catoptions,
			'pgeopt' => $pgeoptions,
			'effopt' => $effoptions,
			'cssopt' => $cssoptions,
			'genopt' => $genoptions
		);
	
		update_option('wp_dtree_options', $wpdtreeopt);	
	}
	
	function wp_dtree_option_page() {
		$wpdtreeopt = get_option('wp_dtree_options');
		
		if( isset($_POST['submit']) ) {	
			( !isset($_POST['arctype']) ) 		? $arctype = "monthly" : $arctype = $_POST['arctype'] ;
			( !isset($_POST['alistpost']) ) 	? $alistpost = "0" : $alistpost = $_POST['alistpost'] ;			
			( !isset($_POST['aoclink']) ) 		? $aoclink = "0" : $aoclink = $_POST['aoclink'] ;
			( !isset($_POST['auselines']) ) 	? $auselines = "0" : $auselines = $_POST['auselines'] ;
			( !isset($_POST['auseicons']) ) 	? $auseicons = "0" : $auseicons = $_POST['auseicons'] ;
			( !isset($_POST['acloselevels']) ) 	? $acloselevels = "0" : $acloselevels = $_POST['acloselevels'] ;
			( !isset($_POST['afolderlinks']) ) 	? $afolderlinks = "0" : $afolderlinks = $_POST['afolderlinks'] ;
			( !isset($_POST['auseselection']) ) ? $auseselection = "0" : $auseselection = $_POST['auseselection'] ;
			( !isset($_POST['aopentosel']) )	? $aopentosel = "0" : $aopentosel = $_POST['aopentosel'] ;
			( !isset($_POST['atopnode']) ) 		? $atopnode = "Archives" : $atopnode = $_POST['atopnode'] ;
			( !isset($_POST['ashowcount']) ) 	? $ashowcount = "0" : $ashowcount = $_POST['ashowcount'] ;			
			( !isset($_POST['ashowrss']))		? $ashowrss = "0" : $ashowrss = $_POST['ashowrss'];		
			( !isset($_POST['csortby']) ) 	 	? $csortby = "ID" : $csortby = $_POST['csortby'] ;
			( !isset($_POST['csortorder']) ) 	? $csortorder = "ASC" : $csortorder = $_POST['csortorder'] ;
			( !isset($_POST['chideempty']) ) 	? $chideempty = "0" : $chideempty = $_POST['chideempty'] ;
			( !isset($_POST['cexclude']) ) 		? $cexclude = '1' : $cexclude = $_POST['cexclude'] ;
			( !isset($_POST['clistpost']) ) 	? $clistpost = "0" : $clistpost = $_POST['clistpost'] ;			
			( !isset($_POST['coclink']) ) 		? $coclink = "0" : $coclink = $_POST['coclink'] ;
			( !isset($_POST['cuselines']) ) 	? $cuselines = "0" : $cuselines = $_POST['cuselines'] ;
			( !isset($_POST['cuseicons']) ) 	? $cuseicons = "0" : $cuseicons = $_POST['cuseicons'] ;
			( !isset($_POST['ccloselevels']) ) 	? $ccloselevels = "0" : $ccloselevels = $_POST['ccloselevels'] ;
			( !isset($_POST['cfolderlinks']) ) 	? $cfolderlinks = "0" : $cfolderlinks = $_POST['cfolderlinks'] ;
			( !isset($_POST['cuseselection']) ) ? $cuseselection = "0" : $cuseselection = $_POST['cuseselection'] ;
			( !isset($_POST['copentosel']) ) 	? $copentosel = "0" : $copentosel = $_POST['copentosel'] ;
			( !isset($_POST['ctopnode']) ) 		? $ctopnode = "Categories" : $ctopnode = $_POST['ctopnode'] ;			
			( !isset($_POST['cshowcount']) ) 	? $showcount = "0" : $showcount = $_POST['cshowcount'] ;			
			( !isset($_POST['showrss']))		? $showrss = "0" : $showrss = $_POST['showrss'];	
			( !isset($_POST['psortby']) ) 		? $psortby = "ID" : $psortby = $_POST['psortby'] ;
			( !isset($_POST['psortorder']) ) 	? $psortorder = "ASC" : $psortorder = $_POST['psortorder'] ;			
			( !isset($_POST['poclink']) ) 		? $poclink = "0" : $poclink = $_POST['poclink'] ;
			( !isset($_POST['puselines']) ) 	? $puselines = "0" : $puselines = $_POST['puselines'] ;
			( !isset($_POST['puseicons']) ) 	? $puseicons = "0" : $puseicons = $_POST['puseicons'] ;
			( !isset($_POST['pcloselevels']) ) 	? $pcloselevels = "0" : $pcloselevels = $_POST['pcloselevels'] ;
			( !isset($_POST['pfolderlinks']) ) 	? $pfolderlinks = "0" : $pfolderlinks = $_POST['pfolderlinks'] ;
			( !isset($_POST['puseselection']) ) ? $puseselection = "0" : $puseselection = $_POST['puseselection'] ;
			( !isset($_POST['popentosel']) ) 	? $popentosel = "0" : $popentosel = $_POST['popentosel'] ;
			( !isset($_POST['ptopnode']) ) 		? $ptopnode = "Pages" : $ptopnode = $_POST['ptopnode'] ;	
			( !isset($_POST['effon']) ) 		? $effon = "0" :  $effon = $_POST['effon'] ;
			( !isset($_POST['efftype']) ) 		? $efftype = "blind" : $efftype = $_POST['efftype'] ;
			( !isset($_POST['duration']) ) 		? $duration = "0.5" : $duration = $_POST['duration'] ;			
			( !isset($_POST['fontsize']) ) 		? $fontsize = "11" : $fontsize = $_POST['fontsize'] ;
			( !isset($_POST['mfontcolor']) ) 	? $mfontcolor = "000000" : $mfontcolor = $_POST['mfontcolor'] ;
			( !isset($_POST['lfontcolor']) ) 	? $lfontcolor = "999999" : $lfontcolor = $_POST['lfontcolor'] ;
			( !isset($_POST['lfontdecor']) ) 	? $lfontdecor = "none" : $lfontdecor = $_POST['lfontdecor'] ;
			( !isset($_POST['hfontcolor']) ) 	? $hfontcolor = "CCCCCC" : $hfontcolor = $_POST['hfontcolor'] ;
			( !isset($_POST['hfontdecor']) ) 	? $hfontdecor = "underline" : $hfontdecor = $_POST['hfontdecor'] ;	
			( !isset($_POST['openlink']) ) 		? $openlink = "open all" : $openlink = $_POST['openlink'] ;
			( !isset($_POST['closelink']) ) 	? $closelink = "close all" : $closelink = $_POST['closelink'] ;
			( !isset($_POST['exclude']) ) 		? $exclude = '' : $exclude = $_POST['exclude'] ;
			( !isset($_POST['truncate']) ) 		? $ptruncate = "16" : $truncate = $_POST['truncate'] ;		
			
	
			$arcoptions = array(
				'arctype' => $arctype,
				'listpost' => $alistpost,				
				'oclink' => $aoclink,
				'uselines' => $auselines,
				'useicons' => $auseicons,
				'closelevels' => $acloselevels,
				'folderlinks' => $afolderlinks,
				'useselection' => $auseselection,
				'opentosel' => $aopentosel,
				'topnode' => $atopnode,
				'showcount' => $ashowcount,
				'showrss' => $ashowrss
			);
	
			$catoptions = array(
				'sortby' => $csortby,
				'sortorder' => $csortorder,
				'hideempty' => $chideempty,
				'exclude' => $cexclude,
				'listpost' => $clistpost,				
				'oclink' => $coclink,
				'uselines' => $cuselines,
				'useicons' => $cuseicons,
				'closelevels' => $ccloselevels,
				'folderlinks' => $cfolderlinks,
				'useselection' => $cuseselection,
				'opentosel' => $copentosel,
				'topnode' => $ctopnode,
				'showcount' => $showcount,
				'showrss' => $showrss
			);
	
			$pgeoptions = array(
				'sortby' => $psortby,
				'sortorder' => $psortorder,				
				'oclink' => $poclink,
				'uselines' => $puselines,
				'useicons' => $puseicons,
				'closelevels' => $pcloselevels,
				'folderlinks' => $pfolderlinks,
				'useselection' => $puseselection,
				'opentosel' => $popentosel,
				'topnode' => $ptopnode
			);
	
			$effoptions = array(
				'effon' => $effon,
				'efftype' => $efftype,
				'duration' => $duration
			);
	
			$cssoptions = array(
				'fontsize' => $fontsize,
				'mfontcolor' => $mfontcolor,
				'lfontcolor' => $lfontcolor,
				'lfontdecor' => $lfontdecor,
				'hfontcolor' => $hfontcolor,
				'hfontdecor' => $hfontdecor
			);
		
			$genoptions = array(
				'truncate' => $truncate,
				'openlink' => $openlink,
				'closelink' => $closelink,
				'exclude' => $exclude				
			);
	
			$wpdtreeopt = array(
				'catopt' => $catoptions,
				'arcopt' => $arcoptions,
				'pgeopt' => $pgeoptions,
				'effopt' => $effoptions,
				'cssopt' => $cssoptions,
				'genopt' => $genoptions
			);
						
			//removes any number signs from the colours, or our GET-query for style.php will break.
			$wpdtreeopt['cssopt']['fontsize'] 	= str_replace("#", '', $wpdtreeopt['cssopt']['fontsize']); 
			$wpdtreeopt['cssopt']['mfontcolor'] = str_replace("#", '', $wpdtreeopt['cssopt']['mfontcolor']);
			$wpdtreeopt['cssopt']['lfontcolor'] = str_replace("#", '', $wpdtreeopt['cssopt']['lfontcolor']);
			$wpdtreeopt['cssopt']['hfontcolor'] = str_replace("#", '', $wpdtreeopt['cssopt']['hfontcolor']);
			if(!is_numeric($wpdtreeopt['effopt']['duration']) || ($wpdtreeopt['effopt']['duration'] <= 0)){
				$wpdtreeopt['effopt']['duration'] = 0.5;
			}
			
			
			if ( !$effon ) {
				update_option('wp_dtree_options', $wpdtreeopt);
				echo "<div id=\"message\" class=\"updated fade\"><p>";
				echo "<font color=\"red\">WP-dTree settings updated...</font><br />";
				echo "</p></div>";
			} else {
				update_option('wp_dtree_options', $wpdtreeopt);
				echo "<div id=\"message\" class=\"updated fade\"><p>";
				echo "<font color=\"red\">WP-dTree settings updated...</font><br />";
				echo "<font color=\"red\">Effects are active...</font><br />";
				echo "</p></div>";
			}
			wp_dtree_update_cache(); //update cache when we edit plugin settings.		
		}
	?>
	
	<form method="post">	
	<div class="wrap">	
		<h2>WP-dTree Settings</h2>		
		<table class="optiontable">
			<tr>
				<td></td>
				<td><strong>Archive</strong></td>
				<td><strong>Category</strong></td>
				<td><strong>Page</strong></td>
			</tr>
			<tr class="alternate">
				<td>Top node name</td>
				<td><input type="text" value="<?php echo $wpdtreeopt['arcopt']['topnode']; ?>" name="atopnode" size="10" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['catopt']['topnode']; ?>" name="ctopnode" size="10" /></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['pgeopt']['topnode']; ?>" name="ptopnode" size="10" /></td>
			</tr>		
			<tr>
				<td>Display Open/Close links</td>
				<td><input type="checkbox" name="aoclink" value="1" <?php if ($wpdtreeopt['arcopt']['oclink']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="coclink" value="1" <?php if ($wpdtreeopt['catopt']['oclink']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="poclink" value="1" <?php if ($wpdtreeopt['pgeopt']['oclink']){ echo "checked";} ?> /></td>
			</tr>
			<tr class="alternate">
				<td>Draw lines</td>
				<td><input type="checkbox" name="auselines" value="1" <?php if ($wpdtreeopt['arcopt']['uselines']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cuselines" value="1" <?php if ($wpdtreeopt['catopt']['uselines']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="puselines" value="1" <?php if ($wpdtreeopt['pgeopt']['uselines']){ echo "checked";} ?> /></td>
			</tr>
			<tr>
				<td>Display icons</td>
				<td><input type="checkbox" name="auseicons" value="1" <?php if ($wpdtreeopt['arcopt']['useicons']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cuseicons" value="1" <?php if ($wpdtreeopt['catopt']['useicons']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="puseicons" value="1" <?php if ($wpdtreeopt['pgeopt']['useicons']){ echo "checked";} ?> /></td>
			</tr>
			<tr class="alternate">
				<td>Close same levels</td>
				<td><input type="checkbox" name="acloselevels" value="1" <?php if ($wpdtreeopt['arcopt']['closelevels']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="ccloselevels" value="1" <?php if ($wpdtreeopt['catopt']['closelevels']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="pcloselevels" value="1" <?php if ($wpdtreeopt['pgeopt']['closelevels']){ echo "checked";} ?> /></td>
			</tr>
			<tr>
				<td>Folders are links</td>
				<td><input type="checkbox" name="afolderlinks" value="1" <?php if ($wpdtreeopt['arcopt']['folderlinks']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cfolderlinks" value="1" <?php if ($wpdtreeopt['catopt']['folderlinks']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="pfolderlinks" value="1" <?php if ($wpdtreeopt['pgeopt']['folderlinks']){ echo "checked";} ?> /></td>
			</tr>
			<tr class="alternate">
				<td>Open to selection</td>
				<td><input type="checkbox" name="aopentosel" value="1" <?php if ($wpdtreeopt['arcopt']['opentosel']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="copentosel" value="1" <?php if ($wpdtreeopt['catopt']['opentosel']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="popentosel" value="1" <?php if ($wpdtreeopt['pgeopt']['opentosel']){ echo "checked";} ?> /></td>
			</tr>
			<tr>
				<td>Highlight selection</td>
				<td><input type="checkbox" name="auseselection" value="1" <?php if ($wpdtreeopt['arcopt']['useselection']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cuseselection" value="1" <?php if ($wpdtreeopt['catopt']['useselection']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="puseselection" value="1" <?php if ($wpdtreeopt['pgeopt']['useselection']){ echo "checked";} ?> /></td>
			</tr>
			<tr class="alternate">
				<td>Exclude Category</td>
				<td></td>
				<td><input type="text" value="<?php echo $wpdtreeopt['catopt']['exclude']; ?>" name="cexclude" size="10" /></td>
				<td></td>
			</tr>			
			<tr>
				<td>Sort by</td>
				<td></td>
				<td>
					<select name="csortby">
						<option value="ID"<?php if($wpdtreeopt['catopt']['sortby'] == 'ID'){ echo(' selected="selected"');}?>>ID</option>
						<option value="name"<?php if($wpdtreeopt['catopt']['sortby'] == 'name'){ echo(' selected="selected"');}?>>Name</option>
					</select>
				</td>
				<td>
					<select name="psortby">
						<option value="post_title"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_title'){ echo(' selected="selected"');}?>>Title</option>
						<option value="menu_order"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'menu_order'){ echo(' selected="selected"');}?>>Menu Order</option>
						<option value="post_date"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'post_date'){ echo(' selected="selected"');}?>>Date</option>
						<option value="ID"<?php if($wpdtreeopt['pgeopt']['sortby'] == 'ID'){ echo(' selected="selected"');}?>>ID</option>
					</select>
				</td>
			</tr>
			<tr class="alternate">
				<td>Sort order</td>
				<td></td>
				<td>
					<select name="csortorder">
						<option value="ASC"<?php if($wpdtreeopt['catopt']['sortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['catopt']['sortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>
				</td>
				<td>
					<select name="psortorder">
						<option value="ASC"<?php if($wpdtreeopt['pgeopt']['sortorder'] == 'ASC'){ echo(' selected="selected"');}?>>Ascending</option>
						<option value="DESC"<?php if($wpdtreeopt['pgeopt']['sortorder'] == 'DESC'){ echo(' selected="selected"');}?>>Descending</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Tree type</td>
				<td>
					<select name="arctype">
						<option value="monthly"<?php if($wpdtreeopt['arcopt']['arctype'] == 'monthly'){ echo(' selected="selected"');}?>>Monthly</option>
						<option value="yearly"<?php if($wpdtreeopt['arcopt']['arctype'] == 'yearly'){ echo(' selected="selected"');}?>>Yearly</option>
					</select>
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr class="alternate">
				<td>List posts</td>
				<td><input type="checkbox" name="alistpost" value="1" <?php if ($wpdtreeopt['arcopt']['listpost']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="clistpost" value="1" <?php if ($wpdtreeopt['catopt']['listpost']){ echo "checked";} ?> /></td>
				<td></td>
			</tr>
			<tr>
				<td>Hide empty categories</td>
				<td></td>
				<td><input type="checkbox" name="chideempty" value="1" <?php if ($wpdtreeopt['catopt']['hideempty']){ echo "checked";} ?> /></td>
				<td></td>
			</tr>			
			<tr class="alternate">
				<td>Show postcount</td>
				<td><input type="checkbox" name="ashowcount" value="1" <?php if ($wpdtreeopt['arcopt']['showcount']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="cshowcount" value="1" <?php if ($wpdtreeopt['catopt']['showcount']){ echo "checked";} ?> /></td>				
				<td></td>
			</tr>
			<tr>
				<td>Show RSS-icons</td>
				<td><input type="checkbox" name="ashowrss" value="1" <?php if ($wpdtreeopt['arcopt']['showrss']){ echo "checked";} ?> /></td>
				<td><input type="checkbox" name="showrss" value="1" <?php if ($wpdtreeopt['catopt']['showrss']){ echo "checked";} ?> /></td>
				<td></td>
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
				<td>				
					<input type="text" value="<?php echo $wpdtreeopt['genopt']['exclude']; ?>" name="exclude" size="10" />
					<label>Exclude posts/pages</label>
				</td>
				<td>
					You can exclude specific posts or pages from the tree. <br>The format for this is 'ID1,ID2,ID3', where the ID is based on the ID you see when you manage your posts/pages.
				</td>			
			</tr>
			<tr>				
				<td>									
					<input type="text" value="<?php echo $wpdtreeopt['genopt']['truncate']; ?>" name="truncate" size="5" />
					<label>Characters to display</label>					
				</td>				
				<td>
					Determines the width of your tree
				</td>
			</tr>
			</tr>
			<td colspan="3"><p class="submit"><input type="submit" name="submit" value="<?php _e('Update Settings &raquo;') ?>" /></p></td>
			<tr>
		</table>
	</div>	
	<div class="wrap">
		<h2>Scriptaculous Effects</h2>
		<table class="optiontable">
			<tr>
				<td width="280">
					<fieldset class="options">
						<p>
						<label><input type="checkbox" name="effon" value="true" <?php if ($wpdtreeopt['effopt']['effon']){ echo "checked";} ?> /> Enable scriptaculous effects.</label>
						</p>
						<p>
						<select name="efftype">
							<option value="blind"<?php if($wpdtreeopt['effopt']['efftype'] == 'blind'){ echo(' selected="selected"');}?>>Default (Blind)</option>
							<option value="slide"<?php if($wpdtreeopt['effopt']['efftype'] == 'slide'){ echo(' selected="selected"');}?>>Slide</option>
							<option value="appear"<?php if($wpdtreeopt['effopt']['efftype'] == 'appear'){ echo(' selected="selected"');}?>>Appear</option>
							<option value="grow"<?php if($wpdtreeopt['effopt']['efftype'] == 'grow'){ echo(' selected="selected"');}?>>Grow</option>
						</select>
						Effect type
						</p>
						<p>
						<input type="text" value="<?php echo $wpdtreeopt['effopt']['duration']; ?>" name="duration" size="10" />
						Duration (sec)
						</p>
					</fieldset>
				</td>
				<td>
					<p>Click the checkbox to enable effects, then select the effect from the drop down menu.</p>					
				</td>
			</tr>
			</tr>
			<td colspan="3"><p class="submit"><input type="submit" name="submit" value="<?php _e('Update Settings &raquo;') ?>" /></p></td>
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
						</p>
						<p>
						<input type="text" value="<?php echo $wpdtreeopt['cssopt']['lfontcolor']; ?>" name="lfontcolor" size="8" />
						Link font colour
						<br />
						<select name="lfontdecor">
							<option value="none"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'none'){ echo(' selected="selected"');}?>>None</option>
							<option value="underline"<?php if($wpdtreeopt['cssopt']['lfontdecor'] == 'underline'){ echo(' selected="selected"');}?>>Underline</option>
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
						</select>
						Mouse over decoration
						</p>
					</fieldset>
				</td>
				<td>
					<p>This area sets the CSS properties for the links that are displayed in your tree.</p>
				</td>
			</tr>
			</tr>
			<td colspan="3"><p class="submit"><input type="submit" name="submit" value="<?php _e('Update Settings &raquo;') ?>" /></p></td>
			<tr>
		</table>
	</div>
	</form>
	<div class="wrap">
		<h2>Help</h2>
		<p>Set the name of what you want the top node to be called with 'Top node name'.</p>
		<p>The 'Open/Close' link displays a link at top of the tree to allow someone to open/close the entire tree.</p>
		<p>'Close same levels' will close a node of the same rank when another node is selected.</p>
		<p>Whether icons and lines are displayed can also be chosen depending on your preferances.</p>
		<p>'Characters to display' sets the number of characters to allow before truncating the link shown with '...'.</p>
		<p>'Folders are links' allows you to switch off the link to a page so that clicking only expands the folder.</p>
		<p>'Open to selection' will make the menu open to the page that a person is browsing on but not highlight it.</p>
		<p>'Highlight selection' will highlight the item of the page that a person is browsing on.</p>
		<p>'List posts' allows your to select if the tree displays only the category or archive folders with or without the posts inside them.</p>
		<p>'Hide empty' allows you to hide any categories that have no posts. Note that this would hide their child categories also, even if the child category has a post.</p>
		<p>You can use 'exclude' to hide the posts or categories you don't want displayed. The format for this is 'ID1,ID2,ID3', where the ID is based on the ID you see when you manage your categories/posts. Note that this would also exclude child categories so becareful.</p>
		<p>Note that if you display both archive and categories and have open to selection or highlight selection on, it will occur on both menus if the settings are active on both.</p>
	</div>
	<?php
}
?>