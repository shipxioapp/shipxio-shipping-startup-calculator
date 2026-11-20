<?php
/** WordPress-owned item overrides, administrator-added items and display order. */

if (! defined('ABSPATH')) {
    exit;
}

function shipxio_shipping_startup_calculator_register_settings()
{
    register_setting('shipxio_shipping_startup_calculator', 'shipxio_shipping_startup_calculator_items', array(
        'type'              => 'array',
        'sanitize_callback' => 'shipxio_shipping_startup_calculator_sanitize_items',
        'default'           => array(),
        'show_in_rest'      => false,
    ));

    // As in Connect, the Settings API owns fields and the page draws the card.
    // Fields follow the saved display order so the admin list matches the calculator.
    add_settings_section('shipxio_shipping_startup_calculator_items', '', '__return_false', 'shipxio-shipping-startup-calculator');
    foreach (shipxio_shipping_startup_calculator_items() as $item) {
        add_settings_field(
            'shipxio_shipping_startup_calculator_' . $item['key'],
            $item['name'],
            'shipxio_shipping_startup_calculator_render_item_fields',
            'shipxio-shipping-startup-calculator',
            'shipxio_shipping_startup_calculator_items',
            array('key' => $item['key'], 'class' => 'shipxio-startup-item-row')
        );
    }
    add_settings_field(
        'shipxio_shipping_startup_calculator_new_item',
        __('Add an item', 'shipxio-shipping-startup-calculator'),
        'shipxio_shipping_startup_calculator_render_new_item_fields',
        'shipxio-shipping-startup-calculator',
        'shipxio_shipping_startup_calculator_items',
        array('class' => 'shipxio-startup-new-row')
    );
}

/** Record a rejected value; the previous value is kept. */
function shipxio_shipping_startup_calculator_settings_error($code, $message)
{
    add_settings_error('shipxio_shipping_startup_calculator_items', $code, $message);
}

/** The error shown when a numeric price is rejected. */
function shipxio_shipping_startup_calculator_cost_error($key, $name, $currency)
{
    shipxio_shipping_startup_calculator_settings_error(
        'invalid-cost-' . $key,
        sprintf(
            'JMD' === $currency
                /* translators: %s: calculator item name. */
                ? __('%s: the price must be a whole number of zero or more. The previous price was kept.', 'shipxio-shipping-startup-calculator')
                /* translators: %s: calculator item name. */
                : __('%s: the price must be zero or more, with up to two decimal places. The previous price was kept.', 'shipxio-shipping-startup-calculator'),
            $name
        )
    );
}

/** An administrator-added item in its stored form. */
function shipxio_shipping_startup_calculator_stored_custom_item($item)
{
    $stored = array(
        'key' => $item['key'],
        'name' => $item['name'],
        'description' => $item['description'],
        'pricing' => shipxio_shipping_startup_calculator_pricing_type($item),
    );
    if (isset($item['priority'])) {
        $stored['priority'] = $item['priority'];
    }
    if (null === $item['cost']) {
        $stored['price_label'] = $item['price_label'];
    } else {
        $stored['cost'] = (float) $item['cost'];
    }
    return $stored;
}

/**
 * Sanitize one administrator-added item.
 *
 * Accepts both the form fields ("price") and the stored form ("cost" or
 * "price_label"), so a second sanitize pass on the same save is a no-op.
 * On invalid input the previous version is kept, or a new item is not added.
 */
function shipxio_shipping_startup_calculator_sanitize_custom_item($submitted, $key, $previous)
{
    $fallback = null === $previous ? null : shipxio_shipping_startup_calculator_stored_custom_item($previous);
    $types = shipxio_shipping_startup_calculator_pricing_types();

    $name = sanitize_text_field(is_string($submitted['name'] ?? null) ? $submitted['name'] : '');
    $label = '' !== $name ? $name : ($previous['name'] ?? __('New item', 'shipxio-shipping-startup-calculator'));
    if ('' === $name) {
        shipxio_shipping_startup_calculator_settings_error(
            'invalid-name-' . $key,
            null === $previous
                ? __('A new item needs a name. It was not added.', 'shipxio-shipping-startup-calculator')
                /* translators: %s: calculator item name. */
                : sprintf(__('%s: the name cannot be empty. The previous version was kept.', 'shipxio-shipping-startup-calculator'), $label)
        );
        return $fallback;
    }

    $pricing = $submitted['pricing'] ?? '';
    if (! is_string($pricing) || ! isset($types[$pricing])) {
        /* translators: %s: calculator item name. */
        shipxio_shipping_startup_calculator_settings_error('invalid-pricing-' . $key, sprintf(__('%s: choose how the item is priced.', 'shipxio-shipping-startup-calculator'), $label));
        return $fallback;
    }

    $stored = array(
        'key' => $key,
        'name' => $name,
        'description' => sanitize_textarea_field(is_string($submitted['description'] ?? null) ? $submitted['description'] : ''),
        'pricing' => $pricing,
    );

    // Priority is optional: an unknown value means no badge.
    $priority = $submitted['priority'] ?? '';
    if (is_string($priority) && isset(shipxio_shipping_startup_calculator_priority_labels()[$priority])) {
        $stored['priority'] = $priority;
    }

    $price = $submitted['price'] ?? ($submitted['cost'] ?? ($submitted['price_label'] ?? null));
    if ('variable' === $pricing) {
        $text = sanitize_text_field(is_string($price) ? $price : '');
        if ('' === $text) {
            /* translators: %s: calculator item name. */
            shipxio_shipping_startup_calculator_settings_error('invalid-cost-' . $key, sprintf(__('%s: enter the text to show when the price varies.', 'shipxio-shipping-startup-calculator'), $label));
            return $fallback;
        }
        $stored['price_label'] = $text;
        return $stored;
    }

    $cost = shipxio_shipping_startup_calculator_parse_cost($price, $types[$pricing][0]);
    if (null === $cost) {
        shipxio_shipping_startup_calculator_cost_error($key, $label, $types[$pricing][0]);
        return $fallback;
    }
    $stored['cost'] = $cost;
    return $stored;
}

/**
 * Sanitize the whole items option.
 *
 * Default items store only deviations from their definitions. Administrator
 * items are stored under "_custom", and a non-default order under "_order".
 */
function shipxio_shipping_startup_calculator_sanitize_items($value)
{
    $value = is_array($value) ? $value : array();
    $current = shipxio_shipping_startup_calculator_items();
    $currentByKey = array_column($current, null, 'key');
    $overrides = array();

    foreach (shipxio_shipping_startup_calculator_default_items() as $item) {
        $previous = $currentByKey[$item['key']];
        $submitted = is_array($value[$item['key']] ?? null) ? $value[$item['key']] : array();
        foreach (array('name', 'description') as $field) {
            $text = $previous[$field];
            if (isset($submitted[$field]) && is_string($submitted[$field])) {
                $text = $submitted[$field];
            }
            $text = 'name' === $field ? sanitize_text_field($text) : sanitize_textarea_field($text);
            if ($text !== $item[$field]) {
                $overrides[$item['key']][$field] = $text;
            }
        }

        // Priority: a known classification or "" for none; anything else keeps the current one.
        $priority = $previous['priority'] ?? '';
        $submittedPriority = $submitted['priority'] ?? null;
        if (is_string($submittedPriority) && ('' === $submittedPriority || isset(shipxio_shipping_startup_calculator_priority_labels()[$submittedPriority]))) {
            $priority = $submittedPriority;
        }
        if ($priority !== ($item['priority'] ?? '')) {
            $overrides[$item['key']]['priority'] = $priority;
        }

        // Invalid prices are rejected: the saved price stays and an error is shown.
        if (null === $item['cost']) {
            $label = $previous['price_label'];
            if (array_key_exists('price_label', $submitted)) {
                $text = sanitize_text_field(is_string($submitted['price_label']) ? $submitted['price_label'] : '');
                if ('' === $text) {
                    shipxio_shipping_startup_calculator_settings_error(
                        'invalid-cost-' . $item['key'],
                        /* translators: %s: calculator item name. */
                        sprintf(__('%s: the price text cannot be empty. The previous text was kept.', 'shipxio-shipping-startup-calculator'), $previous['name'])
                    );
                } else {
                    $label = $text;
                }
            }
            if ($label !== $item['price_label']) {
                $overrides[$item['key']]['price_label'] = $label;
            }
            continue;
        }

        $cost = $previous['cost'];
        if (array_key_exists('cost', $submitted)) {
            $parsed = shipxio_shipping_startup_calculator_parse_cost($submitted['cost'], $item['currency']);
            if (null === $parsed) {
                shipxio_shipping_startup_calculator_cost_error($item['key'], $previous['name'], $item['currency']);
            } else {
                $cost = $parsed;
            }
        }
        if ((float) $cost !== (float) $item['cost']) {
            $overrides[$item['key']]['cost'] = (float) $cost;
        }
    }

    $customs = array();
    foreach ((array) ($value['_custom'] ?? array()) as $submitted) {
        $key = is_array($submitted) ? ($submitted['key'] ?? '') : '';
        if (! is_string($key) || 1 !== preg_match('/^custom-[a-z0-9]{10}$/', $key) || isset($customs[$key])) {
            continue;
        }
        if (! empty($submitted['remove'])) {
            continue;
        }
        $stored = shipxio_shipping_startup_calculator_sanitize_custom_item($submitted, $key, $currentByKey[$key] ?? null);
        if (null !== $stored) {
            $customs[$key] = $stored;
        }
    }

    // A filled-in name adds one new item per save, at the end of the list.
    $new = $value['_new'] ?? null;
    if (is_array($new) && is_string($new['name'] ?? null) && '' !== trim($new['name'])) {
        do {
            $key = 'custom-' . strtolower(wp_generate_password(10, false, false));
        } while (isset($customs[$key]) || isset($currentByKey[$key]));
        $stored = shipxio_shipping_startup_calculator_sanitize_custom_item($new, $key, null);
        if (null !== $stored) {
            $customs[$key] = $stored;
        }
    }

    if ($customs) {
        $overrides['_custom'] = array_values($customs);
    }

    $natural = array_merge(array_column(shipxio_shipping_startup_calculator_default_items(), 'key'), array_keys($customs));
    $requested = $value['_order'] ?? array_column($current, 'key');
    $ordered = array_column(
        shipxio_shipping_startup_calculator_apply_order(array_map(static fn ($key) => array('key' => $key), $natural), $requested),
        'key'
    );
    if ($ordered !== $natural) {
        $overrides['_order'] = $ordered;
    }

    return $overrides;
}

/** The fixed currency and billing period shown beside each price field. */
function shipxio_shipping_startup_calculator_price_unit($item)
{
    $units = array(
        'JMD|one_time' => __('JMD, one-time', 'shipxio-shipping-startup-calculator'),
        'USD|month'    => __('USD per month', 'shipxio-shipping-startup-calculator'),
        'USD|year'     => __('USD per year', 'shipxio-shipping-startup-calculator'),
    );
    return $units[$item['currency'] . '|' . $item['billing_period']] ?? $item['currency'];
}

/** Labels for the pricing choices of administrator-added items. */
function shipxio_shipping_startup_calculator_pricing_labels()
{
    return array(
        'jmd_one_time' => __('JMD, one-time', 'shipxio-shipping-startup-calculator'),
        'usd_month'    => __('USD per month', 'shipxio-shipping-startup-calculator'),
        'usd_year'     => __('USD per year', 'shipxio-shipping-startup-calculator'),
        'variable'     => __('Varies (shown as text, not included in totals)', 'shipxio-shipping-startup-calculator'),
    );
}

function shipxio_shipping_startup_calculator_render_pricing_select($id, $name, $selected)
{
    ?>
    <label for="<?php echo esc_attr($id); ?>-pricing"><?php echo esc_html__('Pricing', 'shipxio-shipping-startup-calculator'); ?></label>
    <select id="<?php echo esc_attr($id); ?>-pricing" name="<?php echo esc_attr($name); ?>[pricing]">
        <?php foreach (shipxio_shipping_startup_calculator_pricing_labels() as $type => $label) : ?>
            <option value="<?php echo esc_attr($type); ?>" <?php selected($selected, $type); ?>><?php echo esc_html($label); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function shipxio_shipping_startup_calculator_render_priority_select($id, $name, $selected)
{
    ?>
    <label for="<?php echo esc_attr($id); ?>-priority"><?php echo esc_html__('Priority', 'shipxio-shipping-startup-calculator'); ?></label>
    <select id="<?php echo esc_attr($id); ?>-priority" name="<?php echo esc_attr($name); ?>[priority]">
        <?php foreach (shipxio_shipping_startup_calculator_priority_labels() as $priority => $label) : ?>
            <option value="<?php echo esc_attr($priority); ?>" <?php selected($selected, $priority); ?>><?php echo esc_html($label); ?></option>
        <?php endforeach; ?>
        <option value="" <?php selected($selected, ''); ?>><?php echo esc_html__('None', 'shipxio-shipping-startup-calculator'); ?></option>
    </select>
    <?php
}

function shipxio_shipping_startup_calculator_register_settings_page()
{
    add_options_page(
        __('Shipxio Shipping Startup Calculator', 'shipxio-shipping-startup-calculator'),
        __('Shipxio Shipping Startup Calculator', 'shipxio-shipping-startup-calculator'),
        'manage_options',
        'shipxio-shipping-startup-calculator',
        'shipxio_shipping_startup_calculator_render_settings_page'
    );
}

function shipxio_shipping_startup_calculator_enqueue_settings_assets($hook)
{
    if ('settings_page_shipxio-shipping-startup-calculator' !== $hook || ! current_user_can('manage_options')) {
        return;
    }
    $url = plugin_dir_url(SHIPXIO_SHIPPING_STARTUP_CALCULATOR_FILE);
    wp_enqueue_style('shipxio-shipping-startup-calculator-admin', $url . 'assets/css/admin.css', array(), SHIPXIO_SHIPPING_STARTUP_CALCULATOR_VERSION);
    wp_enqueue_script('shipxio-shipping-startup-calculator-admin', $url . 'assets/js/admin.js', array(), SHIPXIO_SHIPPING_STARTUP_CALCULATOR_VERSION, true);
}

/** The calculator's own local mark, never the Shipxio Connect mark. */
function shipxio_shipping_startup_calculator_mark_url()
{
    return plugin_dir_url(SHIPXIO_SHIPPING_STARTUP_CALCULATOR_FILE) . 'assets/images/shipping-startup-calculator-mark.webp';
}

function shipxio_shipping_startup_calculator_render_item_fields($args)
{
    foreach (shipxio_shipping_startup_calculator_items() as $item) {
        if ($args['key'] !== $item['key']) {
            continue;
        }
        $custom = ! empty($item['custom']);
        $id = 'shipxio-startup-item-' . $item['key'];
        $option = 'shipxio_shipping_startup_calculator_items';
        $name = $custom ? $option . '[_custom][' . $item['key'] . ']' : $option . '[' . $item['key'] . ']';
        ?>
        <div class="shipxio-startup-item-fields">
            <input type="hidden" name="<?php echo esc_attr($option); ?>[_order][]" value="<?php echo esc_attr($item['key']); ?>">
            <?php if ($custom) : ?>
                <input type="hidden" name="<?php echo esc_attr($name); ?>[key]" value="<?php echo esc_attr($item['key']); ?>">
            <?php endif; ?>
            <?php // Shown by admin.js; without JavaScript the saved order is kept. ?>
            <div class="shipxio-startup-move" hidden>
                <?php /* translators: %s: calculator item name. */ ?>
                <button type="button" class="button button-small" data-shipxio-move="up" aria-label="<?php echo esc_attr(sprintf(__('Move %s up', 'shipxio-shipping-startup-calculator'), $item['name'])); ?>">
                    <span aria-hidden="true">&uarr;</span> <?php echo esc_html__('Up', 'shipxio-shipping-startup-calculator'); ?>
                </button>
                <?php /* translators: %s: calculator item name. */ ?>
                <button type="button" class="button button-small" data-shipxio-move="down" aria-label="<?php echo esc_attr(sprintf(__('Move %s down', 'shipxio-shipping-startup-calculator'), $item['name'])); ?>">
                    <span aria-hidden="true">&darr;</span> <?php echo esc_html__('Down', 'shipxio-shipping-startup-calculator'); ?>
                </button>
            </div>
            <label for="<?php echo esc_attr($id); ?>-name"><?php echo esc_html__('Name', 'shipxio-shipping-startup-calculator'); ?></label>
            <input type="text" class="regular-text" id="<?php echo esc_attr($id); ?>-name" name="<?php echo esc_attr($name); ?>[name]" value="<?php echo esc_attr($item['name']); ?>">
            <label for="<?php echo esc_attr($id); ?>-description"><?php echo esc_html__('Description', 'shipxio-shipping-startup-calculator'); ?></label>
            <textarea class="large-text" rows="3" id="<?php echo esc_attr($id); ?>-description" name="<?php echo esc_attr($name); ?>[description]"><?php echo esc_textarea($item['description']); ?></textarea>
            <?php shipxio_shipping_startup_calculator_render_priority_select($id, $name, $item['priority'] ?? ''); ?>
            <?php if ($custom) : ?>
                <?php shipxio_shipping_startup_calculator_render_pricing_select($id, $name, shipxio_shipping_startup_calculator_pricing_type($item)); ?>
                <label for="<?php echo esc_attr($id); ?>-price"><?php echo esc_html__('Price or price text', 'shipxio-shipping-startup-calculator'); ?></label>
                <input type="text" class="regular-text" id="<?php echo esc_attr($id); ?>-price" name="<?php echo esc_attr($name); ?>[price]" value="<?php echo esc_attr(null === $item['cost'] ? $item['price_label'] : shipxio_shipping_startup_calculator_cost_input_value($item['cost'])); ?>">
                <label class="shipxio-startup-remove">
                    <input type="checkbox" name="<?php echo esc_attr($name); ?>[remove]" value="1">
                    <?php echo esc_html__('Remove this item when saving', 'shipxio-shipping-startup-calculator'); ?>
                </label>
            <?php elseif (null === $item['cost']) : ?>
                <label for="<?php echo esc_attr($id); ?>-price-label"><?php echo esc_html__('Price text (shown as written, not included in totals)', 'shipxio-shipping-startup-calculator'); ?></label>
                <input type="text" class="regular-text" id="<?php echo esc_attr($id); ?>-price-label" name="<?php echo esc_attr($name); ?>[price_label]" value="<?php echo esc_attr($item['price_label']); ?>" required>
            <?php else : ?>
                <label for="<?php echo esc_attr($id); ?>-cost">
                    <?php
                    /* translators: %s: currency and billing period, such as "USD per month". */
                    echo esc_html(sprintf(__('Price (%s)', 'shipxio-shipping-startup-calculator'), shipxio_shipping_startup_calculator_price_unit($item)));
                    ?>
                </label>
                <input type="text" class="regular-text" inputmode="decimal" id="<?php echo esc_attr($id); ?>-cost" name="<?php echo esc_attr($name); ?>[cost]" value="<?php echo esc_attr(shipxio_shipping_startup_calculator_cost_input_value($item['cost'])); ?>" required>
            <?php endif; ?>
        </div>
        <?php
        break;
    }
}

/** Blank fields for one new item; it is added on save when a name is filled in. */
function shipxio_shipping_startup_calculator_render_new_item_fields()
{
    $id = 'shipxio-startup-new-item';
    $name = 'shipxio_shipping_startup_calculator_items[_new]';
    ?>
    <div class="shipxio-startup-item-fields">
        <label for="<?php echo esc_attr($id); ?>-name"><?php echo esc_html__('Name', 'shipxio-shipping-startup-calculator'); ?></label>
        <input type="text" class="regular-text" id="<?php echo esc_attr($id); ?>-name" name="<?php echo esc_attr($name); ?>[name]" value="">
        <label for="<?php echo esc_attr($id); ?>-description"><?php echo esc_html__('Description', 'shipxio-shipping-startup-calculator'); ?></label>
        <textarea class="large-text" rows="3" id="<?php echo esc_attr($id); ?>-description" name="<?php echo esc_attr($name); ?>[description]"></textarea>
        <?php shipxio_shipping_startup_calculator_render_priority_select($id, $name, ''); ?>
        <?php shipxio_shipping_startup_calculator_render_pricing_select($id, $name, 'jmd_one_time'); ?>
        <label for="<?php echo esc_attr($id); ?>-price"><?php echo esc_html__('Price or price text', 'shipxio-shipping-startup-calculator'); ?></label>
        <input type="text" class="regular-text" id="<?php echo esc_attr($id); ?>-price" name="<?php echo esc_attr($name); ?>[price]" value="">
        <p class="description"><?php echo esc_html__('Enter a name to add this item when you save. JMD amounts are whole numbers; USD amounts allow two decimal places. For varying prices, enter the text to show, such as “Free – Varies”. New items are added at the end of the list.', 'shipxio-shipping-startup-calculator'); ?></p>
    </div>
    <?php
}

/** One row of the Connect-style shortcode reference, with a copy button. */
function shipxio_shipping_startup_calculator_render_shortcode_row($shortcode, $title, $description)
{
    ?>
    <li class="shipxio-startup-shortcode">
        <div class="shipxio-startup-shortcode-text">
            <strong><?php echo esc_html($title); ?></strong>
            <span><?php echo esc_html($description); ?></span>
        </div>
        <div class="shipxio-startup-shortcode-action">
            <code><?php echo esc_html($shortcode); ?></code>
            <button type="button" class="button button-secondary shipxio-startup-copy" data-shipxio-copy="<?php echo esc_attr($shortcode); ?>" data-shipxio-copied-label="<?php echo esc_attr__('Copied', 'shipxio-shipping-startup-calculator'); ?>">
                <span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
                <span class="shipxio-startup-copy-label" aria-live="polite"><?php echo esc_html__('Copy', 'shipxio-shipping-startup-calculator'); ?></span>
            </button>
        </div>
    </li>
    <?php
}

function shipxio_shipping_startup_calculator_render_settings_page()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to manage these settings.', 'shipxio-shipping-startup-calculator'));
    }
    ?>
    <div class="wrap shipxio-startup-admin">
        <?php // WordPress anchors admin notices after the first heading in .wrap. ?>
        <h1 class="screen-reader-text"><?php echo esc_html__('Shipxio Shipping Startup Calculator', 'shipxio-shipping-startup-calculator'); ?></h1>
        <?php settings_errors(); ?>
        <header class="shipxio-startup-masthead">
            <?php if (is_file(dirname(SHIPXIO_SHIPPING_STARTUP_CALCULATOR_FILE) . '/assets/images/shipping-startup-calculator-mark.webp')) : ?>
                <img class="shipxio-startup-mark" src="<?php echo esc_url(shipxio_shipping_startup_calculator_mark_url()); ?>" width="48" height="48" alt="" decoding="async">
            <?php endif; ?>
            <div class="shipxio-startup-masthead-text">
                <p class="shipxio-startup-wordmark"><?php echo esc_html__('Shipxio Shipping Startup Calculator', 'shipxio-shipping-startup-calculator'); ?></p>
                <p class="shipxio-startup-tagline"><?php echo esc_html__('Help people planning to start a shipping company estimate their costs.', 'shipxio-shipping-startup-calculator'); ?></p>
            </div>
            <span class="shipxio-startup-version"><?php echo esc_html('v' . SHIPXIO_SHIPPING_STARTUP_CALCULATOR_VERSION); ?></span>
        </header>

        <form method="post" action="options.php" class="shipxio-startup-form">
            <?php settings_fields('shipxio_shipping_startup_calculator'); ?>
            <section class="shipxio-startup-card">
                <div class="shipxio-startup-card-head">
                    <h2><?php echo esc_html__('Calculator Items', 'shipxio-shipping-startup-calculator'); ?></h2>
                    <p><?php echo esc_html__('Edit, add and reorder the items shown in the calculator. Built-in items keep their currency and billing period.', 'shipxio-shipping-startup-calculator'); ?></p>
                </div>
                <div class="shipxio-startup-card-body">
                    <p class="screen-reader-text" aria-live="polite" data-shipxio-move-status></p>
                    <table class="form-table" role="presentation">
                        <?php do_settings_fields('shipxio-shipping-startup-calculator', 'shipxio_shipping_startup_calculator_items'); ?>
                    </table>
                </div>
            </section>
            <div class="shipxio-startup-actions">
                <?php submit_button(__('Save Changes', 'shipxio-shipping-startup-calculator'), 'primary', 'submit', false); ?>
            </div>
        </form>

        <section class="shipxio-startup-card">
            <div class="shipxio-startup-card-head">
                <h2><?php echo esc_html__('Shortcodes', 'shipxio-shipping-startup-calculator'); ?></h2>
                <p><?php echo esc_html__('Add a shortcode to WordPress content, a Gutenberg Shortcode block, or Elementor’s standard Shortcode widget.', 'shipxio-shipping-startup-calculator'); ?></p>
            </div>
            <div class="shipxio-startup-card-body">
                <ul class="shipxio-startup-shortcodes">
                    <?php
                    shipxio_shipping_startup_calculator_render_shortcode_row(
                        '[shipxio_shipping_startup_calculator]',
                        __('Calculator', 'shipxio-shipping-startup-calculator'),
                        __('Calculator with its title and description.', 'shipxio-shipping-startup-calculator')
                    );
                    shipxio_shipping_startup_calculator_render_shortcode_row(
                        '[shipxio_shipping_startup_calculator show_intro="false"]',
                        __('Calculator, title hidden', 'shipxio-shipping-startup-calculator'),
                        __('Calculator without its title and description, for pages that already provide their own heading.', 'shipxio-shipping-startup-calculator')
                    );
                    ?>
                </ul>
                <p class="shipxio-startup-hint"><?php echo esc_html__('Your theme or Elementor container controls the calculator’s outer width and spacing.', 'shipxio-shipping-startup-calculator'); ?></p>
            </div>
        </section>
    </div>
    <?php
}
