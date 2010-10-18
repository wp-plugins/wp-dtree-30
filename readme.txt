=== WP-dTree ===
Contributors: ulfben
Donate link: http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17
Tags: archive, navigation, category, pages, links, bookmarks, dynamic, dtree, tree, sidebar, 
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: trunk

<a href="http://www.destroydrop.com/javascripts/tree/">Dynamic tree</a>-widgets to replace the standard archives-, categories-, pages- and link lists.

== Description ==

This plugin generates [navigation trees](http://www.destroydrop.com/javascripts/tree/) to replace the WordPress standard archives-, categories-, pages- and link lists. It provides you with widgets so you can use [the awesome tree navigation](http://game.hgo.se/cat/projects/3d-games/) without editing any code. But it also exposes several [new template tags](http://wordpress.org/extend/plugins/wp-dtree-30/installation/) if you want to get your hands dirty.

First: to all of you who used previous versions of WP-dTree - *sorry for keeping you waiting!*

WP-dTree 4.0 is a complete re-write, bringing the plugin up to speed with the much matured WordPress 3.x API. The overhaul has made WP-dTree significantly more sane and robust; handling "foreign" characters gracefully, being more in tune with your theme, playing nice with translators and offering proper fallbacks for those who surf without JavaScript.

There is so much new functionality and so many new features that I consider WP-dTree 4.0 to be an entirely new plugin. This means I've had very little formal testing done (see `known issues` below). You might want to hang back for a week and let the early adopters work out the kinks with me.

For those brave enough to try a .0 release - please explore and play with all the settings. And [let me know](http://wordpress.org/tags/wp-dtree-30) when something breaks (provide links!).

If you value [my plugins](http://profiles.wordpress.org/users/ulfben/) and want to motivate further development - please help me out by [signing up with DropBox](http://www.dropbox.com/referrals/NTIzMDI3MDk)! It's a cross-plattform application to sync your files online and across computers. 2GB account is *free* and my refferal earns you a 250MB bonus! 

Or if you want to spend money, feel free to [send me a book](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x), like *these* wonderful people did:

* Bruce Hampton, USA
* Shu Mei Chen, Taiwan
* Kai Kniepkamp, Germany

Thank you all, *very* much!

**Changes in v4.0** (2010-10-17)

* Completely recoded the plugin from the ground up for a much needed code overhaul.
* Added support for multiple tree instances
* Added support for per-tree settings (incl. caching)
* Added translation support
* Added (optional) noscript-fallback for JS-disabled visitors.
* Added setting for type of JS-hiding (XML, HTML or none) to ease page validation
* Added support for links title attribute and category descriptions
* Replaced Scriptacolous with jQuery
* Made animation JS optional (not even included on page if disabled)
* Minified JS and CSS (9KB vs. 16KB!)
* Made truncation optional (titles can be as long as you want!)
* Cache is created once when blog is visited, instead of everytime you add content.
* Removed support for WP <2.3	
* Removed all CSS-options from admin area (style your theme instead)
* Removed animation effects
* Removed all non-essential CSS-styling (inhering from your theme instead)
* **all previous settings will be lost!** Write them down before upgrading.

**Known issues:** 

* Only tested in Chrome with TwentyTen (default theme). 
* opentoselection doesn't handle paging

[Older changelogs moved here](http://wordpress.org/extend/plugins/wp-dtree-30/changelog/).

== Installation ==

**Note:** You will lose all your settings when upgrading from 3.5 to 4.0.

1. If upgrading: *disable the old version first*!
1. Transfer the 'wp-dtree-30' folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the 'WP-dTree' under 'Settings' to adjust your preferences
1. Go to 'Presentation' -> 'Widgets' and drag-n-drop the widgets to the relevant section of your sidebar(s)

To style the widgets, please read the [FAQ](http://wordpress.org/extend/plugins/wp-dtree-30/faq/).

For developers, WP-dTree exposes the following [template tag functions](http://codex.wordpress.org/Template_Tags):

* `wpdt_list_archives();` 			
* `wpdt_list_categories();` 	
* `wpdt_list_pages();`
* `wpdt_list_links();`	
* `wpdt_list_bookmarks();   //same as wpdt_list_links`

They all take an optional `$args` - a [query-string or associative array](http://codex.wordpress.org/How_to_Pass_Tag_Parameters#Tags_with_query-string-style_parameters).
If you want the tree returned instead of printed, make sure you send in `'echo=0'`.

You can get default `$args`-arrays from these functions:

* wpdt_get_archives_defaults(); //returns associative arrays with all default settings
* wpdt_get_categories_defaults(); 
* wpdt_get_pages_defaults();
* wpdt_get_links_defaults();

Use them to find what arguments you have to play with. Or cheat and read [wpdt_get_defaults](http://pastebin.com/Szsyqtnu).

**Here's an example:**

`	<div class="dtree">			
		<?php 	
			if(function_exists('wpdt_list_archives')){								
		   	    wpdt_list_archives('type=yearly&useicons=1');
			}			
		?>					
	</div>`

== Upgrade Notice ==

= 4.0 =

Complete rewrite! Read the docs before upgrading! 

== Changelog == 

(Older entries moved here to clear up [the front page](http://wordpress.org/extend/plugins/wp-dtree-30/))

**Changes in v4.0** (2010-10-17)

* Completely recoded the plugin from the ground up for a much needed code overhaul.
* Added support for multiple tree instances
* Added support for per-tree settings (incl. caching)
* Added translation support
* Added (optional) noscript-fallback for JS-disabled visitors.
* Added setting for type of JS-hiding (XML, HTML or none) to ease page validation
* Added support for links title attribute and category descriptions
* Replaced Scriptacolous with jQuery
* Made animation JS optional (not even included on page if disabled)
* Minified JS and CSS (9KB vs. 16KB!)
* Made truncation optional (titles can be as long as you want!)
* Cache is created once when blog is visited, instead of everytime you add content.
* Removed support for WP <2.3	
* Removed all CSS-options from admin area (style your theme instead)
* Removed animation effects
* Removed all non-essential CSS-styling (inhering from your theme instead)
* **all previous settings will be lost!** Write them down before upgrading.

**Known issues:** 

* Only tested in Chrome with TwentyTen (default theme). 
* opentoselection doesn't handle paging

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

= WP-dTree looks horrible on my blog and I hate you for it! = 
WP-dTree 4.0 has almost no styles of its own. You should apply your own CSS-styling to it. Luckily it's quite simple: open `wp-dtree.css` and copy all the selectors into your theme's stylesheet. 
Now disable the plugins default CSS (from the Settings-panel) and hack away at your own file to make it pretty.

Remember - do not edit `wp-dtree.css`, as this will be replaced on every update of the plugin.

= Can I help you in any way? =
Uh... yes? Please. :) If you [sign up with DropBox](http://www.dropbox.com/referrals/NTIzMDI3MDk) on my refferal, I get 1GB (much needed!) extra space. DropBox a cross-plattform application to sync your files online and across computers, and a 2GB account is *free*. Also - my refferal earns you a 250MB bonus! 

If you've got more money than time then please [send me a book or two](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x). (used are fine!) 

= Can I change the images used by WP-dTree? =

The images are all stored in the 'wp-dtree/dtree-img/' directory. You can change them if you like. Just remember to keep the names the same or they won't appear.

== Screenshots ==

1. The archive tree in action.
2. The category tree in action. 
2. The archive widget configuration.

== Other Notes ==

The original WP-dTree was created by [Christopher Hwang](http://www.silpstream.com/blog/) in 2005. Since Mr. Hwang went MIA, [Ulf Benjaminsson](http://www.ulfben.com/) forked the plugin (as of version 3) in 2007, but kept the name. 

The fork was focused on performance improvements, but soon expanded to add a lot of new features and modernizations; WP 2.7, 2.8, 2.9 compability, widgets, out-of-the-box Scriptaculous support, link trees, feed icons and more. For version 4.0 the entire plugin has been rewritten from scratch by Ulf, bringing it in to line with the much matured WP 3.x API and generally being less of a hack. :P

>> [WP-dTree-plugin](http://wordpress.org/extend/plugins/wp-dtree-30/) (v. 3 and up) is Copyright (C) 2007-2010 Ulf Benjaminsson (email: ulf at ulfben dot com)

>> WP-dTree-plugin (v. 3 and lower) Copyright (C) 2006 Christopher Hwang (email: chris at silpstream dot com).

>> [dTree 2.05](www.destroydrop.com/javascript/tree/)-script is Copyright (c) 2002-2003 Geir Landrö

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