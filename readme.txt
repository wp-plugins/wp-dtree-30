=== WP-dTree ===
Contributors: ulfben
Donate link: http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=21&y=17
Tags: archive, navigation, category, pages, links, bookmarks, dynamic, dtree, tree, sidebar, 
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 4.3.1

<a href="http://www.destroydrop.com/javascripts/tree/">Dynamic tree</a>-widgets to replace the standard archives, categories, pages and link lists.

== Description ==

This plugin provides [dynamic navigation trees](http://www.destroydrop.com/javascripts/tree/) to replace the standard archives, categories, pages and link lists. They're widgets so you can setup [the awesome tree navigation](http://game.hgo.se/cat/projects/3d-games/) with drag & drop ease, but it also exposes several [new template tags](http://wordpress.org/extend/plugins/wp-dtree-30/other_notes/) for developers.

WP-dTree 4.x is a complete re-write, bringing the plugin up to speed with the much matured WordPress 3 API. The overhaul has made WP-dTree significantly more sane and robust; it supports multiple widget instances, "foreign" characters, is more in tune with your themes, plays nice with translators and offers true fallbacks for those who surf without JavaScript.

If you value [my plugins](http://profiles.wordpress.org/users/ulfben/), please help me out by [Flattr-ing them](http://flattr.com/thing/367557/Support-my-WordPress-plugins)! Or perhaps [send me a book](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x=11&y=10)? Used ones are fine! :)

//*[Ulf Benjaminsson](http://profiles.wordpress.org/users/ulfben/)*

= Changes in v4.3.1 (2012-01-11) =
* Fixed: uninitialized settings will not cause [wierd output](http://wordpress.org/support/topic/plugin-wp-dtree-works-but-has-bugs?replies=3).

= Changes in v4.3 (2012-01-01) =
* Added: support and widget for custom taxonomies. (Thanks: Einat Dagan!)
* Added: configurable open/close links for each tree
* Added: [configurable open/close separator for each tree](http://wordpress.org/support/topic/plugin-wp-dtree-pipe-character-between-open-all-and-close-all?replies=2)
* Fixed: various PHP [errors](http://wordpress.org/support/topic/plugin-wp-dtree-stripos-function-error-followed-by-crash-of-site?replies=1#post-2393280) and warnings.
* [Fixed: feed links being wrong](http://wordpress.org/support/topic/rss-problem-36?replies=2#post-2221275)
* Fixed: initialization routines
* Fixed: excluding categories from the archive tree
* Fixed: excluding posts from category tree
* Fixed: [open/close link separator behavior](http://wordpress.org/support/topic/plugin-wp-dtree-pipe-character-between-open-all-and-close-all?replies=2)
* Fixed: [link children are sorted correctly](http://wordpress.org/support/topic/plugin-wp-dtree-sorting-links-by-id-not-working?replies=1)
* Fixed: Open to selection supports pagination
* Fixed: [Open to selection supports categories again](http://wordpress.org/support/topic/plugin-wp-dtree-how-to-force-open-wdtree?replies=3#post-2523443)

[Older changelogs moved here](http://wordpress.org/extend/plugins/wp-dtree-30/changelog/).

== Installation ==

1. If upgrading: *disable the old version first*!
1. Transfer the 'wp-dtree-30' folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the 'WP-dTree' under 'Settings' to adjust your preferences
1. Go to 'Presentation' -> 'Widgets' and drag-n-drop the widgets to the relevant section of your sidebar(s)
1. Configure the active Widget to your liking
1. [Styling is done through CSS](http://wordpress.org/extend/plugins/wp-dtree-30/faq/)

[Developers and widget resistance goes here](http://wordpress.org/extend/plugins/wp-dtree-30/other_notes/).

== For developers ==

WP-dTree exposes the following [template tag functions](http://codex.wordpress.org/Template_Tags):

* `wpdt_list_archives();`			
* `wpdt_list_categories();`
* `wpdt_list_taxonomies(); //needs testing.`
* `wpdt_list_pages();`
* `wpdt_list_links();`
* `wpdt_list_bookmarks(); //alias for wpdt_list_links`

They function a lot like WordPress own wp_list_* functions:

* They take an (optional) [query-string or associative array](http://codex.wordpress.org/How_to_Pass_Tag_Parameters#Tags_with_query-string-style_parameters) with arguments
* They print by default, but if passed 'echo=0' they return the string of markup
* Most of them use the WordPress namesake for generating noscript-content

**Here's an example:**

`	<div class="dtree">`
`		<?php`
`			if(function_exists('wpdt_list_archives')){`					
`		   	    wpdt_list_archives('type=yearly&useicons=1');`
`			}`
`		?>`					
`	</div>`

I've tried to keep the same argument-lists as the WordPress "equivalents" but there are some discrepancies: style- and markup related arguments are not applicable to WP-dTree (but gets passed through for the noscript content).
There is also some inconsistency within WordPress, like some methods takes `sortby` while others takes `sort_column`. I've tried for WP-dTree to accept both but I'm sure I've missed a bunch of these cases.

So, to find out what arguments are definetly available grab the default `$args`:

* `wpdt_get_archives_defaults();` 
* `wpdt_get_categories_defaults();` 
* `wpdt_get_taxonomies_defaults()`
* `wpdt_get_pages_defaults();`
* `wpdt_get_links_defaults();`

They all return associative arrays whith all arguments defaulted.

== Upgrade Notice ==

= Changes in v4.3.1 (2012-01-11) =
* Fixed fallbacks for uninitialized settings: never print [wierd output](http://wordpress.org/support/topic/plugin-wp-dtree-works-but-has-bugs?replies=3).

= 4.3 = 
Lots and lots of fixes. Widget for custom taxonomies.

= 4.2 =
Fixed the uninstallation procedure.

= 4.1 =
Safer installation, better performance. 

= 4.0 =
Complete rewrite! Read the docs before upgrading! 

== Changelog == 

(Older entries moved here to clear up [the front page](http://wordpress.org/extend/plugins/wp-dtree-30/))

= Changes in v4.3.1 (2012-01-11) =
* Fixed fallbacks for uninitialized settings: never print [wierd output](http://wordpress.org/support/topic/plugin-wp-dtree-works-but-has-bugs?replies=3).

= Changes in v4.3 (2012-01-01) =
* Added: support and widget for custom taxonomies. (needs testing, for sure...)
* Added: configurable open/close links for each tree
* Added: [configurable open/close separator for each tree](http://wordpress.org/support/topic/plugin-wp-dtree-pipe-character-between-open-all-and-close-all?replies=2)
* Fixed: various PHP [errors](http://wordpress.org/support/topic/plugin-wp-dtree-stripos-function-error-followed-by-crash-of-site?replies=1#post-2393280) and warnings.
* [Fixed: feed links being wrong](http://wordpress.org/support/topic/rss-problem-36?replies=2#post-2221275)
* Fixed: initialization routines
* Fixed: excluding categories from the archive tree
* Fixed: excluding posts from category tree
* Fixed: open/close link separator behavior (only show when both open/close is shown)
* Fixed: [link children are sorted correctly](http://wordpress.org/support/topic/plugin-wp-dtree-sorting-links-by-id-not-working?replies=1)
* Fixed: Open to selection supports pagination
* Fixed: [Open to selection supports categories again](http://wordpress.org/support/topic/plugin-wp-dtree-how-to-force-open-wdtree?replies=3#post-2523443)

= Changes in v4.2 (2010-10-29) =
* Fixed the uninstallation procedure.

= Changes in v4.1 (2010-10-23) =
* Improved installation to fix [database error for some users](http://tinyurl.com/37352en)
* Added: ["limit posts" for categories and archives](http://wordpress.org/support/topic/plugin-wp-dtree-limit-posts-under-categories?replies=2)
* Added: error catching - dtree can't blow up you other scripts
* Building archives is MUCH faster (2 queries total, from 1 per month)

= Changes in v4.0 (2010-10-17) =
* Completely recoded from the ground up for a much needed code overhaul.
*    **All previous settings will be lost!** Write them down before upgrading.
* Added: support for multiple tree instances
* Added: support for per-tree configurations 
* Added: [template tags](http://wordpress.org/extend/plugins/wp-dtree-30/other_notes/) for theme developers
* Added: (optional) noscript for JS-disabled visitors
* Added: uses category descriptions for link titles
* Added: translation support
* Added: optional JS-escape (XHTML, HTML or none) to ease validation
* Added: caching is optional (per instance, to boot)
* Fixed: should properly encode quotes and HTML-entities
* Replaced Scriptacolous with jQuery
* Made truncation optional
* Removed support for WP <2.3
* Removed all CSS-options from admin area
* [Removed all non-essential CSS-rules](http://wordpress.org/extend/plugins/wp-dtree-30/faq/)
* Only load jQuery if animation is on
* Minified JS and CSS (9KB vs. 16KB!)
* Cache is created on site visit (faster admin, less server load)

**Known issues in 4.0:** 

* This is a true .0 release - please explore and play with all the settings. [Let me know](http://wordpress.org/tags/wp-dtree-30) when something breaks (provide links!).
* Only tested in Chrome
* `opentoselection` doesn't handle paging

= Changes in v3.5 (2008-11-26) =

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

= Changes in v3.4.2 (2008-10-19) =
* Bug: incorrect WP version detection. ([thanks: StMD](http://wordpress.org/support/topic/211402))

= Changes in v3.4.1 (2008-07-20) =
* Validates: both CSS and XHTML 1.0 Transitional ([thanks: ar-jar](http://wordpress.org/support/topic/189643))

= Changes in v3.4 (2008-07-12) =
* Added support for link trees. (needs testing!)
* Fixed breakage in WP 2.5, 2.6
* Fixed invalid XHTML output. ([props: jberghem](http://wordpress.org/support/topic/150888))
* Fixed a CSS-issue. ([props: wenzlerm](http://wordpress.org/support/topic/186314))
* Renamed the dTree script to avoid collisions with plugins using an unmodified version.

= Changes in v3.3.2 (2007-11-26) =
* Fixed bug with excluding multiple categories.

= Changes in v3.3.1 (2007-11-02) =
* Removed redundant `li`-tags from widgets. (props: Alexey Zamulla) 
* Support for non-ascii characters. ([props: michuw](http://wordpress.org/support/topic/141554))
* Properly encoded ampersands (&) in javascript URLs.

= Changes in v3.3 (2007-10-26) =
* Optimized the dtree script, up to **40% less data** required to feed the script. Using dTree now generates less markup than normal HTML.
* New option: Show RSS icon for archives
* New option: Show post count for archives
* Fix: Open to requested node
* Fix: images URL not working on some servers ([props: Zarquod](http://wordpress.org/support/topic/136547))
* Fix: somewhat more IE compatible...

*Known issues:* RSS icons wont show **in IE** if `post count` is on.

= Changes in v3.2 (2007-10-15) =
* Support for WP's bundled scriptacolous library! (turn effects on in the WP-dTree options page)
* New cache structure reduces cache size with ~33% compared to previous implementations.	 
* New option: Show RSS icon for categories
* New option: Show post count for categories
* New option: Effect duration

*Regressions:* `open to selection` is broken again. It'll be back in the next version, but if it's vital for you, stay with 3.1

= Changes in v3.1: (2007-10-06) =
* Updated to comply with WordPress 2.3's new taxonomy tables for categories.
* Widgetized! You no longer need to edit your sidebar manually.
* Fixed "Open To Selection"-option.
		
= Changes in v3.0: (2007-08-17) =
* Forked from the dead [WP-dTree 2.0](http://www.silpstream.com/blog/wp-dtree/)
* Added caching. The plugin creates the trees (only) when blog content changes, instead of creating them on every visit. Displaying the front page on the dev site went from 411 queries to 18. :)
	
== Frequently Asked Questions ==

= WP-dTree looks horrible on my blog and I hate you for it! = 
WP-dTree 4.0 has almost no styles of its own - it inherits from your theme. To help you apply your own styling I've included a template CSS-file. Open `wp-dtree.css` and copy all the selectors into your theme's stylesheet. 
Now disable the plugins default CSS (from the Settings-panel) and hack away at your own file to make it pretty.

Remember - do not edit `wp-dtree.css`, as this will be replaced on every update of the plugin.

= Can I help you in any way? =
Absolutely! If you [sign up with DropBox](http://www.dropbox.com/referrals/NTIzMDI3MDk) on my refferal, I get 1GB (much needed!) extra space. DropBox is a cross-plattform application to sync your files online and across computers, and a 2GB account is *free*. Also - my refferal earns you a 250MB bonus! 

If you've had any commercial applications for my plugins, please consider [sending me a book or two](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x). (used are fine!) 

= Thanks! =
* Bruce Hampton, USA
* Shu Mei Chen, Taiwan
* Kai Kniepkamp, Germany

...[for the books](http://www.amazon.com/gp/registry/wishlist/2QB6SQ5XX2U0N/105-3209188-5640446?reveal=unpurchased&filter=all&sort=priority&layout=standard&x)

= Why is there no "Show more"-link for Archives? =
First: a simple workaround is to use 'Folders are links' and 'Show post count'. This way a visitor can easily see that a folder has more content than is on display, and clicking the folder name will bring her to it.

The reason there's no "Show more"-link for Archives is this: [limit posts in categories](http://wordpress.org/support/topic/plugin-wp-dtree-limit-posts-under-categories?replies=2) was a *paid* request and I added that feature to archives too because it was reasonably simple and actually helped me optimize the code slightly. The "show more"-link was a simple addon for the Category generation code, but not so for Archives - it simply requires a lot of data to be available where it currently is **not**. So until someone feels strongly enough to pay me for the inconvenience, I won't bother bloating the code for this rather narrow feature.

= Can I change the images used by WP-dTree? =
The images are all stored in the 'wp-dtree/dtree-img/' directory. You can change them if you like. Just remember to keep the names the same or they won't appear.

== Screenshots ==

1. The category tree over at game.hgo.se
2. The archive widget configuration screen
3. Archives, pages and categories (with post count and RSS-icons enabled).

== Other Notes ==

The original 'WP-dTree' was created by [Christopher Hwang](http://www.silpstream.com/blog/) in ~2005. By 2007 Mr. Hwang seemed to have dropped of the internet for good so [Ulf Benjaminsson](http://www.ulfben.com/) forked the plugin in and named it 'WP-dTree 3.0' (note to self: having a release number in the title is *stupid*).

Ulf's fork was focused on performance improvements - mainly caching - but soon expanded to add a lot of new features and modernizations; compatibility with WP 2.7 through WP 3.3, widgets, out-of-the-box Scriptaculous support, link trees, feed icons and more. 

For version 4.0 the entire plugin has been rewritten from scratch by Ulf, bringing it in line with the much matured WP 3.x API and generally being less of a hack. :P

*WP-dTree (3.0 and up) is Copyright (C) 2007-2012 Ulf Benjaminsson (email: first name at ulfben dot com)

*WP-dTree (3.x and lower) Copyright (C) 2006 Christopher Hwang (email: chris at silpstream dot com).

*[dTree](www.destroydrop.com/javascript/tree/)-JavaScript is Copyright (c) 2002-2003 Geir Landr�

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