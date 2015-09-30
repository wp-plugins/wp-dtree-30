<?php
class WPDT_Pages_Widget extends WPDT_Widget{	
	function __construct() {			
		$widget_ops = array('classname' => 'wpdt-pages', 'description' => __('Add dTree navigation for your pages.', 'wp-dtree-30') ); //widget settings. 
		$control_ops = array('width' => 200, 'height' => 350, 'id_base' => 'wpdt-pages-widget'); //Widget control settings.
		parent::__construct('wpdt-pages-widget', __('WP-dTree Pages', 'wp-dtree-30'), $widget_ops, $control_ops ); //Create the widget.		
	}
	
	function widget($args, $settings){
		parent::widget($args, $settings);
	}

	function update($new_settings, $old_settings) {// Update the widget settings.					
		$old_settings = parent::update($new_settings, $old_settings);
		$settings = $old_settings;								
		$settings['meta_key'] 		= isset($new_settings['meta_key']) ? $new_settings['meta_key'] : '';
		$settings['meta_value'] 	= isset($new_settings['meta_value']) ? $new_settings['meta_value'] : '';
		$settings['authors'] 		= isset($new_settings['authors']) ? wpdt_clean_exclusion_list($new_settings['authors']) : '';
		$settings['exclude_tree'] 	= isset($new_settings['exclude_tree']) ? $new_settings['exclude_tree'] : '';
		//$settings['number'] 		= $new_settings['number'];
		//$settings['offset'] 		= intval($new_settings['offset']);
		$settings['child_of'] 		= isset($new_settings['child_of']) ? intval($new_settings['child_of']) : 0; 
		$settings['child_of_current'] = isset($new_settings['child_of_current']) ? 1 : 0;
		$settings['parent'] 		= isset($new_settings['parent']) ? intval($new_settings['parent']) : 0;
		$settings['treetype'] 		= 'pge';
		$settings['title_li']		= '';//the widget already prints a title. (this is only for the the noscript output, which is from wp_list_pages()
		if($settings['parent'] != -1){$settings['child_of'] = 0;}		
		return $settings;
	}

	function form($settings) {
		$defaults = wpdt_get_defaults('pge');	
		$settings = wp_parse_args((array) $settings, $defaults); 
		parent::form($settings);
	?>		
		<p>The following settings expose parameters of <code>get_pages()</code>. Please check <a href="http://codex.wordpress.org/Function_Reference/get_pages#Parameters">the WordPress Codex</a> for more information.</p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Sort by:', 'wp-dtree-30'); ?></label> 	
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
			<label for="<?php echo $this->get_field_id('meta_key'); ?>" title="Only include the pages that have this Custom Field Key / Value"><?php _e('Meta key and value:', 'wp-dtree-30'); ?></label><br />
			<input id="<?php echo $this->get_field_id('meta_key'); ?>" name="<?php echo $this->get_field_name('meta_key'); ?>" value="<?php echo $settings['meta_key']; ?>" style="width:40%;" />
			<input id="<?php echo $this->get_field_id('meta_value'); ?>" name="<?php echo $this->get_field_name('meta_value'); ?>" value="<?php echo $settings['meta_value']; ?>" style="width:40%;" />
		</p><p>
			<label for="<?php echo $this->get_field_id('authors'); ?>" title="Only include the pages written by the given author(s) ID. (comma-separated list of IDs)"><?php _e('Author ID(s):', 'wp-dtree-30'); ?></label>
			<input id="<?php echo $this->get_field_id('authors'); ?>" name="<?php echo $this->get_field_name('authors'); ?>" value="<?php echo $settings['authors']; ?>" style="width:30%;" />		
		</p><p>
			<label for="<?php echo $this->get_field_id('child_of'); ?>" title="Display all pages that are descendants (i.e. children & grandchildren) of this page"><?php _e('Show only children of:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('child_of'); ?>" name="<?php echo $this->get_field_name('child_of'); ?>" class="widefat" style="width:100%;">
				<option value="0" <?php selected(0, $settings['child_of']); ?>><?php echo esc_attr('(allow all)'); ?></option> 
			<?php 				
				foreach (get_pages() as $page) {					
					echo "<option value='{$page->ID}'"; selected($page->ID, $settings['child_of']); echo ">{$page->post_title} (ID: {$page->ID})</option>\n";								
				}
			 ?>
			</select>		
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['child_of_current'], true); ?> id="<?php echo $this->get_field_id('child_of_current'); ?>" name="<?php echo $this->get_field_name('child_of_current'); ?>" /> 
			<label for="<?php echo $this->get_field_id('child_of_current'); ?>" title="Active page becomes the parent node of the tree."><?php _e('Set "Child of" to active page', 'wp-dtree-30'); ?></label>
		</p><p>
			<label for="<?php echo $this->get_field_id('parent'); ?>" title="Display only pages that are direct descendants (i.e. children only) of the page. This does NOT work like the 'child_of' parameter."><?php _e('Show only *direct* children of:', 'wp-dtree-30'); ?></label> 			
			<select id="<?php echo $this->get_field_id('parent'); ?>" name="<?php echo $this->get_field_name('parent'); ?>" class="widefat" style="width:100%;">				
				<option value="-1" <?php selected(-1,$settings['parent']);?>><?php echo esc_attr('(allow all parents)'); ?></option> 
			<?php 				 
				foreach (get_pages() as $page) {
					echo "<option value='{$page->ID}'"; selected($page->ID, $settings['parent']); echo ">{$page->post_title} (ID: {$page->ID})</option>\n";							
				}
			?>
			</select>		
		</p><p>
			<label for="<?php echo $this->get_field_id('exclude_tree'); ?>" title="The opposite of 'child_of', 'exclude_tree' will remove all children of a given ID from the results. Useful for hiding all children of a given page. Can also be used to hide grandchildren in conjunction with a 'child_of' value."><?php _e('Show no children from:', 'wp-dtree-30'); ?></label> 
			<select id="<?php echo $this->get_field_id('exclude_tree'); ?>" name="<?php echo $this->get_field_name('exclude_tree'); ?>" class="widefat" style="width:100%;">
				<option value="0" <?php selected(0,$settings['exclude_tree']);?>><?php echo esc_attr('(exclude nothing)'); ?></option> 
			<?php 				
				foreach (get_pages() as $page) {
					echo "<option value='{$page->ID}'"; selected($page->ID, $settings['exclude_tree']); echo ">{$page->post_title} (ID: {$page->ID})</option>\n";						
				}
			 ?>
			</select>		
		</p>
	<?php
	}
}

?>