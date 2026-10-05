<?php
/** WordPress-owned name and description overrides for the fixed calculator items. */

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
    add_settings_section('shipxio_shipping_startup_calculator_items', '', '__return_false', 'shipxio-shipping-startup-calculator');
    foreach (shipxio_shipping_startup_calculator_default_items() as $item) {
        add_settings_field(
            'shipxio_shipping_startup_calculator_' . $item['key'],
            $item['name'],
            'shipxio_shipping_startup_calculator_render_item_fields',
            'shipxio-shipping-startup-calculator',
            'shipxio_shipping_startup_calculator_items',
            array('key' => $item['key'])
        );
    }
}

/** Whitelist the eight stable keys and two text fields; store deviations only. */
function shipxio_shipping_startup_calculator_sanitize_items($value)
{
    $current = shipxio_shipping_startup_calculator_items();
    $overrides = array();
    foreach (shipxio_shipping_startup_calculator_default_items() as $index => $item) {
        $submitted = is_array($value) ? ($value[$item['key']] ?? array()) : array();
        foreach (array('name', 'description') as $field) {
            $text = $current[$index][$field];
            if (is_array($submitted) && isset($submitted[$field]) && is_string($submitted[$field])) {
                $text = $submitted[$field];
            }
            $text = 'name' === $field ? sanitize_text_field($text) : sanitize_textarea_field($text);
            if ($text !== $item[$field]) {
                $overrides[$item['key']][$field] = $text;
            }
        }
    }
    return $overrides;
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
        $id = 'shipxio-startup-item-' . $item['key'];
        $name = 'shipxio_shipping_startup_calculator_items[' . $item['key'] . ']';
        ?>
        <div class="shipxio-startup-item-fields">
            <label for="<?php echo esc_attr($id); ?>-name"><?php echo esc_html__('Name', 'shipxio-shipping-startup-calculator'); ?></label>
            <input type="text" class="regular-text" id="<?php echo esc_attr($id); ?>-name" name="<?php echo esc_attr($name); ?>[name]" value="<?php echo esc_attr($item['name']); ?>">
            <label for="<?php echo esc_attr($id); ?>-description"><?php echo esc_html__('Description', 'shipxio-shipping-startup-calculator'); ?></label>
            <textarea class="large-text" rows="3" id="<?php echo esc_attr($id); ?>-description" name="<?php echo esc_attr($name); ?>[description]"><?php echo esc_textarea($item['description']); ?></textarea>
        </div>
        <?php
        break;
    }
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
                    <p><?php echo esc_html__('Edit the names and descriptions shown in the calculator.', 'shipxio-shipping-startup-calculator'); ?></p>
                </div>
                <div class="shipxio-startup-card-body">
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
