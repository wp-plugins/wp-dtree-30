<?php
class WPDT_Taxonomies_Widget extends WPDT_Widget{	
	function __construct() {		
		$widget_ops = array('classname' => 'wpdt-taxonomies', 'description' => __('An experimental dTree for your custom taxonomies.', 'wp-dtree-30') ); //widget settings. 
		$control_ops = array('width' => 200, 'height' => 350, 'id_base' => 'wpdt-taxonomies-widget'); //Widget control settings.
		parent::__construct('wpdt-taxonomies-widget', __('WP-dTree Taxonomies (beta)', 'wp-dtree-30'), $widget_ops, $control_ops ); //Create the widget.
	}
	
	function widget($args, $settings){		
		parent::widget($args, $settings);
	}
	function update($new_settings, $old_settings){		
		$old_settings = parent::update($new_settings, $old_settings);
		$settings = $old_settings;		
		$settings['taxonomy'] 		= isset($new_settings['taxonomy']) ? $new_settings['taxonomy'] : 'category';
		$settings['usedescription'] = isset($new_settings['usedescription']) ? 1 : 0;						
		$settings['listposts'] 		= isset($new_settings['listposts']) ? 1 : 0;						
		$settings['showrss'] 		= isset($new_settings['showrss']) ? 1 : 0;
		$settings['hide_empty'] 	= isset($new_settings['hide_empty']) ? 1 : 0;
		$settings['showcount'] 		= isset($new_settings['showcount']) ? 1 : 0;				
		$settings['allowdupes'] 	= isset($new_settings['allowdupes']) ? 1 : 0;
		$setting['exclude_tree']	= isset($new_settings['exclude_tree']) ? $new_settings['exclude_tree'] : '';
		$settings['postexclude']	= isset($new_settings['postexclude']) ? wpdt_clean_exclusion_list($new_settings['postexclude']) : '';		
		$settings['cpsortby'] 		= isset($new_settings['cpsortby']) ? $new_settings['cpsortby'] : ''; //sort posts
		$settings['cpsortorder']	= isset($new_settings['cpsortorder']) ? $new_settings['cpsortorder'] : 'asc'; //order of posts (asc/desc)
		$settings['child_of'] 		= isset($new_settings['child_of']) ? intval($new_settings['child_of']) : 0;
		$settings['child_of_current'] = isset($new_settings['child_of_current']) ? 1 : 0;
		$settings['parent'] 		= (isset($new_settings['parent']) && is_numeric($new_settings['parent']) && intval($new_settings['parent']) >= 0) ? intval($new_settings['parent']) : 'none';
		$settings['number'] 		= isset($new_settings['number']) ? intval($new_settings['number']) : 0;
		$settings['limit_posts'] 	= isset($new_settings['limit_posts']) ? intval($new_settings['limit_posts']) : 0;
		$settings['more_link'] 		= isset($new_settings['more_link']) ? strip_tags($new_settings['more_link']) : '';
		$settings['treetype'] 		= 'tax';
		$settings['title_li'] 		= ''; //the widget already prints a title. (this is only for the the noscript output, which is from wp_list_Taxonomies()
		if(is_numeric($settings['parent'])){$settings['child_of'] = 0;}
		if($settings['child_of']){$settings['parent'] = ''; $settings['hide_empty'] = 0;}
		return $settings;		
	}	
	function form($settings){
		$defaults = wpdt_get_defaults('tax');	
		$settings = wp_parse_args((array) $settings, $defaults); 
		parent::form($settings);
	?>
		<p>
			<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy name:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>" value="<?php echo $settings['taxonomy']; ?>" style="width:95%;" />
		</p><p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>" title="<?php esc_attr_e('Sort taxonomies alphabetically or by unique taxonomy ID. The default is sort by taxonomy ID.','wp-dtree-30');?>"><?php _e('Sort by:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('sortby'); ?>" name="<?php echo $this->get_field_name('sortby'); ?>" class="widefat" style="width:65px;">
				<option <?php selected('name',$settings['sortby']); ?>>name</option>				
				<option <?php selected('id',$settings['sortby']); ?>>id</option>
				<option <?php selected('slug',$settings['sortby']); ?>>slug</option>
				<option <?php selected('count',$settings['sortby']); ?>>count</option>
				<option <?php selected('group',$settings['sortby']); ?>>group</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('cpsortby'); ?>"><?php _e('Sort posts by:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('cpsortby'); ?>" name="<?php echo $this->get_field_name('cpsortby'); ?>" class="widefat" style="width:100px;">
				<option value='post_title' <?php selected('post_title',$settings['cpsortby']); ?>>Title</option>
				<option value='menu_order' <?php selected('menu_order',$settings['cpsortby']); ?>>Menu Order</option>
				<option value='post_date' <?php selected('post_date',$settings['cpsortby']); ?>>Date</option>
				<option value='ID' <?php selected('ID',$settings['cpsortby']); ?>>ID</option>
				<option value='post_modified' <?php selected('post_modified',$settings['cpsortby']); ?>>Modified</option>
				<option value='post_author' <?php selected('post_author',$settings['cpsortby']); ?>>Author</option>
				<option value='post_name' <?php selected('post_name',$settings['cpsortby']); ?>>Slug</option>				
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('cpsortorder'); ?>"><?php _e('Post order:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('cpsortorder'); ?>" name="<?php echo $this->get_field_name('cpsortorder'); ?>" class="widefat" style="width:65px;">
				<option <?php selected('ASC',$settings['cpsortorder']); ?>>ASC</option>
				<option <?php selected('DESC',$settings['cpsortorder']); ?>>DESC</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('number'); ?>" title="<?php esc_attr_e('Number of taxonomies to display. (0 to display all)','wp-dtree-30');?>"><?php _e('Limit:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php echo $settings['number']; ?>" style="width:3em;" />
		</p><p>
			<label for="<?php echo $this->get_field_id('limit_posts'); ?>" title="<?php esc_attr_e('Number of posts to display under each Taxonomy (0 to display all)','wp-dtree-30');?>"><?php _e('Limit posts:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('limit_posts'); ?>" name="<?php echo $this->get_field_name('limit_posts'); ?>" value="<?php echo $settings['limit_posts']; ?>" style="width:3em;" />		
		</p><p>	
			<label for="<?php echo $this->get_field_id('more_link'); ?>" title="<?php esc_attr_e('Show link to additional taxonomy content. %excluded% is replaced with the remaining count.','wp-dtree-30'); ?>"><?php esc_html_e('Show more link:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('more_link'); ?>" name="<?php echo $this->get_field_name('more_link'); ?>" value="<?php echo $settings['more_link']; ?>" style="width:95%;"/>
		</p><p>
	
		<?php 
			// Only show these options if taxonomy is hierarchical 
			if (isset($settings['taxonomy']) && is_taxonomy_hierarchical($settings['taxonomy'])) { 
		?>		
			<label for="<?php echo $this->get_field_id('child_of'); ?>" title="<?php esc_attr_e('Display alltaxonomies that are descendants (i.e. children & grandchildren) of the Taxonomy.','wp-dtree-30'); ?>"><?php _e('Show descendands of:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('child_of'); ?>" name="<?php echo $this->get_field_name('child_of'); ?>" class="widefat" style="width:100%;">
				<option value="0" <?php selected(0,$settings['child_of']); ?>><?php echo esc_attr(__('Select an ancestor')); ?></option> 
			<?php 				
				foreach (get_terms($settings['taxonomy']) as $term) {
					$sel = ($term->term_id == $settings['child_of']) ? 'selected="selected"' : '';
					echo "<option value='{$term->term_id}'{$sel}>{$term->name} (ID: {$term->term_id})</option>\n";								
				}
			 ?>
			</select>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['child_of_current'], true); ?> id="<?php echo $this->get_field_id('child_of_current'); ?>" name="<?php echo $this->get_field_name('child_of_current'); ?>" /> 
			<label for="<?php echo $this->get_field_id('child_of_current'); ?>" title="Active taxonomy becomes the parent node of the tree."><?php _e('Set "Child of" to active taxonomy', 'wp-dtree-30'); ?></label>
		</p><p>			
			<label for="<?php echo $this->get_field_id('parent'); ?>" title="<?php esc_attr_e('Display only taxonomies that are direct descendants (i.e. children only) of the Taxonomy. This does NOT work like the \'child_of\' parameter.','wp-dtree-30'); ?>"><?php _e('Only *direct* children of:', 'wp-dtree-30'); ?></label> 			
			<select id="<?php echo $this->get_field_id('parent'); ?>" name="<?php echo $this->get_field_name('parent'); ?>" class="widefat" style="width:100%;">				
				<option value="none" <?php selected('',$settings['parent']); ?>><?php echo esc_attr(__('Don\'t filter on parents')); ?></option> 
				<option value="0" <?php selected(0,$settings['parent']); ?>><?php echo esc_attr(__('Root (0)')); ?></option>
			<?php 				 
				foreach (get_terms($settings['taxonomy']) as $term) {
					$sel = ($term->term_id == $settings['parent']) ? 'selected="selected"' : '';
					echo "<option value='{$term->term_id}'{$sel}>{$term->name} (ID: {$term->term_id})</option>\n";								
				}
			?>
			</select>		
		</p><p>
		<?php } //end hierarchical check ?>
			<label for="<?php echo $this->get_field_id('postexclude'); ?>" title="<?php esc_attr_e('Comma separated list of post IDs. The "exclude"-filed above is for taxonomy IDs','wp-dtree-30') ?>"><?php esc_html_e('Exclude posts:', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('postexclude'); ?>" name="<?php echo $this->get_field_name('postexclude'); ?>" value="<?php echo $settings['postexclude']; ?>" style="width:100px;" />
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['usedescription'], true); ?> id="<?php echo $this->get_field_id('usedescription'); ?>" name="<?php echo $this->get_field_name('usedescription'); ?>" /> 
			<label for="<?php echo $this->get_field_id('usedescription'); ?>" title="<?php esc_attr_e('Use the taxonomy description instead of name when rendering the tree.','wp-dtree-30'); ?>"><?php _e('Use description', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['allowdupes'], true); ?> id="<?php echo $this->get_field_id('allowdupes'); ?>" name="<?php echo $this->get_field_name('allowdupes'); ?>" /> 
			<label for="<?php echo $this->get_field_id('allowdupes'); ?>" title="<?php esc_attr_e('Allow posts sorted under multiple Taxonomies? Otherwise the post will appear only in the first of its Taxonomies.','wp-dtree-30'); ?>"><?php _e('Allow duplicate entries', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['hide_empty'], true); ?> id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" /> 
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e('Hide empty taxonomies', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['listposts'], 1); ?> id="<?php echo $this->get_field_id('listposts'); ?>" name="<?php echo $this->get_field_name('listposts'); ?>" /> 
			<label for="<?php echo $this->get_field_id('listposts'); ?>"><?php _e('List posts', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['showcount'], true); ?> id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" /> 
			<label for="<?php echo $this->get_field_id('showcount'); ?>"><?php _e('Show post count', 'wp-dtree-30'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['showrss'], true); ?> id="<?php echo $this->get_field_id('showrss'); ?>" name="<?php echo $this->get_field_name('showrss'); ?>" /> 
			<label for="<?php echo $this->get_field_id('showrss'); ?>"><?php _e('Show RSS', 'wp-dtree-30'); ?></label>
		</p>
	<?php	
	}
}

?>