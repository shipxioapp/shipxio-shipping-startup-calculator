<?php
/** Proven calculator definitions, including the Go Ship educational content. */

if (! defined('ABSPATH')) {
    exit;
}

function shipxio_shipping_startup_calculator_default_items()
{
    $items = [
        [
            'key' => 'business-registration',
            'name' => 'Business Registration',
            'description' => 'Makes your shipping company a legal business you can trade under.',
            'cost' => 6000,
            'currency' => 'JMD',
            'billing_period' => 'one_time',
            'priority' => 'recommended',
        ],
        [
            'key' => 'logo-design',
            'name' => 'Logo Design',
            'description' => 'The mark customers recognise your company by.',
            'cost' => 12000,
            'currency' => 'JMD',
            'billing_period' => 'one_time',
            'priority' => 'recommended',
        ],
        [
            'key' => 'brand-identity',
            'name' => 'Brand Identity',
            'description' => 'The colours, fonts and look that keep your brand consistent everywhere.',
            'cost' => 30000,
            'currency' => 'JMD',
            'billing_period' => 'one_time',
            'priority' => 'optional',
        ],
        [
            'key' => 'domain',
            'name' => 'Domain Name',
            'description' => 'Your website address, the name customers type to find you online.',
            'aside' => 'Example: goship.com',
            'cost' => 20,
            'currency' => 'USD',
            'billing_period' => 'year',
            'priority' => 'recommended',
        ],
        [
            'key' => 'landing-page',
            'name' => 'Website',
            'description' => 'Your online home where customers can learn about your shipping company, contact you, and become familiar with your brand.',
            'cost' => 100000,
            'currency' => 'JMD',
            'billing_period' => 'one_time',
            'priority' => 'recommended',
        ],
        [
            'key' => 'web-hosting',
            'name' => 'Web Hosting',
            'description' => 'The service that keeps your website online so customers can visit it at any time.',
            'aside' => 'Think of the domain as your address and hosting as the place where your website lives.',
            'cost' => 143.88,
            'currency' => 'USD',
            'billing_period' => 'year',
            'priority' => 'recommended',
        ],
        [
            'key' => 'business-email',
            'name' => 'Business Email',
            'description' => 'A professional email address that uses your own domain instead of a free email service.',
            'aside' => 'Example: hello@goship.com',
            'cost' => 20,
            'currency' => 'USD',
            'billing_period' => 'year',
            'priority' => 'recommended',
        ],
        [
            'key' => 'shipping-software',
            'name' => 'Shipping Software',
            'description' => 'One place to manage customers, packages, payments and daily deliveries.',
            'cost' => 19.99,
            'currency' => 'USD',
            'billing_period' => 'month',
            'priority' => 'required',
        ],
        [
            // Variable pricing: shown as text, never counted in any total.
            'key' => 'logistics-provider',
            'name' => 'Logistics Provider / Warehouse',
            'description' => 'Some logistics providers offer free setup, while others charge a fee. Package handling, freight, and other service charges are billed separately.',
            'cost' => null,
            'price_label' => 'Free – Varies',
            'currency' => '',
            'billing_period' => '',
            'priority' => 'required',
        ],
    ];

    /**
     * Optional educational content shown behind a disclosure on some rows.
     *
     * Go Ship LLC is used as the example shipping company. Its logo files are
     * demo assets only.
     */
    $itemDetails = [
        'logo-design' => [
            'intro' => 'A professional logo package gives you the versions you need to use your logo across your website, social media, documents, signs, uniforms and other business materials.',
            'list' => [
                'Primary horizontal logo',
                'Logo icon / emblem',
                'Full-colour version',
                'White version for dark backgrounds',
                'Dark version for light backgrounds',
                'Files suitable for digital and print use',
            ],
            'image' => [
                'src' => 'assets/images/logo-design.webp',
                'width' => 1400,
                'height' => 800,
                'alt' => 'Example logo package for Go Ship LLC, showing the horizontal logo and the icon in full colour, black and white, on light and dark backgrounds.',
            ],
        ],
        'brand-identity' => [
            'intro' => 'Brand identity takes your logo and builds a complete visual system around it so your shipping company looks consistent wherever customers see it.',
            'list' => [
                'Primary logo',
                'Horizontal logo',
                'Icon / emblem',
                'Light and dark logo versions',
                'Full colour palette',
                'Typography / font guidance',
                'Brand style guide',
                'Supporting graphics and visual elements',
                'Example brand applications',
                'Business and marketing mockups',
            ],
            'image' => [
                'src' => 'assets/images/brand-identity.webp',
                'width' => 1400,
                'height' => 800,
                'alt' => 'Example brand board for Go Ship LLC, showing logo versions, the colour palette, typography, brand graphics and example applications.',
            ],
        ],
    ];


    foreach ($items as &$item) {
        if (isset($itemDetails[$item['key']])) {
            $item['details'] = $itemDetails[$item['key']];
        }
    }
    unset($item);
    return $items;
}

/**
 * Validate an administrator price for an item's currency.
 *
 * JMD amounts are whole numbers, matching the calculator's J$ formatting; USD
 * amounts allow up to two decimal places. Returns the amount, or null when the
 * value is not a non-negative number in that form.
 */
function shipxio_shipping_startup_calculator_parse_cost($value, $currency)
{
    if (is_int($value) || is_float($value)) {
        $value = shipxio_shipping_startup_calculator_cost_input_value($value);
    }
    if (! is_string($value)) {
        return null;
    }

    $pattern = 'JMD' === $currency ? '/^\d{1,9}$/' : '/^\d{1,9}(\.\d{1,2})?$/';
    $value = trim($value);
    if (1 !== preg_match($pattern, $value)) {
        return null;
    }

    return (float) $value;
}

/** A cost as an unformatted field value: 6000, 20, 143.88. */
function shipxio_shipping_startup_calculator_cost_input_value($cost)
{
    $cost = (float) $cost;
    return $cost === floor($cost) ? (string) (int) $cost : number_format($cost, 2, '.', '');
}

/** Priority classifications shown as badges. Priority never affects display order. */
function shipxio_shipping_startup_calculator_priority_labels()
{
    return array(
        'required' => __('Required', 'shipxio-shipping-startup-calculator'),
        'recommended' => __('Recommended', 'shipxio-shipping-startup-calculator'),
        'optional' => __('Optional', 'shipxio-shipping-startup-calculator'),
    );
}

/**
 * Pricing choices for administrator-added items: [currency, billing period].
 * "variable" items show a text label and never contribute to a total.
 */
function shipxio_shipping_startup_calculator_pricing_types()
{
    return array(
        'jmd_one_time' => array('JMD', 'one_time'),
        'usd_month'    => array('USD', 'month'),
        'usd_year'     => array('USD', 'year'),
        'variable'     => array('', ''),
    );
}

/** The pricing type of an item, derived from its currency and billing period. */
function shipxio_shipping_startup_calculator_pricing_type($item)
{
    if (null === $item['cost']) {
        return 'variable';
    }
    foreach (shipxio_shipping_startup_calculator_pricing_types() as $type => $pair) {
        if (array($item['currency'], $item['billing_period']) === $pair) {
            return $type;
        }
    }
    return '';
}

/**
 * Build a calculator item from a stored administrator-added item.
 *
 * Returns null when the stored data is incomplete or invalid, so a damaged
 * entry is skipped rather than shown with a wrong price.
 */
function shipxio_shipping_startup_calculator_custom_item($stored)
{
    if (! is_array($stored)) {
        return null;
    }
    $key = $stored['key'] ?? '';
    $name = $stored['name'] ?? '';
    $pricing = $stored['pricing'] ?? '';
    $types = shipxio_shipping_startup_calculator_pricing_types();
    if (! is_string($key) || 1 !== preg_match('/^custom-[a-z0-9]{10}$/', $key)
        || ! is_string($name) || '' === trim($name)
        || ! is_string($pricing) || ! isset($types[$pricing])) {
        return null;
    }

    $item = array(
        'key' => $key,
        'name' => $name,
        'description' => is_string($stored['description'] ?? null) ? $stored['description'] : '',
        'cost' => null,
        'currency' => $types[$pricing][0],
        'billing_period' => $types[$pricing][1],
        'custom' => true,
    );

    // Optional; items saved before priorities existed simply have none.
    $priority = $stored['priority'] ?? '';
    if (is_string($priority) && isset(shipxio_shipping_startup_calculator_priority_labels()[$priority])) {
        $item['priority'] = $priority;
    }

    if ('variable' === $pricing) {
        $label = $stored['price_label'] ?? '';
        if (! is_string($label) || '' === trim($label)) {
            return null;
        }
        $item['price_label'] = $label;
        return $item;
    }

    $item['cost'] = shipxio_shipping_startup_calculator_parse_cost($stored['cost'] ?? null, $item['currency']);
    return null === $item['cost'] ? null : $item;
}

/** Arrange items by a saved key order; unlisted items keep their natural order at the end. */
function shipxio_shipping_startup_calculator_apply_order($items, $order)
{
    if (! is_array($order)) {
        return $items;
    }
    $byKey = array_column($items, null, 'key');
    $ordered = array();
    foreach ($order as $key) {
        if (is_string($key) && isset($byKey[$key])) {
            $ordered[] = $byKey[$key];
            unset($byKey[$key]);
        }
    }
    return array_merge($ordered, array_values($byKey));
}

/**
 * The effective calculator items.
 *
 * The saved option holds deviations from the defaults keyed by item key, plus
 * two reserved entries: "_custom" (administrator-added items) and "_order"
 * (display order). Item keys never start with an underscore.
 */
function shipxio_shipping_startup_calculator_items()
{
    $items = shipxio_shipping_startup_calculator_default_items();
    $saved = get_option('shipxio_shipping_startup_calculator_items', array());
    if (! is_array($saved)) {
        return $items;
    }

    foreach ($items as &$item) {
        $override = $saved[$item['key']] ?? array();
        if (! is_array($override)) {
            continue;
        }
        foreach (array('name', 'description') as $field) {
            if (isset($override[$field]) && is_string($override[$field])) {
                $item[$field] = $override[$field];
            }
        }
        // Priority override; an empty string means "None" (no badge).
        if (isset($override['priority']) && is_string($override['priority'])) {
            if ('' === $override['priority']) {
                unset($item['priority']);
            } elseif (isset(shipxio_shipping_startup_calculator_priority_labels()[$override['priority']])) {
                $item['priority'] = $override['priority'];
            }
        }
        // Currency and billing period stay fixed; an invalid stored price is ignored.
        if (null === $item['cost']) {
            if (isset($override['price_label']) && is_string($override['price_label']) && '' !== trim($override['price_label'])) {
                $item['price_label'] = $override['price_label'];
            }
        } elseif (isset($override['cost'])) {
            $cost = shipxio_shipping_startup_calculator_parse_cost($override['cost'], $item['currency']);
            if (null !== $cost) {
                $item['cost'] = $cost;
            }
        }
    }
    unset($item);

    $keys = array_column($items, 'key');
    foreach ((array) ($saved['_custom'] ?? array()) as $stored) {
        $custom = shipxio_shipping_startup_calculator_custom_item($stored);
        if (null !== $custom && ! in_array($custom['key'], $keys, true)) {
            $items[] = $custom;
            $keys[] = $custom['key'];
        }
    }

    return shipxio_shipping_startup_calculator_apply_order($items, $saved['_order'] ?? null);
}
