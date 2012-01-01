<?php
class WPDT_Archives_Widget extends WPDT_Widget {
		
	function __construct() {				
		$widget_ops = array('classname' => 'wpdt-archives', 'description' => __('Dynamic javascript archives', 'wpdtree') ); //widget settings. 
		$control_ops = array('width' => 200, 'height' => 350, 'id_base' => 'wpdt-archives-widget'); //Widget control settings.
		parent::__construct('wpdt-archives-widget', __('WP-dTree Archives', 'wpdtree'), $widget_ops, $control_ops ); //Create the widget.       			
	}
	
	function widget($args, $settings){ 
		parent::widget($args, $settings);
	}
	
	function update($new_settings, $old_settings) {
		$old_settings = parent::update($new_settings, $old_settings);
		$settings = $old_settings;				
		$settings['type'] 		= 	$new_settings['type'];		
		$settings['listposts'] 	= isset($new_settings['listposts']) ? 1 : 0;	
		$settings['showcount'] 	= isset($new_settings['showcount']) ? 1 : 0;	
		$settings['showrss'] 	= isset($new_settings['showrss']) ? 1 : 0;	
		$settings['limit_posts'] = intval($new_settings['limit_posts']);
		$settings['exclude_cats'] = wpdt_clean_exclusion_list($new_settings['exclude_cats']);
		$settings['include'] 	= '';		
		$settings['treetype']	= 'arc';
		return $settings;
	}
	
	function form($settings){
		$defaults = wpdt_get_defaults('arc');	
		$settings = wp_parse_args((array) $settings, $defaults); 
		parent::form($settings);
	?>
		<p>
			<label for="<?php echo $this->get_field_id('exclude_cats'); ?>" title="A comma-separated list of category IDs to be excluded from the tree (example: 3,7,31)."><?php _e('Exclude cats:', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('exclude_cats'); ?>" name="<?php echo $this->get_field_name('exclude_cats'); ?>" value="<?php echo $settings['exclude_cats']; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Sort by:', 'wpdtree'); ?></label> 	
			<select id="<?php echo $this->get_field_id('sortby'); ?>" name="<?php echo $this->get_field_name('sortby'); ?>" class="widefat" style="width:100px;">	
				<option value="post_title"<?php selected($settings['sortby'], 'post_title');?>>Title</option>
				<option value="menu_order"<?php selected($settings['sortby'], 'menu_order');?>>Menu Order</option>
				<option value="post_date"<?php selected($settings['sortby'], 'post_date');?>>Date</option>
				<option value="ID"<?php selected($settings['sortby'], 'ID');?>>ID</option>
				<option value="post_modified"<?php selected($settings['sortby'], 'post_modified');?>>Modified</option>
				<option value="post_author"<?php selected($settings['sortby'], 'post_author');?>>Author</option>
				<option value="post_name"<?php selected($settings['sortby'], 'post_name');?>>Slug</option>					
			</select>	
		</p><p>
			<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Type:', 'wpdtree'); ?></label> 
			<select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" class="widefat" style="width:75px;">
				<option <?php selected('yearly', $settings['type']);?>>yearly</option>
				<option <?php selected('monthly',$settings['type']);?>>monthly</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('limit_posts'); ?>" title="Number of posts to display under each year / month (0 to display all)"><?php _e('Limit posts:', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('limit_posts'); ?>" name="<?php echo $this->get_field_name('limit_posts'); ?>" value="<?php echo $settings['limit_posts']; ?>" style="width:3em;" />		
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['listposts'], true); ?> id="<?php echo $this->get_field_id('listposts'); ?>" name="<?php echo $this->get_field_name('listposts'); ?>" /> 
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