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
            'priority' => 'required',
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
            'cost' => 60,
            'currency' => 'USD',
            'billing_period' => 'month',
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

/** Only names and descriptions may override the proven definitions. */
function shipxio_shipping_startup_calculator_items()
{
    $items = shipxio_shipping_startup_calculator_default_items();
    $overrides = get_option('shipxio_shipping_startup_calculator_items', array());
    if (! is_array($overrides)) {
        return $items;
    }

    foreach ($items as &$item) {
        $override = $overrides[$item['key']] ?? array();
        foreach (array('name', 'description') as $field) {
            if (is_array($override) && isset($override[$field]) && is_string($override[$field])) {
                $item[$field] = $override[$field];
            }
        }
    }
    unset($item);
    return $items;
}
