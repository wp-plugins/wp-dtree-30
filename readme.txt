=== WP-dTree ===
Contributors: ulfben
Donate link: http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17
Tags: archive, navigation, dynamic, dtree, tree, sidebar, 
Requires at least: 2.3
Tested up to: 2.7-beta3
Stable tag: 3.5

Turns your sidebar into a very convenient, dynamic navigation tree. Supports scriptaculous effects.

== Description ==

This plugin can generate [navigation trees](http://www.destroydrop.com/javascripts/tree/) for your posts, pages, links and categories. It uses Scriptaculous for awesome display effects.

WP-dTree was originally created by [Christopher Hwang](http://www.silpstream.com/blog/). Since Mr. Hwang went MIA, [Ulf Benjaminsson](http://www.ulfben.com/) forked the plugin (as of version 3.x). 

The fork is focused on [performance improvements](http://wordpress.org/extend/plugins/wp-dtree-30/faq/), but packs a lot of new features and modernizations to boot; WP 2.7 compability, widgets, out-of-the-box Scriptaculous support, link trees, feed icons and more.

**Changes in v3.5** (2008-11-26)

* New option: "shut down unused trees" (performance!)
* New option: "force open to"
* New option: per-tree truncation setting
* New option: custom sort order for archives
* New option: custom sort order for posts in categories
* New option: exclude posts from category tree
* New option: more CSS options avaliable from the admin
* Added: widget preview in the admin area
* Added: link target attributes in link tree
* Added: path defines to support non-standard WP-installations
* Added: uninstall.php for nice WP 2.7 plugin cleanup.
* Fixed: include sub-categories when counting posts
* Fixed: "close same level" 
* Fixed: Quotes "" in titles breaks alt-texts
* Fixed: Nestled cats get excluded if parent is empty
* Fixed: RSS-icons don't show in IE
* Fixed: Unwanted spacing in IE
* Misc: improved admin screen feng-shui.
* Misc: Moved config screen to "settings"-section of admin
* Misc: CSS should be a bit more robust now

[Older changelogs moved here](http://wordpress.org/extend/plugins/wp-dtree-30/other_notes/).

If you appreciate the work I put into WP-dTree and want to motivate further development - please consider buying me [a used book](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x), like *these* wonderful people did:

* Bruce Hampton, USA
* Shu Mei Chen, Taiwan
* Kai Kniepkamp, Germany

Thank you all, *very* much!

== Installation ==

1. If upgrading: disable the old version first
1. Transfer the 'wp-dtree-30' folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the 'WP-dTree' under 'Settings' to adjust your preferences
1. Go to 'Presentation' -> 'Widgets' and drag-n-drop the widgets to the relevant section of your sidebar

If you think that widgets are lame: edit the template file that you want the archives to show on:

Displaying archives
---------
	<li>
	<h2>Archives</h2>
	<ul>
		<?php 	
			if (function_exists('wp_dtree_get_archives')){				
		   	    wp_dtree_get_archives();
			}else{
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
				if (function_exists('wp_dtree_get_categories')){
					wp_dtree_get_categories();
				}else{
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
				if (function_exists('wp_dtree_get_pages')){
					wp_dtree_get_pages();
				}else{
					wp_list_pages();				
				} 
			?>				
		</ul>
	</li>
	
Displaying links:
---------
	<li>
	<h2>Links</h2>
		<ul>
			<?php 
 			if(function_exists('wp_dtree_get_links')){				
			    wp_dtree_get_links();
			}else{
				wp_list_bookmarks(); 
			}
		</ul>
	</li> 
	
== Changelog == 

(Older entries moved here to clear up [the front page](http://wordpress.org/extend/plugins/wp-dtree-30/))

**Changes in v3.4.2** (2008-10-19)

* Bug: incorrect WP version detection. ([thanks: StMD](http://wordpress.org/support/topic/211402))

**Changes in v3.4.1** (2008-07-20)

* Validates: both CSS and XHTML 1.0 Transitional ([thanks: ar-jar](http://wordpress.org/support/topic/189643))

**Changes in v3.4** (2008-07-12)

* Added support for link trees. (needs testing!)
* Fixed breakage in WP 2.5, 2.6
* Fixed invalid XHTML output. ([props: jberghem](http://wordpress.org/support/topic/150888))
* Fixed a CSS-issue. ([props: wenzlerm](http://wordpress.org/support/topic/186314))
* Renamed the dTree script to avoid collisions with plugins using an unmodified version.

**Changes in v3.3.2** (2007-11-26)

* Fixed bug with excluding multiple categories.

**Changes in v3.3.1** (2007-11-02)

* Removed redundant `li`-tags from widgets. (props: Alexey Zamulla) 
* Support for non-ascii characters. ([props: michuw](http://wordpress.org/support/topic/141554))
* Properly encoded ampersands (&) in javascript URLs.

**Changes in v3.3** (2007-10-26)

* Optimized dtree, up to **40% less data** is stored and transfered! 
* New option: Show RSS icon for archives
* New option: Show post count for archives
* Fix: Open to requested node
* Fix: images URL not working on some servers ([props: Zarquod](http://wordpress.org/support/topic/136547))
* Fix: somewhat more IE compatible...

*Known issues:* RSS icons wont show **in IE** if `post count` is on.

**Changes in v3.2** (2007-10-15)

* Support for WP's bundled scriptacolous library! (turn effects on in the WP-dTree options page)
* New cache structure reduces cache size with ~33% compared to previous implementations.	 
* New option: Show RSS icon for categories
* New option: Show post count for categories
* New option: Effect duration

*Regressions:* `open to selection` is broken again. It'll be back in the next version, but if it's vital for you, stay with 3.1

**Changes in v3.1:** (2007-10-06)

* Updated to comply with WordPress 2.3's new taxonomy tables for categories.
* Widgetized! You no longer need to edit your sidebar manually.
* Fixed "Open To Selection"-option.
	
== Frequently Asked Questions ==

= How do I ask for help? =
1. Search [the forums](http://wordpress.org/tags/wp-dtree-30) and post in a relevant thread if one exists.
1. Always tag your post with `WP-dTree 3.0`
1. State your problem succintly, *provide a link*!
1. If it broke after you changed a setting, let us know this!
1. If it's a *style* problem, read on...

= Why does WP-dTree look horrible on my blog? = 
99% of the time, it's *your theme* that breaks the layout. Run the default WP theme (Kubric) to quickly confirm this. Then follow these simple steps (in your theme) to find the cause.

1. Get the [Web Developer Plugin](https://addons.mozilla.org/firefox/addon/60) for firefox.
1. Right-click anywhere on your blog, select `Web Developer -> CSS -> Edit CSS` and fiddle around with anything related to the sidebar, images, lists or links. Changes are applied in realtime so you'll know when you've struck gold.

If the fix is something that *can* and *should* be supplied by WP-dTree itself, please *post the fix* on the forums.

= Why does dTree timeout on Internet Explorer? =
It's an inherent limitation with the IE Javascript engine. dTree does a lot of string juggling, and any large-ish amount of data *will* take a while to get through. 

There is no hard limit, but my experience suggest that IE chokes around 110-120K (WP-dTree prints its size in your blog source). In practice this is rarely a problem, but if you hit the limits it's probably with a category tree and `list posts` on. Keep each post in *a single category* (use tags instead!) to keep your category tree slim. 
 
= Why should I disable unused trees? = 
Even if you never display the tree, WP-dTree will create and store it every time you add or edit content on your blog. By disabling unused trees you can save your server from going through that effort. If you've got a large(ish) number of posts, this will speed up posting and editing quite noticeably.

= How does the 3.x fork improve performance of WP-dTree? =
Instead of generating all the trees *on every visit*, 3.x employs caching - building the trees *only when you add or alter content on your blog*. The result is a tremendous load reduction from previous versions; the devsite (~360 posts, Kubrik theme) went from 411 to 18 queries (!) to display the main page.

Of course, caching also yelds a *significant* reduction in how much processing is needed for each visit. Unless you activate `Open to requested node`, all WP-dTree does is to print a static string.

Version 3.3 brought optimizations to [the dtree javascript](http://www.destroydrop.com/javascripts/tree/) itself too, further reducing the amount of data to store and transmit to create the trees.

= Can I change the images used by WP-dTree? =

The images are all stored in the 'wp-dtree-30/dtree-img/' directory. You can change them if you like. Just remember to keep the names the same, or you'll break the script.

= Can I help you with anything? = 
Yes please!

We desperately need a proper overhaul of the admin interface. It's not standards compliant and it looks terrible in WP 2.7. If you (unlike me...) are a proper front-end developer - feel free to send me a patch.

Same goes for CSS and styling - I really need someone to have a look at it and make it *proper*. Compliant, fail safe, robust, sane.

For coders, I'd like help on optimizing what hooks WP-dTree uses and *how* they're used. I see a lot of potential in the "new" variable hooks, but they're poorly documented. The plugin should *always* fire when (relevant) content is edited, but we should *only* run the methods necessary. We shouldn't re-generate Links, Pages and Archive-trees when the user adds a new *category* to the database.

Finally - if you've got more money than time - I always appreciate a [(used) book](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17).

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