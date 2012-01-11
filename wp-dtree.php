<?php
	/*
	Plugin Name: WP-dTree
	Plugin URI: http://wordpress.org/extend/plugins/wp-dtree-30/
	Description: <a href="http://www.destroydrop.com/javascripts/tree/">Dynamic tree</a> widgets to replace the standard archives-, categories-, pages- and link lists.
	Version: 4.3.1
	Author: Ulf Benjaminsson
	Author URI: http://www.ulfben.com
	License: GPL2
	Text Domain: wpdtree
	Domain Path: /lang
	
	WP-dTree - Creates a JS navigation tree for your blog archives	
	Copyright (C) 2007 Ulf Benjaminsson (email: ulf at ulfben.com)	
	Copyright (C) 2006 Christopher Hwang (email: chris@silpstream.com)	 
	
	This is a plugin created for Wordpress in order to generate JS navigation trees	for your archives. 
	It uses the (much modified) JS engine dTree that was created by Geir Landrö (http://www.destroydrop.com/javascripts/tree/).
	Christopher Hwang wrapped the wordpress APIs around it so that we can use it as a plugin. He handled all development of WP-dTree up to version 2.2 (~2007).	
	*/		
	add_action('plugins_loaded', 'wpdt_init');
	register_activation_hook(__FILE__, 'wpdt_activate');	
	register_deactivation_hook(__FILE__, 'wpdt_deactivate');				
	global $wpdt_tree_ids;
	$wpdt_tree_ids = array('arc' => 0, 'cat' => 0, 'pge' => 0, 'lnk' => 0, 'tax' => 0);//used to create unique instance names for the javascript trees.	
	require_once('wp-dtree-cache.php');	
	function wpdt_init() {
		if(!defined('ULFBEN_DONATE_URL')){
			define('ULFBEN_DONATE_URL', 'http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17');
		}		
		define('WPDT_BASENAME', plugin_basename( __FILE__ ));		
		define('WPDT_SCRIPT', 'wp-dtree.min.js');	
		define('WPDT_STYLE', 'wp-dtree.min.css');			
		load_plugin_textdomain('wpdt', false, dirname(WPDT_BASENAME).'/lang/');				
		add_filter('plugin_row_meta', 	'wpdt_set_plugin_meta', 2, 10);			
		add_action('admin_menu', 		'wpdt_add_option_page');	
		add_action('deleted_post', 		'wpdt_update_cache'); 
		add_action('publish_post', 		'wpdt_update_cache'); 
		add_action('save_post', 		'wpdt_update_cache');
		add_action('created_category', 	'wpdt_update_cache'); 
		add_action('edited_category', 	'wpdt_update_cache'); 
		add_action('delete_category', 	'wpdt_update_cache');
		add_action('publish_page', 		'wpdt_update_cache');	
		add_action('update_option_permalink_structure', 'wpdt_update_cache');
		add_action('add_link', 			'wpdt_update_cache');
		add_action('delete_link', 		'wpdt_update_cache');
		add_action('edit_link', 		'wpdt_update_cache');
		add_action('wp_print_styles', 	'wpdt_css');	
		add_action('wp_print_scripts', 	'wpdt_js');	
		add_action('widgets_init', 		'wpdt_load_widgets');	
		wpdt_print_errors();		
	}			
	function wpdt_print_errors(){	
		if ( TRUE === function_exists('error_get_last') && isset($_GET['charsout'])) {
			echo '<div id="message" class="error"><p>' . sprintf(__('error/warning/notice: <code>%s</code> | length: <code>%s</code>'), esc_html(var_export(error_get_last(), true)), $_GET['charsout']) . '</p></div>';
		}
	}
		
	function wpdt_get_version(){
		static $plugin_data;
		if(!$plugin_data){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php');
			$plugin_data = get_plugin_data( __FILE__ );
		}
		return "".$plugin_data['Version'];
	}	

	function wpdt_activate(){
		delete_option('wpdt_db_version');		
		wpdt_install_cache();		
		wpdt_install_options();
		wpdt_print_errors();	
	}	
	function wpdt_deactivate(){
		//options are only cleared on plugin uninstall (ie. delete from admin panel)		
		wpdt_uninstall_cache();
	}
	function wpdt_set_plugin_meta($links, $file) {		
		if($file == WPDT_BASENAME) {
			return array_merge($links, array(sprintf( '<a href="options-general.php?page=%s">%s</a>', WPDT_BASENAME, __('Settings', 'wpdtree'))));
		}
		return $links;
	}	
	function wpdt_add_admin_footer(){ //shows some plugin info in the footer of the config screen.
		$plugin_data = get_plugin_data(__FILE__);
		printf('%1$s by %2$s (who <a href="'.ULFBEN_DONATE_URL.'">appreciates books</a>) :)<br />', $plugin_data['Title'].' '.$plugin_data['Version'], $plugin_data['Author']);		
	}								
	function wpdt_add_option_page(){				
		add_options_page('WP-dTree Settings', 'WP-dTree', 'manage_options', WPDT_BASENAME, 'wpdt_option_page');						 
	}		
	function wpdt_css(){
		if(is_admin() || is_feed()){return;}
		$opt = get_option('wpdt_options');
		if(!$opt['disable_css']){
			wp_enqueue_style('dtree.css', plugin_dir_url(__FILE__).WPDT_STYLE, false, wpdt_get_version());
		}
	}
	function wpdt_js() {			   	
		if(is_admin() || is_feed()){return;}
		$opt = get_option('wpdt_options');
		$deps = array();
		if($opt['animate']){
			wp_enqueue_script('jquery', '', array(), false, true);					
			$deps = array('jquery');
		}
		wp_enqueue_script('dtree', plugin_dir_url(__FILE__).WPDT_SCRIPT, $deps, wpdt_get_version(), false);				
		wp_localize_script('dtree', 'WPdTreeSettings', array('animate' => $opt['animate'],'duration'=>$opt['duration'],'imgurl'=>plugin_dir_url(__FILE__)));
	}	
	function wpdt_load_widgets() {
		require_once('wp-dtree-widget.php');
		require_once('wp-dtree-arc-widget.php');
		require_once('wp-dtree-cat-widget.php');
		require_once('wp-dtree-tax-widget.php');
		require_once('wp-dtree-pge-widget.php');
		require_once('wp-dtree-lnk-widget.php');
		register_widget('WPDT_Archives_Widget');
		register_widget('WPDT_Categories_Widget');
		register_widget('WPDT_Taxonomies_Widget');
		register_widget('WPDT_Pages_Widget');
		register_widget('WPDT_Links_Widget');
	}
	/*These are convenience-functions for theme developers. They work kind of like the WordPress-function they replace. 
		They all accept template tag arguments (query string or assoc. array) - http://codex.wordpress.org/How_to_Pass_Tag_Parameters#Tags_with_query-string-style_parameters
		They accept empty parameter lists and gives reasonable defaults	
	Give array('echo' => 0) to get a very long string in return.
	More info: http://wordpress.org/extend/plugins/wp-dtree-30/other_notes/ */		
	function wpdt_list_archives($args = array()){ 	//similar to wp_get_archives		
		$args = wp_parse_args($args, wpdt_get_defaults('arc'));
		return wpdt_list_($args);
	}
	function wpdt_get_archives($args = array()){ 	//if you want to use WP inconsistent naming... :)
		wpdt_list_archives($args);
	}
	function wpdt_list_categories($args = array()){ //similar to wp_list_categories
		$args = wp_parse_args($args, wpdt_get_defaults('cat'));		
		return wpdt_list_($args);			
	}	
	function wpdt_list_taxonomies($args = array()){ //similar to wp_list_categories
		$args = wp_parse_args($args, wpdt_get_defaults('tax'));		
		return wpdt_list_($args);			
	}	
	function wpdt_list_pages($args = array()){ 		//similar to wp_list_pages
		$args = wp_parse_args($args, wpdt_get_defaults('pge'));
		return wpdt_list_($args);
	}
	function wpdt_list_links($args = array()){		//similar wp_list_bookmarks
		$args = wp_parse_args($args, wpdt_get_defaults('lnk'));
		return wpdt_list_($args);
	}
	function wpdt_list_bookmarks($args = array()){ 	//wrapper to emulate new WP function names
		return wpdt_list_links($args); 
	}
	function wpdt_get_archives_defaults(){ //to simplify finding all parameters
		return wpdt_get_defaults('arc');
	}
	function wpdt_get_categories_defaults(){
		return wpdt_get_defaults('cat');
	}
	function wpdt_get_taxonomies_defaults(){
		return wpdt_get_defaults('tax');
	}
	function wpdt_get_pages_defaults(){
		return wpdt_get_defaults('pge');
	}
	function wpdt_get_links_defaults(){
		return wpdt_get_defaults('lnk');
	}
	
	/*End "public" functions*/	
	
	
	function wpdt_list_($args){//common stub for "wp_list_*"-wrappers.
		$args['echo'] = !isset($args['echo']) ? 1 : $args['echo']; //default to print
		if($args['echo']){
			echo wpdt_get_tree($args);
		}else{
			return wpdt_get_tree($args);
		}
	}		
	function wpdt_print_tree($args){		
		echo wpdt_get_tree($args);
	}	
	function wpdt_get_tree($args){ 				
		require_once('wp-dtree-build.php');	
		global $wpdt_tree_ids;
		$args = wp_parse_args($args, wpdt_get_defaults($args['treetype']));
		$wpdt_tree_ids[$args['treetype']] += 1; //uniquely identify all trees.
		$opt = get_option('wpdt_options');	
		$was_cached = ($args['cache'] == 1);
		$seed = '';
		$tree = '';		
		if($args['cache']){
			$seed = wpdt_get_seed($args);		
			$tree = wpdt_get_cached_data($seed);			
		}			
		if(!$tree){
			$was_cached = false;
			if($args['treetype'] == 'arc'){
				require_once('wp-dtree-arc.php');
				$nodelist = wpdt_get_archive_nodelist($args);
				if(isset($args['show_post_count'])){$args['showcount'] = $args['show_post_count'];} //convert vanilla wp_get_archives arguments				
				$tree = wpdt_build_tree($nodelist, $args);
				if($opt['addnoscript']){
					$args['echo'] = 0;		
					$tree .= "\n<noscript>\n".wp_get_archives($args)."\n</noscript>\n";								
				}
			}else if($args['treetype'] == 'cat'){
				require_once('wp-dtree-cat.php');
				if(isset($args['parent']) && $args['parent'] == 'none'){unset($args['parent']);} //no default for parent, so let's flag and turn it off here.								
				if(isset($args['show_count'])){$args['showcount'] = $args['show_count'];} //convert vanilla wp_list_categories arguments
				if(isset($args['orderby'])){$args['sortby'] = $args['orderby'];}
				if(isset($args['order'])){$args['sortorder'] = $args['order'];}
				if(isset($args['feed'])){$args['showrss'] = 1;}			
				$nodelist = wpdt_get_category_nodelist($args);
				$tree = wpdt_build_tree($nodelist, $args);
				if($opt['addnoscript']){
					$args['echo'] = 0;		
					$tree .= "\n<noscript>\n".wp_list_categories($args)."\n</noscript>\n";								
				}
			}else if($args['treetype'] == 'tax'){
				require_once('wp-dtree-tax.php');
				if(isset($args['parent']) && $args['parent'] == 'none'){unset($args['parent']);} //no default for parent, so let's flag and turn it off here.								
				if(isset($args['show_count'])){$args['showcount'] = $args['show_count'];} //convert vanilla wp_list_categories arguments
				if(isset($args['orderby'])){$args['sortby'] = $args['orderby'];}
				if(isset($args['order'])){$args['sortorder'] = $args['order'];}
				if(isset($args['feed'])){$args['showrss'] = 1;}			
				$nodelist = wpdt_get_taxonomy_nodelist($args);
				$tree = wpdt_build_tree($nodelist, $args);
				if($opt['addnoscript']){
					$args['echo'] = 0;		
					$tree .= "\n<noscript>\n".wp_list_categories($args)."\n</noscript>\n";	//http://groups.google.com/group/wp-hackers/browse_thread/thread/24a41454c945dd9f?pli=1							
				}
			}else if($args['treetype'] == 'pge'){
				require_once('wp-dtree-pge.php');
				if(!isset($args['sort_column']) || $args['sort_column'] == ''){$args['sort_column'] = $args['sortby'];} //handle the vanilla wp_get_pages arguments.
				$nodelist = wpdt_get_pages_nodelist($args);
				$tree = wpdt_build_tree($nodelist, $args);
				if($opt['addnoscript']){
					$args['echo'] = 0;		
					$tree .= "\n<noscript>\n".wp_list_pages($args)."\n</noscript>\n";								
				}				
			}else if($args['treetype'] == 'lnk'){ 
				require_once('wp-dtree-lnk.php');
				if(!isset($args['orderby']) || $args['orderby'] == ''){$args['orderby'] = $args['sortby'];} //handle the vanilla wp_get_bookmarks arguments.	
				if(!isset($args['order']) || $args['order'] == ''){$args['order'] = $args['sort_order'];} 
				$nodelist = wpdt_get_links_nodelist($args);
				$tree = wpdt_build_tree($nodelist, $args);
				if($opt['addnoscript']){
					$args['echo'] = 0;		
					$tree .= "\n<noscript>\n".wp_list_bookmarks($args)."\n</noscript>\n";								
				}					
			}else{//user error. no type given. 
				return false;// '<!-- wpdt_get_tree: user error, no treetype given. -->';
			}			
		}		
		if($args['cache'] && !$was_cached){
			wpdt_insert_tree_data($tree, $seed);
		} 	
		if($args['opentoselection'] || $args['opento']){ 	
			$tree_id = wpdt_get_tree_id($tree); 
			$openTo = '';			
			if($tree_id){
				if($args['opentoselection'] && isset($_SERVER['REQUEST_URI'])){	
					$openTo .= wpdt_open_tree_to($_SERVER['REQUEST_URI'], $tree_id, $tree);		
				}
				if($args['opento']){ //force open to			
					$openTo .= wpdt_force_open_to($args['opento'], $tree_id, $tree);	
				}
			}
			if($openTo){
				$openScript = ($opt['openscript']) ? $opt['openscript'] : "<script type='text/javascript'>"; //this happens for some reason?
				$closeScript = ($opt['closescript']) ? $opt['closescript'] : '</script>' ; 
				$tree .= $openScript . $openTo . $closeScript;	
			}
		}
		unset($opt);
		wpdt_print_errors();
		return $tree;
	}	
	
	function wpdt_get_defaults($treetype){
		$common = array('title' => '', 'cache'=> 1, 'opento' => '', 'uselines' => 1, 'useicons' => 0, 
			'exclude' => '', 'closelevels' => 1, 'folderlinks' => 0, 'showselection' => 0, 'include' => '',
			'opentoselection' => 1,'truncate' => 0, 'sort_order' => 'ASC', 'sortby' => 'ID', 'treetype' => $treetype,
			'openlink' 	=> __('open all', 'wpdtree'), 'closelink' => __('close all', 'wpdtree'), 'oclink_sep' => ' | '
		);		
		if($treetype == 'arc'){			
			return array_merge($common, array(				
				'title' => __('Archives', 'wpdtree'),
				'sortby' 	=> 'post_date',
				'sort_order'=> 'DESC',
				'exclude_cats' => '',				
				'listposts' => 1,				
				'showrss' 	=> 0,
				'type' 		=> 'monthly',
				'showcount' => 1,		//show_post_count 
				'limit_posts'=> 0,
				'number_of_posts'=> 0
			));
		}else if($treetype == 'cat'){
			return array_merge($common, array(
				//should implement: exclude_tree (string) (only applicable to wp_list_categories). Exclude category-tree from the results.
				'title' => __('Categories', 'wpdtree'),								
				'cpsortby' 		=> 'post_date',
				'cpsortorder' 	=> 'DESC',			
				'hide_empty' 	=> 1,
				'child_of' 		=> 0,
				'parent' 		=> 'none', //there is no default for parents.
				'allowdupes' 	=> 1,
				'postexclude' 	=> '',
				'listposts' 	=> 1,									
				'showrss' 		=> 0,
				'showcount' 	=> 0,	//show_count
				'taxonomy' 		=> 'category',			
				'pad_counts' 	=> 1,
				'hierarchical' 	=> 0,
				'number' 		=> 0,
				'limit_posts'	=> 0,
				'more_link' 	=> "Show more (%excluded%)...", //if number of posts-limit is hit, show link to full category listing
				'include_last_update_time' => 0
			));		
		}else if($treetype == 'tax'){
			return array_merge($common, array(				
				'title' => __('Taxonomy', 'wpdtree'),								
				'cpsortby' 		=> 'post_date',
				'cpsortorder' 	=> 'DESC',			
				'hide_empty' 	=> 1,
				'child_of' 		=> 0,
				'parent' 		=> 'none', //there is no default for parents.
				'allowdupes' 	=> 1,
				'postexclude' 	=> '',
				'listposts' 	=> 1,									
				'showrss' 		=> 0,
				'showcount' 	=> 0,	//show_count
				'taxonomy' 		=> 'taxonomy', //or any registered taxonomy			
				'pad_counts' 	=> 1,
				'hierarchical' 	=> 0,
				'number' 		=> 0,
				'limit_posts'	=> 0,
				'more_link' 	=> "Show more (%excluded%)...", //if number of posts-limit is hit, show link to full category listing
				'include_last_update_time' => 0
			));		
		}else if($treetype == 'pge'){
			return array_merge($common, array(
				'title' => __('Pages', 'wpdtree'),
				'folderlinks' 	=> 1,
				//'sort_column' 	=> '', //handle inconsistent argument names in WordPress API. Other functions use 'sortby'.
				'meta_key' 		=> '',
				'meta_value' 	=> '',
				'authors' 		=> '',
				'child_of'		=> 0, 
				'parent' 		=> -1,
				'exclude_tree' 	=> -1,
				'number' 		=> -1,
				'offset' 		=> 0,
				'hierarchical' 	=> 1				
			));				
		}else if($treetype == 'lnk'){
			return array_merge($common, array(
				//limit -1
				'title' => __('Links', 'wpdtree'),
				'opentoselection' => 0,
				'useselection' 	=> 0,
				'showcount'		=> 0,
				'catsorderby'	=> 'name',
				'catssort_order'=> 'ASC',
				'folderlinks' 	=> 0,			
				'sortby' 		=> 'name',
				//'orderby'       => 'name', //inconsistent argument names in WordPress API. All others use 'sortby'.				
				//'order'         => 'ASC', //other uses 'sort_order'								
				'category'      => '', //Comma separated list of bookmark category ID's.
				'category_name' => '', //Category name of a catgeory of bookmarks to retrieve. Overrides category parameter.
				'hide_invisible'=> 1,
				'show_updated'  => 0,								
				'search'        => '' //Searches link_url, link_name or link_description like the search string.				
			));				
		}else{
			return array(				
				'openscript'=> "\n<script type='text/javascript'>\n/* <![CDATA[ */\ntry{\n",
				'closescript'=> "}catch(e){} /* ]]> */\n</script>\n",
				'addnoscript'=> 0,
				'version' 	=> wpdt_get_version(),
				'animate' 	=> 1, 
				'duration' 	=> 250,
				'disable_css'=> 0
			);
		}
	}
		
	function wpdt_install_options(){						
		$old = get_option('wpdt_options');
		$default = wpdt_get_defaults('gen'); //general settings	
		if(isset($old['genopt'])){ //old leftovers from previous version. Nukem.
			update_option('wpdt_options', $default);
		}else{
			$new = array_merge($default,$old);
			$new['version'] = wpdt_get_version(); 
			update_option('wpdt_options',$new);
		}		
	}

	function wpdt_option_page(){
		if(!function_exists('current_user_can') || !current_user_can('manage_options') ){
			die(__('Cheatin&#8217; uh?'));
		}				
		require_once('wp-dtree-cache.php');	 
		add_action('in_admin_footer', 'wpdt_add_admin_footer');
		$oplain	= "\n<script type='text/javascript'>\ntry{\n";	
		$cplain = "}catch(e){}</script>\n";
		$ohtml = "\n<script type='text/javascript'>\n<!--\ntry{\n";
		$chtml = "}catch(e){} //-->\n</script>\n";
		$oxml = "\n<script type='text/javascript'>\n/* <![CDATA[ */\ntry{\n";		
		$cxml = "}catch(e){} /* ]]> */\n</script>\n";
		$opt = get_option('wpdt_options');		
		if($opt['version'] != wpdt_get_version()){
			wpdt_install_options(); //update options if the user forgot to disable the plugin prior to upgrading.
			$opt = get_option('wpdt_options');			
		}				
		if(isset($_POST['submit'])){			
			$opt['version'] = wpdt_get_version();	
			$opt['duration'] = intval($_POST['duration']);
			$opt['animate'] = isset($_POST['animate']) ? 1 : 0;	
			$opt['addnoscript'] = isset($_POST['addnoscript']) ? 1 : 0;
			$opt['disable_css'] = isset($_POST['disable_css']) ? 1 : 0;
			if($_POST['openscript'] == 'html'){
				$opt['openscript'] = $ohtml;
				$opt['closescript'] = $chtml;
			}else if($_POST['openscript'] == 'xml'){
				$opt['openscript'] = $oxml;
				$opt['closescript'] = $cxml;
			}else{
				$opt['openscript'] = $oplain;
				$opt['closescript'] = $cplain;
			}
			update_option('wpdt_options', $opt);
			echo '<div id="message" class="updated wpdtfade" style="background: #ffc;border: 1px solid #333;"><p><font color="black">'.__('WP-dTree settings updated...','wpdtree').'</font><br /></p></div>';						
			echo $oxml.'jQuery("div.wpdtfade").delay(2000).fadeOut("slow");'.$cxml;
			wpdt_update_cache();
		}		
	?>	
	
	<form method="post">
	<div class="wrap">									
		<h2><?php esc_html_e('WP-dTree General Settings','wpdtree'); ?></h2>				
		<table class="optiontable" width="80%">
			<fieldset class="options">
			<tr><td valign="top">
			<p style="font-weight:bold;">Widget-settings are in <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/widgets.php">the widget panels</a>.</p>			
			<p>
				<label for="animate" title="<?php esc_attr_e('Use jquery to animate the tree opening/closing.','wpdtree'); ?>"><?php esc_html_e('Animate:', 'wpdtree'); ?></label>
				<input class="checkbox" type="checkbox" <?php checked($opt['animate'], true ); ?> id="animate" name="animate" /> 								
				<input type="text" value="<?php echo $opt['duration']; ?>" name="duration" id="duration" size="10" />
				<label><?php esc_html_e('Duration (milliseconds)', 'wpdtree'); ?></label>
			</p><p>
				<label for="disable_css" title="<?php esc_attr_e('To style the trees, copy wp-dtree.css to your themes\'s stylesheet and edit that. Then disable this.','wpdtree'); ?>"><?php _e('Disable WP-dTree\'s default stylesheet:', 'wpdtree'); ?></label>
				<input class="checkbox" type="checkbox" <?php checked($opt['disable_css'], true ); ?> id="disable_css" name="disable_css" /> 			
			</p><p>
				<label for="addnoscript" title="<?php esc_attr_e('Outputs normal archives/pages/links/categories, to no-javascript users. Doubles the size of each tree!','wpdtree'); ?>"><?php _e('Include <a href="http://www.w3schools.com/tags/tag_noscript.asp">noscript</a> fallbacks:', 'wpdtree'); ?></label>
				<input class="checkbox" type="checkbox" <?php checked($opt['addnoscript'], true ); ?> id="addnoscript" name="addnoscript" /> 			
			</p><p>
				<label for="openscript" title="<?php esc_attr_e('Might be useful for validation of your site','wpdtree'); ?>"><?php esc_html_e('Javascript escape method:', 'wpdtree'); ?></label> 
				<select id="openscript" name="openscript">
					<option value="html" <?php selected($ohtml, $opt['openscript']);?>><?php esc_html_e('<!--'); ?></option>
					<option value="xml" <?php selected($oxml, $opt['openscript']);?>><?php esc_html_e('/* <![CDATA[ */'); ?></option>				
					<option value="plain" <?php selected($oplain, $opt['openscript']);?>>(no escaping)</option>
				</select>
			</p>
			<p><input id="submit" type="submit" name="submit" value="<?php esc_attr_e('Update Settings &raquo;') ?>" /></p>			
			</td><td><?php include_once(plugin_dir_path(__FILE__).'about.php'); ?> </td></tr>			
			</fieldset>												
		</table>
										
	</div>		
	</form>
	<?php
}
?>