=== Redirect 404 Mapper ===
Contributors: mgn
Tags: 404, redirect, url-mapping, javascript
Requires at least: 5.0
Requires PHP: 7.2
Tested up to: 6.6
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0-or-later.html

Map 404 accessed URLs and redirect to specified destinations using JavaScript.

== Description ==

Redirect 404 Mapper automatically detects access to non-existent pages (404 errors) and redirects users to alternative URLs based on rules configured in the WordPress admin panel.

All redirect processing is executed via JavaScript, providing faster redirection compared to server-side redirects.

= Features =

* **Rule Management UI**: Easily add and edit redirect rules from the WordPress admin panel
* **Dynamic Redirects**: Automatically detects accessed URLs and redirects via JavaScript
* **Flexible URL Specification**: Full path and query string matching
* **Secure Implementation**: Proper input sanitization and escaping

= Requirements =

* WordPress 5.0 or higher
* PHP 7.2 or higher

== Installation ==

1. Download and extract the plugin
2. Upload the `redirect-404-mapper` folder to `/wp-content/plugins/`
3. Activate the plugin from the WordPress Plugins page

== Usage ==

= Setting Up Rules =

1. Go to **Settings > 404 Redirect Mapper** in the admin menu
2. Enter the following information:
   * **404 URL (from)**: The path to redirect from (e.g., `/old-page/`)
   * **Redirect URL (to)**: The complete URL to redirect to (e.g., `https://example.com/new-page/`)
3. Click **Add Rule** to add more rows
4. Click **Save Changes** to save your rules

= Including Query Strings =

You can include query strings in your redirect mappings:

```
404 URL (from): /old-page/?param=value
Redirect URL (to): https://example.com/new-page/
```

= How It Works =

When a user visits a 404 page, the plugin checks the requested URL against your configured rules. If a match is found, the user is automatically redirected to the specified destination URL via JavaScript.

The redirect happens on the client side, making it extremely fast and reducing server load.

== Frequently Asked Questions ==

= Why use JavaScript redirects? =

JavaScript-based redirects are executed on the client side, making them faster and reducing server processing. This is ideal for handling legacy URL migrations without site performance impact.

= Can I redirect with query parameters? =

Yes. When mapping a 404 URL, you can include query parameters (e.g., `/old-page/?id=123`). The plugin performs exact matching on the full path and query string.

= Is it secure? =

Yes. All user inputs are properly sanitized and escaped. The plugin uses WordPress nonces for form submissions and respects user capabilities.

== Screenshots ==

1. Rule management interface in WordPress admin panel

== Changelog ==

= 1.0.0 =
* Initial release

== Support ==

For issues or feature requests, please contact the plugin author.
