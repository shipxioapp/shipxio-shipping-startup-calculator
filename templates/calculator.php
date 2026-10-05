<?php
/** The proven startup calculator markup inside a WordPress shortcode. */

if (! defined('ABSPATH')) {
    exit;
}

/** @var string $id */
/** @var bool $show_intro */
/** @var array $items */
/** @var array $priority_labels */
?>
<div class="shipxio-shipping-startup-calculator">
    <?php // Without the heading the section still needs an accessible name. ?>
    <?php if ($show_intro) : ?>
    <section class="startup-calculator" aria-labelledby="<?php echo esc_attr($id); ?>-title">
        <header class="calculator-header">
            <p class="calculator-eyebrow"><?php echo esc_html__('STARTUP COST CALCULATOR', 'shipxio-shipping-startup-calculator'); ?></p>
            <h2 id="<?php echo esc_attr($id); ?>-title"><?php echo esc_html__('Estimate Your Startup Cost.', 'shipxio-shipping-startup-calculator'); ?></h2>
            <p><?php echo esc_html__('Select the items that apply to your shipping company and see an estimated startup cost.', 'shipxio-shipping-startup-calculator'); ?></p>
        </header>
    <?php else : ?>
    <section class="startup-calculator" aria-label="<?php echo esc_attr__('Shipping Startup Calculator', 'shipxio-shipping-startup-calculator'); ?>">
    <?php endif; ?>
        <form class="calculator-form">
            <div class="calculator-items">
                <?php foreach ($items as $shipxio_shipping_startup_calculator_item) : ?>
                    <?php
                    $shipxio_shipping_startup_calculator_input_id = $id . '-item-' . $shipxio_shipping_startup_calculator_item['key'];
                    $shipxio_shipping_startup_calculator_priority = $shipxio_shipping_startup_calculator_item['priority'];
                    $shipxio_shipping_startup_calculator_details = $shipxio_shipping_startup_calculator_item['details'] ?? null;
                    ?>
                    <div class="calculator-row">
                        <label class="calculator-item" for="<?php echo esc_attr($shipxio_shipping_startup_calculator_input_id); ?>">
                            <input
                                id="<?php echo esc_attr($shipxio_shipping_startup_calculator_input_id); ?>"
                                class="calculator-checkbox"
                                type="checkbox"
                                name="startup_items[]"
                                value="<?php echo esc_attr($shipxio_shipping_startup_calculator_item['key']); ?>"
                                data-cost="<?php echo esc_attr((string) $shipxio_shipping_startup_calculator_item['cost']); ?>"
                                data-currency="<?php echo esc_attr($shipxio_shipping_startup_calculator_item['currency']); ?>"
                                data-billing-period="<?php echo esc_attr($shipxio_shipping_startup_calculator_item['billing_period']); ?>"
                            >
                            <span class="calculator-check" aria-hidden="true"></span>
                            <span class="calculator-item-content">
                                <span class="calculator-item-heading">
                                    <span class="calculator-item-name"><?php echo esc_html($shipxio_shipping_startup_calculator_item['name']); ?></span>
                                    <span class="calculator-priority priority-<?php echo esc_attr($shipxio_shipping_startup_calculator_priority); ?>"><?php echo esc_html($priority_labels[$shipxio_shipping_startup_calculator_priority]); ?></span>
                                </span>
                                <span class="calculator-item-description"><?php echo esc_html($shipxio_shipping_startup_calculator_item['description']); ?></span>
                                <?php if (isset($shipxio_shipping_startup_calculator_item['aside'])) : ?>
                                    <span class="calculator-item-aside"><?php echo esc_html($shipxio_shipping_startup_calculator_item['aside']); ?></span>
                                <?php endif; ?>
                            </span>
                            <strong class="calculator-item-price"><?php echo esc_html(shipxio_shipping_startup_calculator_price($shipxio_shipping_startup_calculator_item)); ?></strong>
                        </label>

                        <?php if (null !== $shipxio_shipping_startup_calculator_details) : ?>
                            <details class="calculator-details">
                                <summary class="calculator-details-summary"><?php echo esc_html__('See what’s included', 'shipxio-shipping-startup-calculator'); ?></summary>
                                <div class="calculator-details-body">
                                    <p class="calculator-details-title"><?php echo esc_html__('What’s included', 'shipxio-shipping-startup-calculator'); ?></p>
                                    <p class="calculator-details-intro"><?php echo esc_html($shipxio_shipping_startup_calculator_details['intro']); ?></p>
                                    <ul class="calculator-details-list">
                                        <?php foreach ($shipxio_shipping_startup_calculator_details['list'] as $shipxio_shipping_startup_calculator_entry) : ?>
                                            <li><?php echo esc_html($shipxio_shipping_startup_calculator_entry); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php if (isset($shipxio_shipping_startup_calculator_details['image'])) : ?>
                                        <img
                                            class="calculator-details-image"
                                            src="<?php echo esc_url(plugin_dir_url(SHIPXIO_SHIPPING_STARTUP_CALCULATOR_FILE) . $shipxio_shipping_startup_calculator_details['image']['src']); ?>"
                                            width="<?php echo esc_attr((string) $shipxio_shipping_startup_calculator_details['image']['width']); ?>"
                                            height="<?php echo esc_attr((string) $shipxio_shipping_startup_calculator_details['image']['height']); ?>"
                                            alt="<?php echo esc_attr($shipxio_shipping_startup_calculator_details['image']['alt']); ?>"
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="calculator-summary">
                <div class="calculator-summary-primary">
                    <span><?php echo esc_html__('Estimated Startup Cost', 'shipxio-shipping-startup-calculator'); ?></span>
                    <strong data-ssc-id="startup-total" aria-live="polite">J$0</strong>
                </div>
                <div data-ssc-id="recurring-costs" class="calculator-recurring" aria-live="polite" hidden>
                    <div data-ssc-id="monthly-cost-row" hidden>
                        <span><?php echo esc_html__('Monthly Cost', 'shipxio-shipping-startup-calculator'); ?></span>
                        <strong data-ssc-id="monthly-total">US$0/month</strong>
                    </div>
                    <div data-ssc-id="yearly-cost-row" hidden>
                        <span><?php echo esc_html__('Yearly Cost', 'shipxio-shipping-startup-calculator'); ?></span>
                        <strong data-ssc-id="yearly-total">US$0/year</strong>
                    </div>
                </div>
                <p class="calculator-note"><?php echo esc_html__('This estimate is for planning purposes. Actual costs may vary.', 'shipxio-shipping-startup-calculator'); ?></p>
            </div>
        </form>
    </section>
</div>
