=== WP-dTree 3.3.1 ===
Contributors: ulfben, Christopher Hwang
Donate link: http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17
Tags: archive, navigation, dynamic, dtree, tree, sidebar, 
Requires at least: 2.2
Tested up to: 2.3.1
Stable tag: trunk

Turns your sidebar into a very convenient, dynamic navigation tree. Supports scriptaculous effects.

== Description ==

This plugin can generate [navigation trees](http://www.destroydrop.com/javascripts/tree/) for your posts, pages and categories. It uses Scriptaculous for awesome display effects (enable in admin panel).

WP-dTree was originally created by [Christopher Hwang](http://www.silpstream.com/blog/). Since Mr. Hwang went MIA, Ulf Benjaminsson forked the plugin (as of version 3.x). 

The fork is focused on [performance improvements](http://wordpress.org/extend/plugins/wp-dtree-30/faq/), but it packs a lot of new features and modernizations to boot; WP 2.3 compability, widgets, out-of-the-box Scriptaculous support, feed icons, post counts and more.

If you enjoy WP-dTree and would like to suggest a specific feature, or just motivate further development - please consider buying me [a used book](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x).

**Changes in v3.3.1** (ulfben - 2007-11-02)

1. Removed redundant `li`-tags from widgets. (props: Alexey Zamulla) 
1. Support for non-ascii characters. ([props: michuw](http://wordpress.org/support/topic/141554))
1. Properly encoded ampersands (&) in javascript URLs.

**Changes in v3.3** (2007-10-26)

1. Optimized dtree, up to **40% less data** is stored and transfered! 
1. New option: Show RSS icon for archives
1. New option: Show post count for archives
1. Fix: Open to requested node
1. Fix: images URL not working on some servers ([props: Zarquod](http://wordpress.org/support/topic/136547))
1. Fix: somewhat more IE compatible...

*Known issues:* RSS icons wont show **in IE** if `post count` is on.

**Changes in v3.2** (2007-10-15)

1. Support for WP's bundled scriptacolous library! (turn effects on in the WP-dTree options page)
1. New cache structure reduces cache size with ~33% compared to previous implementations.	 
1. New option: Show RSS icon for categories
1. New option: Show post count for categories
1. New option: Effect duration

*Regressions:* `open to selection` is broken again. It'll be back in the next version, but if it's vital for you, stay with 3.1

**Changes in v3.1:** (2007-10-06)

1. Updated to comply with WordPress 2.3's new taxonomy tables for categories.
1. Widgetized! You no longer need to edit your sidebar manually.
1. Fixed "Open To Selection"-option.

**Changes in v3.0:**

1. Added caching to reduce the database load. 

== Installation ==

Make sure to disable and remove any previous installation of WP-dTree first! As of v3.2, the code for showing the trees have changed. Make sure to update your sidebar accordingly if you are not using widgets to display your archive.

1. Extract the files and transfer the 'wp-dtree-30' folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the 'WP-dTree' option menu under 'Presentation' and set your preferences.
1. Go to 'Presentation' -> 'Widgets' and drag-n-drop WP-dTree Archives / Categories / Pages to the relevant section of your sidebar. 
1. If you think that widgets are lame: go to the template file that you want the archives to show on and copy paste the relevant code:

Displaying archives
---------
	<li>
	<h2>Archives</h2>
	<ul>
		<?php 	
			if (function_exists('wp_dtree_get_archives'))		
			{				
		   	    wp_dtree_get_archives();
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
				if (function_exists('wp_dtree_get_categories')) 
				{
					wp_dtree_get_categories();
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
				if (function_exists('wp_dtree_get_pages')) 
				{
					wp_dtree_get_pages();
				}
				else
				{
					wp_list_pages();				
				} 
			?>				
		</ul>
	</li>


== Frequently Asked Questions ==

= How does the 3.x fork improve performance of WP-dTree? =
Instead of generating all the trees *on every visit*, 3.x employs caching - building the trees *only when you add or alter content on your blog*. The result is a tremendous load reduction from previous versions; the devsite (~360 posts, Kubrik theme) went from 411 to 18 queries (!) to display the main page.

Of course, caching also yelds a *significant* reduction in how much processing is needed for each visit. Unless you activate `Open to requested node`, all WP-dTree does is to print a static string.

Version 3.3 brought optimizations to [the dtree javascript](http://www.destroydrop.com/javascripts/tree/) itself too, further reducing the amount of data to store and transmit to create the trees.

= I need feature XYZ! Can you make it for me? =
Yes probably, but I will ask you for a favor in return:

Really small jobs might cost you a post card.<br />
Medium sized jobs might cost you [a used book or two](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x).<br />
Larger requests might involve dollars changing owner. <br />

Send me your request (ulf at ulfben dot com) and I'll let you know. 

*Please note that this is **free** code - you are allowed (and indeed - encouraged) to modify it yourself.*

= Can I change the images used by WP-dTree? =

The images are all stored in the 'wp-dtree/dtree-img/' directory. You can change them if you like. Just remember to keep the names the same, or you'll break the script.

== Screenshots ==

1. The archive navigation tree in action.
2. The admin configuration screen.

== Other Notes ==
Copyright (C) 2007 Ulf Benjaminsson (email: ulf at ulfben dot com).

Copyright (C) 2006 Christopher Hwang (email: chris at silpstream dot com).

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