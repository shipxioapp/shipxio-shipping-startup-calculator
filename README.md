# Shipxio Shipping Startup Calculator

A WordPress calculator for people planning to start a shipping company. Estimate startup and recurring costs using eight predefined categories, with no account or external service required for calculations.

## Calculator categories

- Business Registration
- Logo Design
- Brand Identity
- Domain Name
- Website
- Web Hosting
- Business Email
- Shipping Software

Costs are shown separately by currency and billing period. There is no currency conversion, and recurring costs do not enter the startup estimate.

| Estimate | Total with all items selected |
| --- | --- |
| One-time startup cost | J$148,000 |
| Monthly cost | US$60/month |
| Yearly cost | US$183.88/year |

## Requirements

- WordPress 6.3 or later
- PHP 8.1 or later

Elementor is optional.

## Installation

1. Download `shipxio-shipping-startup-calculator.zip` from [GitHub Releases](https://github.com/shipxioapp/shipxio-shipping-startup-calculator/releases).
2. In WordPress, open Plugins > Add New > Upload Plugin and upload the ZIP.
3. Activate Shipxio Shipping Startup Calculator.

## Shortcodes

| Shortcode | Displays |
| --- | --- |
| `[shipxio_shipping_startup_calculator]` | Calculator with its title and description |
| `[shipxio_shipping_startup_calculator show_intro="false"]` | The same calculator without its title and description |

Add either shortcode to WordPress content, a Gutenberg Shortcode block, or Elementor's standard Shortcode widget. Your theme or Elementor container controls the calculator's outer width and spacing.

## Settings

Open Settings > Shipxio Shipping Startup Calculator to edit the name and description of each item. Prices and other item properties remain fixed. The settings page also provides shortcode examples and copy buttons.

## Updates

Plugin updates are delivered through WordPress from this project's GitHub Releases.

## License

GPLv2 or later. See [LICENSE](LICENSE).
