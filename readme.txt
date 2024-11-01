=== SpinChimp WP Spinner ===
Contributors: akturatech
Donate link: 
Tags: spinner, spinchimp, chimprewriter
Requires at least: 3.3
Tested up to: 3.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An automatic content spinner using the SpinChimp API

== Description ==

Use the SpinChimp API to spin content to your blog! Choose to automatically spin each post as it is submitted or manually spin before you post.

Optionally catches posts made by other plugins and rewrites them as well.

Protect your categories and tags from being spun in one click.

SpinChimp API key required.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open the "Credentials" page under SpinChimp in the Wordpress menu and enter your API details

== Frequently asked questions ==

**What is spinning?**

The process of replacing words and phrases within your content to generate a unique version of the article.

**Will be keywords be spun?**

The SpinChimp plugin includes the ability to import your tags and/or categories as protected terms that will not be spun.

== Screenshots ==



== Changelog ==

**1.1**

- Rewriting title and content only uses a single query.
- Added spinchimp_spinned meta to posts/pages spinned by the plugin to track which articles have already been rewritten. This prevents duplicate rewriting.
- Changed maxspindepth parameter to integer with values from 0 to 5 as defined
in the API documentation.
- NEW OPTION - Use SpinChimp when publishing content using other 
plugins. This allows posts from other plugins may be rewritten 
using Spinchimp, given they use the correct WP functions.
- NEW FEATURE - for plugins that write posts directly to the database, the plugin can automatically fetch previous posts to rewrite in the database. The number of posts to fetch is configurable, as is the check frequency. This feature uses the Wordpress psuedo-cron or an actual cron script
- Movable sidebar.

**1.0**

Initial release

== Upgrade notice ==

