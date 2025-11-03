=== Featured Archive Scroller ===
Contributors: donncha
Tags: gallery, archive, images, featured-images, horizontal-scroll
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a /gallery endpoint to archive URLs, displaying featured images in a beautiful horizontal scrolling gallery.

== Description ==

Featured Archive Scroller transforms your WordPress archives into stunning horizontal image galleries. Simply append `/gallery` to any archive URL (category, tag, author, date, or custom taxonomy) to view a smooth-scrolling gallery of featured images.

= Key Features =

* **Universal Archive Support**: Works with categories, tags, authors, date archives, custom post types, and taxonomies
* **Smooth Horizontal Scrolling**: CSS scroll-snap with JavaScript enhancements
* **Keyboard Navigation**: Navigate with arrow keys for better accessibility
* **Touch Support**: Swipe left and right on mobile devices
* **Lazy Loading**: Images load as you scroll for optimal performance
* **SEO Friendly**: Options to noindex gallery pages and set canonical URLs
* **Customizable**: Admin settings to control behavior and appearance
* **Accessible**: ARIA labels, focus states, and keyboard navigation
* **Pagination**: Handle large archives with built-in pagination support

= How It Works =

1. Install and activate the plugin
2. Visit any archive page (e.g., `/category/news/`)
3. Append `/gallery` to the URL (e.g., `/category/news/gallery`)
4. Enjoy your horizontal scrolling gallery!

= Perfect For =

* Photography websites
* Portfolio sites
* News and magazine sites
* Any site with image-rich content

= Usage Examples =

* Category: `/category/photography/gallery`
* Tag: `/tag/featured/gallery`
* Author: `/author/john/gallery`
* Date: `/2024/01/gallery`
* Custom Post Type: `/products/gallery`

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/featured-archive-scroller/`, or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings → Archive Scroller to configure options
4. Visit any archive and append `/gallery` to see your horizontal gallery

== Frequently Asked Questions ==

= Does this work with all archive types? =

Yes! The plugin supports categories, tags, authors, date archives, custom post types, and custom taxonomies.

= What happens to posts without featured images? =

By default, posts without featured images are excluded. You can enable placeholder images in the settings if you want to show all posts.

= Can I customize the gallery appearance? =

Yes! The plugin includes a template file that can be overridden in your theme. Copy `templates/archive-gallery.php` to your theme root to customize.

= Is it mobile-friendly? =

Absolutely! The gallery supports touch swipe navigation and is fully responsive.

= Will this affect SEO? =

The plugin includes SEO options. You can choose to noindex gallery pages and set canonical URLs to avoid duplicate content issues.

= Can I change the `/gallery` slug? =

Yes! Go to Settings → Archive Scroller and change the endpoint slug to anything you prefer.

== Screenshots ==

1. Horizontal scrolling gallery view
2. Title and excerpt overlay on hover
3. Admin settings page
4. Mobile view with touch support

== Changelog ==

= 1.0.0 =
* Initial release
* Gallery endpoint for all archive types
* Horizontal scroll with CSS scroll-snap
* Keyboard and touch navigation
* Lazy loading and image prefetching
* Admin settings page
* SEO options
* Accessibility features
* Pagination support

== Upgrade Notice ==

= 1.0.0 =
Initial release of Featured Archive Scroller.

== Development ==

The plugin is actively developed on GitHub. Bug reports and pull requests are welcome!

== Credits ==

Developed with love for the WordPress community.
