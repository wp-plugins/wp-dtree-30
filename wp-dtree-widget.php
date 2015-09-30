<?php

class WPDT_Widget extends WP_Widget{	
	function __construct($id_base = false, $name, $widget_options = array(), $control_options = array()){		
		parent::__construct($id_base, $name, $widget_options, $control_options);
	}
	
	function widget($args, $settings){
		extract($args);		
		$title = apply_filters('widget_title', esc_html__($settings['title'], 'wp-dtree-30'));
		echo $before_widget; //defined by theme		
		if($title){
			echo $before_title . $title . $after_title; //defined by theme
		}
		echo '<div class="dtree">';
		wpdt_print_tree($settings);		
		echo '</div>';		
		echo $after_widget; //defined by theme
	}

	function update($new_settings, $old_settings){
		require_once('wp-dtree-cache.php');		
		$seed = wpdt_get_seed($old_settings);
		wpdt_clear_cache($seed);
		$settings = $old_settings;	
		$settings['title'] 			= strip_tags($new_settings['title']);
		$settings['sort_order'] 	= isset($new_settings['sort_order']) ? $new_settings['sort_order'] : 'DESC'; //asc / desc
		$settings['sortby'] 		= isset($new_settings['sortby']) ? $new_settings['sortby'] : ''; //sort_columns is native for pages. 	
		$settings['openlink'] 		= isset($new_settings['openlink']) ? strip_tags($new_settings['openlink']) : '';
		$settings['closelink'] 		= isset($new_settings['closelink']) ? strip_tags($new_settings['closelink']) : '';
		$settings['oclink_sep'] 	= isset($new_settings['oclink_sep']) ? strip_tags($new_settings['oclink_sep']) : '';
		$settings['uselines'] 		= isset($new_settings['uselines'])? 1 : 0;
		$settings['useicons'] 		= isset($new_settings['useicons'])? 1 : 0;
		$settings['exclude'] 		= isset($new_settings['exclude']) ? wpdt_clean_exclusion_list($new_settings['exclude']) : '';
		$settings['include'] 		= isset($new_settings['include']) ? wpdt_clean_exclusion_list($new_settings['include']) : '';
		$settings['opento'] 		= isset($new_settings['opento'])  ?	$new_settings['opento'] : '';//comma-separated list of node titles, IDs or request URLs
		$settings['closelevels'] 	= isset($new_settings['closelevels'])? 1 : 0;
		$settings['folderlinks'] 	= isset($new_settings['folderlinks'])? 1 : 0;
		$settings['showselection'] 	= isset($new_settings['showselection'])? 1 : 0;
		$settings['opentoselection'] = isset($new_settings['opentoselection'])? 1 : 0; //not applicable for linktrees but what the hey.		
		$settings['truncate'] 		= isset($new_settings['truncate']) ? intval($new_settings['truncate']) : 0;				
		$settings['cache'] 			= isset($new_settings['cache'])? 1 : 0;
		return $settings;
	}
	function form($settings){		
		$treetype = $settings['treetype'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $settings['title']; ?>" style="width:95%;" />
		</p>
		<?php if($treetype == 'arc'): /*only applicable to archives*/?>			
			<input id="<?php echo $this->get_field_id('posttype'); ?>" name="<?php echo $this->get_field_name('posttype'); ?>" value="<?php echo $settings['posttype']; ?>" size="10" />
			<label for="<?php echo $this->get_field_id('posttype'); ?>" title="'post', 'attachment' or any custom post type"><?php _e('Specify <a href="http://codex.wordpress.org/Post_Types">Post type</a>', 'wp-dtree-30'); ?></label><br />
		<?php endif; /*only applicable to archives*/ ?>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($settings['cache'], true); ?> id="<?php echo $this->get_field_id('cache'); ?>" name="<?php echo $this->get_field_name('cache'); ?>" /> 
			<label for="<?php echo $this->get_field_id('cache'); ?>" title="Do not disable this unless you're a developer or having problems with the trees not updating properly!"><?php _e('Cache (recommended!)', 'wp-dtree-30'); ?></label>
		</p><p>		
			<input type="text" value="<?php echo $settings['openlink']; ?>" name="<?php echo $this->get_field_name('openlink'); ?>" id="<?php echo $this->get_field_id('openlink'); ?>" size="10" />
			<label for="<?php echo $this->get_field_id('openlink'); ?>"><?php esc_html_e('"open all"-link', 'wp-dtree-30'); ?></label>
			<br />
			<input type="text" value="<?php echo $settings['oclink_sep']; ?>" name="<?php echo $this->get_field_name('oclink_sep'); ?>" id="<?php echo $this->get_field_id('oclink_sep'); ?>" size="10" />
			<label for="<?php echo $this->get_field_id('oclink_sep'); ?>"><?php esc_html_e('open/close all separator', 'wp-dtree-30'); ?></label>	
			<br />
			<input type="text" value="<?php echo $settings['closelink']; ?>" name="<?php echo $this->get_field_name('closelink'); ?>" id="<?php echo $this->get_field_id('closelink'); ?>" size="10" />
			<label for="<?php echo $this->get_field_id('closelink'); ?>"><?php esc_html_e('"close all"-link', 'wp-dtree-30'); ?></label>					
		</p><p>	
			<input class="checkbox" type="checkbox" <?php checked($settings['uselines'], true); ?> id="<?php echo $this->get_field_id('uselines'); ?>" name="<?php echo $this->get_field_name('uselines'); ?>" /> 
			<label for="<?php echo $this->get_field_id('uselines'); ?>" title="Draw the dotted lines"><?php _e('Use lines', 'wp-dtree-30'); ?></label>
			<input class="checkbox" type="checkbox" <?php checked($settings['useicons'], true); ?> id="<?php echo $this->get_field_id('useicons'); ?>" name="<?php echo $this->get_field_name('useicons'); ?>" /> 
			<label for="<?php echo $this->get_field_id('useicons'); ?>" title="Show icons next to nodes"><?php _e('Use icons', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['closelevels'], true); ?> id="<?php echo $this->get_field_id('closelevels'); ?>" name="<?php echo $this->get_field_name('closelevels'); ?>" /> 
			<label for="<?php echo $this->get_field_id('closelevels'); ?>" title="Keep only one node open at a time."><?php _e('Close same level', 'wp-dtree-30'); ?></label>
		</p><p>
			<label for="<?php echo $this->get_field_id('truncate'); ?>" title="Limit node titles to this number of characters. (0 to disable)"><?php _e('Truncate titles:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('truncate'); ?>" name="<?php echo $this->get_field_name('truncate'); ?>" value="<?php echo $settings['truncate']; ?>" style="width:3em;" />
		</p>
		<?php if($treetype != 'lnk'): /*Not applicable to Links*/?>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($settings['folderlinks'], true); ?> id="<?php echo $this->get_field_id('folderlinks'); ?>" name="<?php echo $this->get_field_name('folderlinks'); ?>" /> 
			<label for="<?php echo $this->get_field_id('folderlinks'); ?>" title="If on folder-nodes (categories, archive year / months)can be browsed."><?php _e('Folders are links', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['showselection'], true); ?> id="<?php echo $this->get_field_id('showselection'); ?>" name="<?php echo $this->get_field_name('showselection'); ?>" /> 
			<label for="<?php echo $this->get_field_id('showselection'); ?>" title="Highligt current node. It applies the class '.dtree a.nodeSel' for you to style as you'd like."><?php _e('Highlight selection', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['opentoselection'], true); ?> id="<?php echo $this->get_field_id('opentoselection'); ?>" name="<?php echo $this->get_field_name('opentoselection'); ?>" /> 
			<label for="<?php echo $this->get_field_id('opentoselection'); ?>" title="Open tree to the current page/post/category."><?php _e('Open to selection', 'wp-dtree-30'); ?></label>		
		</p>
		<?php endif; //Not applicable to Links ?>
		<?php if($treetype != 'mnu'): ?>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>" title="A comma-separated list of post/category/page/link IDs to be excluded from the tree (example: 3,7,31). For Links, this applies to both link categories- and links!"><?php _e('Exclude:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" value="<?php echo $settings['exclude']; ?>"/>
		</p>
		<?php if($treetype != 'arc'): /*Does not apply to Archives*/?>
		<p>
			<label for="<?php echo $this->get_field_id('include'); ?>" title="Only include certain categories/pages in the tree (example: 3,7,31). For Links, this only applies to categories"><?php _e('Include:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('include'); ?>" name="<?php echo $this->get_field_name('include'); ?>" value="<?php echo $settings['include']; ?>"/>
		</p>
		<?php endif; //Does not apply to Archives ?>
		<p>
			<label for="<?php echo $this->get_field_id('opento'); ?>" title="Always open tree to a specified node (comma separated list of node IDs or request URLs. 'all' to open entire tree):"><?php _e('Force open to:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('opento'); ?>" name="<?php echo $this->get_field_name('opento'); ?>" value="<?php echo $settings['opento']; ?>" style="width:50%"/>
		</p><p>
			<label for="<?php echo $this->get_field_id('sort_order'); ?>"><?php _e('Order:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('sort_order'); ?>" name="<?php echo $this->get_field_name('sort_order'); ?>" class="widefat" style="width:65px;">
				<option <?php if ('ASC' == $settings['sort_order'])echo 'selected="selected"'; ?>>ASC</option>
				<option <?php if ('DESC' == $settings['sort_order'])echo 'selected="selected"'; ?>>DESC</option>
			</select>
		</p>
		<?php endif; //mnu ?>
		<hr />
	<?php
	}
}

?>