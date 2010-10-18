<?php
class WPDT_Links_Widget extends WPDT_Widget{	
	function WPDT_Links_Widget(){				
		$widget_ops = array('classname' => 'wpdt-links', 'description' => __('Dynamic javascript links', 'wpdtree')); //widget settings. 
		$control_ops = array('width' => 200, 'height' => 350, 'id_base' => 'wpdt-links-widget'); //Widget control settings.
		$this->WP_Widget('wpdt-links-widget', __('WP-dTree Links', 'wpdtree'), $widget_ops, $control_ops); //Create the widget.		
	}
	
	function widget($args, $settings){
		parent::widget($args, $settings);
	}

	function update($new_settings, $old_settings){
		$old_settings = parent::update($new_settings, $old_settings);
		$settings = $old_settings;		
		$settings['catsorderby'] 	= $new_settings['catsorderby'];	
		$settings['catssort_order'] = $new_settings['catssort_order'];		
		$settings['category']      	= wpdt_clean_exclusion_list($settings['include'].','.$new_settings['category']); //Comma separated list of bookmark category ID's.
		$settings['category_name'] 	= $new_settings['category_name']; //name of a category of bookmarks to retrieve. Overrides category parameter.
		$settings['hide_invisible']	= isset($new_settings['hide_invisible']) ? 1 : 0;
		$settings['show_updated']  	= isset($new_settings['show_updated']) ? 1 : 0;
		$settings['showcount']		= isset($new_settings['showcount']) ? 1 : 0;
		$settings['search']        	= $new_settings['search']; //Searches link_url, link_name or link_description like the search string.
		$settings['orderby']		= $new_settings['sortby']; //work around inconsistent API
		$settings['order']			= $new_settings['sort_order'];
		$settings['opentoselection']= 0;
		$settings['useselection'] 	= 0;
		$settings['folderlinks']	= 0;
		$settings['include']		= '';
		$settings['treetype'] 		= 'lnk';
		return $settings;
	}

	function form($settings){
		$defaults = wpdt_get_defaults('lnk');			
		$settings = wp_parse_args((array) $settings, $defaults); 
		parent::form($settings);
	?>			
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Sort links by:', 'wpdtree'); ?></label> 	
			<select id="<?php echo $this->get_field_id('sortby'); ?>" name="<?php echo $this->get_field_name('sortby'); ?>" class="widefat" style="width:80px;">	
				<option value="id"<?php selected($settings['sortby'], 'id');?>>id</option>
				<option value="url"<?php selected($settings['sortby'], 'url');?>>url</option>
				<option value="name"<?php selected($settings['sortby'], 'name');?>>name</option>
				<option value="target"<?php selected($settings['sortby'], 'target');?>>target</option>
				<option value="description"<?php selected($settings['sortby'], 'description');?>>description</option>
				<option value="owner"<?php selected($settings['sortby'], 'owner');?>>owner</option>
				<option value="rating"<?php selected($settings['sortby'], 'rating');?>>rating</option>
				<option value="updated"<?php selected($settings['sortby'], 'updated');?>>updated</option>					
				<option value="rel"<?php selected($settings['sortby'], 'rel');?>>rel</option>					
				<option value="notes"<?php selected($settings['sortby'], 'notes');?>>notes</option>					
				<option value="rss"<?php selected($settings['sortby'], 'rss');?>>rss</option>					
				<option value="length"<?php selected($settings['sortby'], 'length');?>>slug</option>					
				<option value="rand"<?php selected($settings['sortby'], 'rand');?>>random</option>					
			</select>	
		</p><p>
			<label for="<?php echo $this->get_field_id('catssort_order'); ?>"><?php _e('Order categories by:', 'wpdtree'); ?></label> 
			<select id="<?php echo $this->get_field_id('catssort_order'); ?>" name="<?php echo $this->get_field_name('catssort_order'); ?>" class="widefat" style="width:65px;">
				<option <?php if ('ASC' == $settings['catssort_order'] ) echo 'selected="selected"'; ?>>ASC</option>
				<option <?php if ('DESC' == $settings['catssort_order'] ) echo 'selected="selected"'; ?>>DESC</option>
			</select>
		</p><p>
			<label for="<?php echo $this->get_field_id('catsorderby'); ?>"><?php _e('Sort categories by:', 'wpdtree'); ?></label> 	
			<select id="<?php echo $this->get_field_id('catsorderby'); ?>" name="<?php echo $this->get_field_name('catsorderby'); ?>" class="widefat" style="width:75px;">	
				<option value="id"<?php selected($settings['catsorderby'], 'id');?>>id</option>
				<option value="slug"<?php selected($settings['catsorderby'], 'slug');?>>slug</option>
				<option value="name"<?php selected($settings['catsorderby'], 'name');?>>name</option>
				<option value="count"<?php selected($settings['catsorderby'], 'count');?>>count</option>
			</select>	
		</p><p>
			<label for="<?php echo $this->get_field_id('category'); ?>" title="Comma separated list of bookmark category ID's."><?php _e('Only from categories (IDs):', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" value="<?php echo $settings['category']; ?>" style="width:95%;" />
		</p><p>
			<label for="<?php echo $this->get_field_id('category_name'); ?>" title="Name of a category of bookmarks to retrieve. Overrides category ID's above."><?php _e('Only from category (name):', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('category_name'); ?>" name="<?php echo $this->get_field_name('category_name'); ?>" value="<?php echo $settings['category_name']; ?>" style="width:95%;" />
		</p><!--<p>
			<label for="<?php echo $this->get_field_id('search'); ?>" title="Searches link_url, link_name or link_description like the search string."><?php _e('Search: (unused)', 'wpdtree'); ?></label>
			<input id="<?php echo $this->get_field_id('search'); ?>" name="<?php echo $this->get_field_name('search'); ?>" value="<?php echo $settings['search']; ?>" style="width:95%;" />
		</p>--><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['showcount'], true); ?> id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" /> 
			<label for="<?php echo $this->get_field_id('showcount'); ?>"><?php _e('Show link count', 'wpdtree'); ?></label>
		</p><p>			
			<input class="checkbox" type="checkbox" <?php checked($settings['hide_invisible'],1); ?> id="<?php echo $this->get_field_id('hide_invisible'); ?>" name="<?php echo $this->get_field_name('hide_invisible'); ?>" /> 
			<label for="<?php echo $this->get_field_id('hide_invisible'); ?>"><?php _e('Hide invisible', 'wpdtree'); ?></label>
		</p><p>
			<input class="checkbox" type="checkbox" <?php checked($settings['show_updated'],1); ?> id="<?php echo $this->get_field_id('show_updated'); ?>" name="<?php echo $this->get_field_name('show_updated'); ?>" /> 
			<label for="<?php echo $this->get_field_id('show_updated'); ?>"><?php _e('Show updated', 'wpdtree'); ?></label>
		</p>
		
	<?php
	}
}
?>