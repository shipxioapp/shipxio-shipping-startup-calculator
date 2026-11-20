# Shipxio Shipping Startup Calculator

A WordPress calculator for people planning to start a shipping company. Estimate startup and recurring costs from a list of startup items, with no account or external service required for calculations.

## Default items

| Item | Default price |
| --- | --- |
| Business Registration | J$6,000 |
| Logistics Provider / Warehouse | Free – Varies |
| Shipping Software | US$19.99/month |
| Logo Design | J$12,000 |
| Brand Identity | J$30,000 |
| Domain Name | US$20/year |
| Business Email | US$20/year |
| Web Hosting | US$143.88/year |
| Website | J$100,000 |

Items appear in this default order until an administrator saves a different one. Administrators can change these prices, add their own items, and reorder the list (see [Settings](#settings)).

Costs are shown separately by currency and billing period. There is no currency conversion, and recurring costs do not enter the startup estimate. Variable prices, such as "Free – Varies", are shown as text and never enter any total.

| Estimate | Total with all default items selected |
| --- | --- |
| Startup total | J$148,000 |
| Monthly expenses | US$19.99/month |
| Yearly expenses | US$183.88/year |

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

Open Settings > Shipxio Shipping Startup Calculator to manage the calculator items:

- **Edit** each item's name, description, priority (Required, Recommended, Optional, or None), and price. Priority controls the small badge beside the item name and does not change the order. A built-in item keeps its currency and billing period; for Logistics Provider / Warehouse, the price is the text shown in place of an amount.
- **Add** an item by filling in the blank "Add an item" fields and saving. Choose its pricing: JMD one-time, USD per month, USD per year, or varies (shown as text and not included in totals) and its priority. Added items can be edited or removed later.
- **Reorder** items with the Up and Down buttons, then save. The calculator shows items in the saved order.

JMD prices are whole numbers and USD prices allow up to two decimal places; both must be zero or more. An invalid price is rejected with an error message and the previous price is kept. Built-in items keep their default prices until you change them. The settings page also provides shortcode examples and copy buttons.

## Updates

Plugin updates are delivered through WordPress from this project's GitHub Releases.

## License

GPLv2 or later. See [LICENSE](LICENSE).
