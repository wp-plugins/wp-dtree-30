<?php
class WPDT_Categories_Widget extends WPDT_Widget{	
	function WPDT_Categories_Widget(){		
		$widget_ops = array('classname' => 'wpdt-categories', 'description' => __('Dynamic category list', 'wpdtree') ); //widget settings. 
		$control_ops = array('width' => 200, 'height' => 350, 'id_base' => 'wpdt-categories-widget'); //Widget control settings.
		$this->WP_Widget('wpdt-categories-widget', __('WP-dTree Categories', 'wpdtree'), $widget_ops, $control_ops ); //Create the widget.
	}
	
	function widget($args, $settings){		
		parent::widget($args, $settings);
	}
	function update($new_settings, $old_settings){		
		$old_settings = parent::update($new_settings, $old_settings);
		$settings = $old_settings;						
		$settings['listposts'] 	= isset($new_settings['listposts']) ? 1 : 0;						
		$settings['showrss'] 	= isset($new_settings['showrss']) ? 1 : 0;
		$settings['hide_empty'] = isset($new_settings['hide_empty']) ? 1 : 0;
		$settings['showcount'] 	= isset($new_settings['showcount']) ? 1 : 0;				
		$settings['allowdupes'] = isset($new_settings['allowdupes']) ? 1 : 0;
		$setting['exclude_tree']= $new_settings['exclude_tree'];
		$settings['postexclude']= wpdt_clean_exclusion_list($new_settings['postexclude']);		
		$settings['cpsortby'] 	= $new_settings['cpsortby']; //sort posts
		$settings['cpsortorder']= $new_settings['cpsortorder']; //order of posts (asc/desc)
		$settings['child_of'] 	= intval($new_settings['child_of']);
		$settings['parent'] 	= (is_numeric($new_settings['parent']) && intval($new_settings['parent']) >= 0) ? intval($new_settings['parent']) : 'none';
		$settings['number'] 	= intval($new_settings['number']);
		$settings['treetype'] 		= 'cat';
		$settings['title_li'] 	= ''; //the widget already prints a title. (this is only for the the noscript output, which is from wp_list_categories()
		if(is_numeric($settings['parent'])){$settings['child_of'] = 0;}
		if($settings['child_of']){$settings['parent'] = ''; $settings['hide_empty'] = 0;}
		return $settings;		
	}	
	function form($settings){
		$defaults = wpdt_get_defaults('cat');	
		$settings = wp_parse_args((array) $settings, $defaults); 
		parent::form($settings);
	?>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>" title="Sort categories alphabetically or by unique category ID. The default is sort by Category ID."><?php _e('Sort by:', 'wpdtree'); ?></label> 
			<select id="<?php echo $this->get_field_id('sortby'); ?>" name="<?php echo $this->get_field_name('sortby'); ?>" class="widefat" style="width:65px;">
				<option <?php selected('name',$settings['sortby']); ?>>name</option>				
				<option <?php selected('id',$settings['sortby']); ?>>id</option>
				<option <?php selected('slug',$settings['sortby']); ?>>slug</option>
				<option <?php selected('count',$settings['sortby']); ?>>count</option>
				<option <?php selected('group',$settings['sortby']); ?>>group</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('cpsortby'); ?>"><?php _e('Sort posts by:', 'wpdtree'); ?></label> 
			<select id="<?php echo $this->get_field_id('cpsortby'); ?>" name="<?php echo $this->get_field_name('cpsortby'); ?>" class="widefat" style="width:90px;">
				<option <?php selected('post_title',$settings['cpsortby']); ?>>post_title</option>
				<option <?php selected('post_date',$settings['cpsortby']); ?>>post_date</option>
				<option <?php selected('ID',$settings['cpsortby']); ?>>ID</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('cpsortorder'); ?>"><?php _e('Post order:', 'wpdtree'); ?></label> 
			<select id="<?php echo $this->get_field_id('cpsortorder'); ?>" name="<?php echo $this->get_field_name('cpsortorder'); ?>" class="widefat" style="width:65px;">
				<option <?php selected('ASC',$settings['cpsortorder']); ?>>ASC</option>
				<option <?php selected('DESC',$settings['cpsortorder']); ?>>DESC</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('number'); ?>" title="Number of categories to display. (0 to display all)"><?php _e('Limit:', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php echo $settings['number']; ?>" style="width:3em;" />
		</p><p>			
			<label for="<?php echo $this->get_field_id('child_of'); ?>" title="Display all categories that are descendants (i.e. children & grandchildren) of the category."><?php _e('Show descendands of:', 'wpdtree'); ?></label> 
			<select id="<?php echo $this->get_field_id('child_of'); ?>" name="<?php echo $this->get_field_name('child_of'); ?>" class="widefat" style="width:100%;">
				<option value="0" <?php selected(0,$settings['child_of']); ?>><?php echo attribute_escape(__('Select an ancestor')); ?></option> 
			<?php 				
				foreach (get_categories() as $category) {
					$sel = ($category->term_id == $settings['child_of']) ? 'selected="selected"' : '';
					echo "<option value='{$category->term_id}'{$sel}>{$category->cat_name} (ID: {$category->term_id})</option>\n";								
				}
			 ?>
			</select>
		</p><p>			
			<label for="<?php echo $this->get_field_id('parent'); ?>" title="Display only categories that are direct descendants (i.e. children only) of the category. This does NOT work like the 'child_of' parameter."><?php _e('Only *direct* children of:', 'wpdtree'); ?></label> 
			<?php echo 'poo'.$settings['parent']; ?>
			<select id="<?php echo $this->get_field_id('parent'); ?>" name="<?php echo $this->get_field_name('parent'); ?>" class="widefat" style="width:100%;">				
				<option value="none" <?php selected('',$settings['parent']); ?>><?php echo attribute_escape(__('Don\'t filter on parents')); ?></option> 
				<option value="0" <?php selected(0,$settings['parent']); ?>><?php echo attribute_escape(__('Root (0)')); ?></option>
			<?php 				 
				foreach (get_categories() as $category) {
					$sel = ($category->term_id == $settings['parent']) ? 'selected="selected"' : '';
					echo "<option value='{$category->term_id}'{$sel}>{$category->cat_name} (ID: {$category->term_id})</option>\n";								
				}
			?>
			</select>		
		</p><p>
			<label for="<?php echo $this->get_field_id('postexclude'); ?>" title="<?php esc_attr_e('Comma separated list of post IDs. The "exclude"-filed above if for category IDs') ?>"><?php esc_html_e('Exclude posts:', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('postexclude'); ?>" name="<?php echo $this->get_field_name('postexclude'); ?>" value="<?php echo $settings['postexclude']; ?>" style="width:100px;" />
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['allowdupes'], true); ?> id="<?php echo $this->get_field_id('allowdupes'); ?>" name="<?php echo $this->get_field_name('allowdupes'); ?>" /> 
			<label for="<?php echo $this->get_field_id('allowdupes'); ?>" title="Allow posts sorted under multiple categories? Otherwise the post will appear only in the first of its categories."><?php _e('Allow duplicate entries', 'wpdtree'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['hide_empty'], true); ?> id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" /> 
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e('Hide empty categories', 'wpdtree'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['listposts'], 1); ?> id="<?php echo $this->get_field_id('listposts'); ?>" name="<?php echo $this->get_field_name('listposts'); ?>" /> 
			<label for="<?php echo $this->get_field_id('listposts'); ?>"><?php _e('List posts', 'wpdtree'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['showcount'], true); ?> id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" /> 
			<label for="<?php echo $this->get_field_id('showcount'); ?>"><?php _e('Show post count', 'wpdtree'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['showrss'], true); ?> id="<?php echo $this->get_field_id('showrss'); ?>" name="<?php echo $this->get_field_name('showrss'); ?>" /> 
			<label for="<?php echo $this->get_field_id('showrss'); ?>"><?php _e('Show RSS', 'wpdtree'); ?></label>
		</p>
	<?php	
	}
}

?>