<?php
/** Frontend assets and shortcode. */

if (! defined('ABSPATH')) {
    exit;
}

function shipxio_shipping_startup_calculator_register_assets()
{
    $url = plugin_dir_url(SHIPXIO_SHIPPING_STARTUP_CALCULATOR_FILE);
    wp_register_style('shipxio-shipping-startup-calculator', $url . 'assets/css/app.css', array(), SHIPXIO_SHIPPING_STARTUP_CALCULATOR_VERSION);
    wp_register_script('shipxio-shipping-startup-calculator', $url . 'assets/js/app.js', array(), SHIPXIO_SHIPPING_STARTUP_CALCULATOR_VERSION, array('in_footer' => true));
}

/** Accept the same boolean spellings as the Shipxio Connect shortcodes. */
function shipxio_shipping_startup_calculator_show_intro($atts, $tag)
{
    $parsed = shortcode_atts(array('show_intro' => 'true'), $atts, $tag);
    $value = $parsed['show_intro'];

    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, array('false', 'no', 'off', '0', ''), true)) {
            return false;
        }
    }

    return wp_validate_boolean($value);
}

function shipxio_shipping_startup_calculator_render_shortcode($atts = array(), $content = null, $tag = 'shipxio_shipping_startup_calculator')
{
    wp_enqueue_style('shipxio-shipping-startup-calculator');
    wp_enqueue_script('shipxio-shipping-startup-calculator');

    // Unique IDs also cover independently rendered Elementor shortcode requests.
    $id = 'shipxio-shipping-startup-calculator-' . wp_generate_uuid4();
    $show_intro = shipxio_shipping_startup_calculator_show_intro($atts, $tag);
    $items = shipxio_shipping_startup_calculator_items();
    $priority_labels = array(
        'required' => __('Required', 'shipxio-shipping-startup-calculator'),
        'recommended' => __('Recommended', 'shipxio-shipping-startup-calculator'),
        'optional' => __('Optional', 'shipxio-shipping-startup-calculator'),
    );

    ob_start();
    include __DIR__ . '/../templates/calculator.php';
    return (string) ob_get_clean();
}

/** Preserve the prototype currency and billing-period formatting. */
function shipxio_shipping_startup_calculator_price($item)
{
    $cost = (float) $item['cost'];
    if ('JMD' === $item['currency']) {
        return 'J$' . number_format($cost, 0);
    }

    $price = 'US$' . number_format($cost, $cost === floor($cost) ? 0 : 2);
    if ('month' === $item['billing_period']) {
        return $price . '/month';
    }
    if ('year' === $item['billing_period']) {
        return $price . '/year';
    }
    return $price;
}
