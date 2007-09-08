=== wp-dTree 3.0 ===
Contributors: Christopher Hwang, Ulf Benjaminssson
Donate link: https://www.fsf.org/associate/support_freedom
Tags: archive, navigation, dynamic, dtree, tree
Requires at least: 2.0.2
Tested up to: 2.2.3
Stable tag: trunk

Generates a JS navigation tree for your blog, with support for scriptaculous effects. Version 3.0 implements caching - reducing loads significantly. 

== Description ==

This plugin was created by [Christopher Hwang](http://www.silpstream.com/blog/) to generate smooth JavaScript navigation trees for your archives. It's a WP adaption of [Geir Landros's dTree](http://www.destroydrop.com/javascripts/tree/).

It supports navigation through a yearly or a monthly tree, and you can also have trees for your categories and pages. The category tree can be displayed with or without their posts.

Scriptaculous support is built in for cool presentation effects. It is optional and can be controlled through in the admin interface (Presentation -> WP-dTree). If you choose to activate this setting, you'll need to [download WP-Scriptaculous](http://www.silpstream.com/blog/wp-scriptaculous/).

Version 3.0 is a fork by Ulf Benjaminsson, aimed at reducing the excessive database querying when running wp-dTree. Instead of creating the trees on every visit, 3.0 employs caching and updates the trees only when posts/pages or categories are altered.

The result is a tremendous load reduction from previous versions; one site I tested (~360 posts) went from 411 to 18 queries (!) to display the main page.


== Installation ==

1. Extract the files and transfer the 'wp-dtree-3.0' folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the 'WP-dTree' option menu under 'Presentation' and set your preferences.
1. Go to the template file that you want the archives to show on and copy paste the relevant code:

Displaying archives
---------
	<li>
	<h2>Archives</h2>
	<ul>
		<?php 	
			if (function_exists('silpstream_wp_dtree_get_archives'))		
			{				
			     silpstream_wp_dtree_get_archives();
			}
			else
			{
				wp_get_archives('type=monthly'); 
			} 
		?>				
	</ul>
	</li>

Displaying categories:
---------
	<li>
	<h2>Categories</h2>
		<ul>
			<?php 
				if (function_exists('silpstream_wp_dtree_get_categories')) 
				{
					silpstream_wp_dtree_get_categories();
				}
				else
				{
					wp_list_categories('show_count=1');
				} 
			?>				
		</ul>
	</li>

Displaying pages:
---------
	<li>
	<h2>Pages</h2>
		<ul>
			<?php 
				if (function_exists('silpstream_wp_dtree_get_pages')) 
				{
					silpstream_wp_dtree_get_pages();
				}
				else
				{
					wp_list_pages();				
				} 
			?>				
		</ul>
	</li>

All trees can be displayed at the same time if you like. Just add the relevant code in to your page.

== Frequently Asked Questions ==

= Can I change the images used by wp-dTree? =

The images are all stored in the 'wp-dtree/dtree-img/' directory. You can change them if you like. Just remember to keep the names the same, or you'll break the script.

== Screenshots ==

1. The archive navigation tree in action.
2. The admin configuration screen.

== Other Notes ==
Copyright (C) 2006 Christopher Hwang (email: chris at silpstream dot com).
3.0 fork by Ulf Benjaminsson (ulf at ulfben dot com).

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


