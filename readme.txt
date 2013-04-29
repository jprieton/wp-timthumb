=== TimThumb Helper ===
Contributors: jprieton
Donate link:
Tags: timthumb, helper, thumbnail, images, attachments, gallery
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Helper for retrieve thumbnails, galleries and attachments apply TimThumb script if is set

== Description ==

Helper for retrieve thumbnails, galleries and attachments apply TimThumb script if is set

All getters return an WP_Post object unless the `object` parameter set to false

* [Project Page](http://code.google.com/p/wp-timthumb/)
* [Getting Started](http://code.google.com/p/wp-timthumb/wiki/GettingStarted)

== Installation ==

1. Upload an unzip `timthumb-helper.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it. You're ready to go!

== Changelog ==

= 1.1.2 =
* Fix search by slug

= 1.1.1 =
* Fix issue with `default` parameter in `the_first_image()` and `the_featured_image()`
* Fix error in `the_first_image()`
* Added `slug` parameter to search by slug in `get_post_galleries()`

= 1.1.0 =
* Documentation
* New function to get galleries in the post

= 1.0.1 =
* Minor Fixes

= 1.0.0 =
* First release
