<?php
/*
Plugin Name: Shipxio Shipping Startup Calculator
Plugin URI: https://github.com/shipxioapp/shipxio-shipping-startup-calculator
Update URI: https://github.com/shipxioapp/shipxio-shipping-startup-calculator
Description: Helps people planning to start a shipping company estimate startup and recurring costs.
Version: 1.0.2
Requires at least: 6.3
Requires PHP: 8.1
Author: Shipxio
Author URI: https://shipxio.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: shipxio-shipping-startup-calculator
*/

if (! defined('ABSPATH')) {
    exit;
}

define('SHIPXIO_SHIPPING_STARTUP_CALCULATOR_VERSION', '1.0.2');
define('SHIPXIO_SHIPPING_STARTUP_CALCULATOR_FILE', __FILE__);

require_once __DIR__ . '/includes/items.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/frontend.php';
require_once __DIR__ . '/includes/updater.php';

add_action('admin_init', 'shipxio_shipping_startup_calculator_register_settings');
add_action('admin_menu', 'shipxio_shipping_startup_calculator_register_settings_page');
add_action('admin_enqueue_scripts', 'shipxio_shipping_startup_calculator_enqueue_settings_assets');
add_action('init', 'shipxio_shipping_startup_calculator_register_assets');
add_shortcode('shipxio_shipping_startup_calculator', 'shipxio_shipping_startup_calculator_render_shortcode');

// WordPress selects this filter from the Update URI hostname.
add_filter('update_plugins_github.com', 'shipxio_shipping_startup_calculator_check_for_update', 10, 3);
add_filter('plugins_api', 'shipxio_shipping_startup_calculator_plugin_information', 10, 3);
