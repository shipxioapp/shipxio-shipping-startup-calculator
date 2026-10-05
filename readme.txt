=== Shipxio Shipping Startup Calculator ===
Contributors: shipxio
Tags: startup costs, shipping company, calculator, logistics
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Estimate the startup and recurring costs of starting a shipping company.

== Description ==

Shipxio Shipping Startup Calculator helps people planning to start a shipping company estimate costs using eight fixed categories:

* Business Registration
* Logo Design
* Brand Identity
* Domain Name
* Website
* Web Hosting
* Business Email
* Shipping Software

JMD one-time amounts contribute only to Estimated Startup Cost. USD monthly amounts contribute only to Monthly Cost, and USD yearly amounts contribute only to Yearly Cost. There is no currency conversion or exchange rate. Recurring rows disappear when their totals are zero.

With every item selected, the estimates are J$148,000 startup, US$60/month, and US$183.88/year.

The calculator includes Go Ship example boards for Logo Design and Brand Identity, with accessible native disclosures. Administrators can edit item names and descriptions in Settings > Shipxio Shipping Startup Calculator. Prices, currencies, billing periods, priorities, order, and images are fixed.

Calculations run locally in the browser and have no external service or API dependency. The updater checks a public manifest on GitHub and downloads this plugin's own GitHub Release ZIPs. Calculator selections and settings are not sent to GitHub.

== Installation ==

1. Upload the plugin ZIP in Plugins > Add New > Upload Plugin, then activate it.
2. Open Settings > Shipxio Shipping Startup Calculator to edit names and descriptions if needed.
3. Add [shipxio_shipping_startup_calculator] to WordPress content, a Gutenberg Shortcode block, or Elementor's standard Shortcode widget.

Requirements: WordPress 6.3 or later and PHP 8.1 or later. Elementor is optional.

== Shortcodes ==

[shipxio_shipping_startup_calculator]

Calculator with its title and description. Intro is shown by default.

[shipxio_shipping_startup_calculator show_intro="false"]

The same calculator without its title and description, for pages with their own heading.

== Appearance ==

The calculator fills its host container without imposing outer width limits, margins, or padding. Your theme or Elementor container controls that layout.

Frontend styling uses the website's existing --mpx-* design tokens with matching built-in fallbacks. The plugin does not load or duplicate the site's token stylesheet, and remains usable when those variables are absent.

== Data and Updates ==

Settings persist when the plugin is deactivated. Deleting the plugin removes only its item text overrides and updater failure flag, including per-site data on multisite.

Updates use the public repository at https://github.com/shipxioapp/shipxio-shipping-startup-calculator. No GitHub authentication is needed by WordPress. The updater relies on WordPress update timing and briefly throttles failed manifest requests; manual checks bypass that throttle.

== Verification ==

Compatibility metadata follows the approved Shipxio Connect baseline. Source and isolated fixtures have been checked; these checks do not establish live WordPress activation, authenticated settings saves, Gutenberg, or Elementor acceptance.

== Changelog ==

= 1.0.3 =
* Maintenance and improvements.

= 1.0.2 =
* Set WordPress compatibility metadata to the approved Shipxio Connect baseline.
* Validate compatibility metadata locally before release preparation and tag creation.

= 1.0.1 =
* Maintenance and improvements.

= 1.0.0 =
* Initial WordPress conversion of the proven eight-item startup calculator.
* Added both shortcode intro modes, editable item names and descriptions, responsive layout, and accessible animated example disclosures.
* Added calculator-owned GitHub release/update infrastructure and deletion cleanup.
