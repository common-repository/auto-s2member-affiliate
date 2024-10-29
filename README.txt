=== Auto s2Member Affiliate ===

Contributors: avantman42
Tags: s2 member, membership, affiliate, affiliates, users, affiliates manager, integration
Requires at least: 4.6
Tested up to: 5.5
Requires PHP: 5.6
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

Automatically creates a WP Affiliate Manager affiliate account for users when they register using the s2Member framework

== Description ==

**This plugin is no longer maintained**

This plugin was originally written for a client, but may be useful for others. The client was using the s2Member framework to manage paid users, and WP Affiliate Manager to allow members to make affiliate income. They wanted members to get an affiliate account automatically when they registered.

When a user registers on a site using the s2Member framework, this plugin automatically creates an affiliate account for the user.

This addon requires [WP Affiliate Manager](https://wordpress.org/plugins/affiliates-manager/) and the [s2Member framework](https://wordpress.org/plugins/s2member/).

Errors and successes are logged in the WP Affiliate Manager log files if debugging is enabled in WP Affiliate Manager.

== Installation ==

1. Upload the plugin zip file using WordPress' plugin installer, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Go to the Settings->Auto s2Member Affiliate screen. Copy the URL given there and add it to s2Member settings, API/Notifications -> Registration Notifications or API/Notifications -> Signup Notifications.

== Screenshots ==

1. The settings page

== Changelog ==

= v1.3 =

Added notice that plugin is no longer maintained

= v1.2 =

Bug fix: Overuse of esc_url()

= v1.1 =

Set user's PayPal email to their login email

= v1.0 =

Bug fix: affiliate had two records created

= v0.1 =

* Initial version
