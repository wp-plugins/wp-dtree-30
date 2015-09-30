<?php
class WPDT_Menu_Widget extends WPDT_Widget{	
	function __construct() {			
		$widget_ops = array('classname' => 'wpdt-menu', 'description' => __('Your custom menus, the dTree way.', 'wp-dtree-30') ); //widget settings. 
		$control_ops = array('width' => 200, 'height' => 350, 'id_base' => 'wpdt-menu-widget'); //Widget control settings.
		parent::__construct('wpdt-menu-widget', __('WP-dTree Menu', 'wp-dtree-30'), $widget_ops, $control_ops, "poo" ); //Create the widget.		
	}
	
	function widget($args, $settings){
		parent::widget($args, $settings);
	}

	function update($new_settings, $old_settings) {// Update the widget settings.					
		$old_settings = parent::update($new_settings, $old_settings);
		$settings = $old_settings;
		$settings['menuslug'] 	= isset($new_settings['menuslug']) ? $new_settings['menuslug'] : '';	
		$settings['treetype']	= 'mnu';		
		return $settings;
	}

	function form($settings) {
		$defaults = wpdt_get_defaults('mnu');	
		$settings = wp_parse_args((array) $settings, $defaults); 
		parent::form($settings);
		$menus = wp_get_nav_menus(array('orderby'=>'name','hide_empty'=>true));		
	?>
		<p>
		
			<label for="<?php echo $this->get_field_id('menuslug'); ?>"><?php _e('Select Menu:', 'wp-dtree-30'); ?></label> 	
			<select id="<?php echo $this->get_field_id('menuslug'); ?>" name="<?php echo $this->get_field_name('menuslug'); ?>" class="widefat" style="width:100px;">	
				<?php 
					if(is_array($menus)){
						foreach($menus as $menu){
							$name = esc_html($menu->name);
							$slug = $menu->slug;							
							echo "<option value='{$slug}'" . selected($settings['menuslug'], $slug) .">{$name}</option>";
						}					
					}
				?>				
			</select>	
		</p>			
	<?php
	}
}

?>