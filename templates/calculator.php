<?php
/** The startup calculator markup inside a WordPress shortcode. */

if (! defined('ABSPATH')) {
    exit;
}

/** @var string $id */
/** @var bool $show_intro */
/** @var array $items */
?>
<div class="shipxio-shipping-startup-calculator">
    <?php // Without the heading the section still needs an accessible name. ?>
    <?php if ($show_intro) : ?>
    <section class="startup-calculator" aria-labelledby="<?php echo esc_attr($id); ?>-title">
        <header class="calculator-header">
            <h3 class="calculator-eyebrow"><?php echo esc_html__('Plan Your Budget', 'shipxio-shipping-startup-calculator'); ?></h3>
            <h2 id="<?php echo esc_attr($id); ?>-title"><?php echo esc_html__('Estimate Your Startup Cost.', 'shipxio-shipping-startup-calculator'); ?></h2>
            <p><?php echo esc_html__('Select what you’ll need to start your shipping company and see your estimated costs instantly.', 'shipxio-shipping-startup-calculator'); ?></p>
        </header>
    <?php else : ?>
    <section class="startup-calculator" aria-label="<?php echo esc_attr__('Shipping Startup Calculator', 'shipxio-shipping-startup-calculator'); ?>">
    <?php endif; ?>
        <form class="calculator-form">
            <div class="calculator-selection">
                <div class="calculator-selection-header">
                    <p class="calculator-panel-title" id="<?php echo esc_attr($id); ?>-items-title"><?php echo esc_html__('Startup essentials', 'shipxio-shipping-startup-calculator'); ?></p>
                    <?php /* translators: 1: selected item count, 2: total item count. */ ?>
                    <?php $shipxio_shipping_startup_calculator_count_format = __('%1$d of %2$d selected', 'shipxio-shipping-startup-calculator'); ?>
                    <p class="calculator-count" data-ssc-id="selected-count" data-format="<?php echo esc_attr($shipxio_shipping_startup_calculator_count_format); ?>" aria-live="polite"><?php echo esc_html(sprintf($shipxio_shipping_startup_calculator_count_format, 0, count($items))); ?></p>
                </div>

                <div class="calculator-items" role="group" aria-labelledby="<?php echo esc_attr($id); ?>-items-title">
                    <?php foreach ($items as $shipxio_shipping_startup_calculator_item) : ?>
                        <?php
                        $shipxio_shipping_startup_calculator_input_id = $id . '-item-' . $shipxio_shipping_startup_calculator_item['key'];
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
                                    <span class="calculator-item-name"><?php echo esc_html($shipxio_shipping_startup_calculator_item['name']); ?></span>
                                    <span class="calculator-item-price"><?php echo esc_html(shipxio_shipping_startup_calculator_price($shipxio_shipping_startup_calculator_item)); ?></span>
                                </span>
                            </label>

                            <?php // The disclosure stays outside the label so it never toggles the checkbox. ?>
                            <details class="calculator-details">
                                <summary class="calculator-details-summary">
                                    <svg class="calculator-info-icon" viewBox="0 0 20 20" width="16" height="16" aria-hidden="true" focusable="false"><circle cx="10" cy="10" r="8.25" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M10 9v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="10" cy="6.25" r="1" fill="currentColor"/></svg>
                                    <span class="calculator-sr-only">
                                        <?php
                                        /* translators: %s: calculator item name. */
                                        echo esc_html(sprintf(__('About %s', 'shipxio-shipping-startup-calculator'), $shipxio_shipping_startup_calculator_item['name']));
                                        ?>
                                    </span>
                                </summary>
                                <div class="calculator-details-body">
                                    <p class="calculator-item-description"><?php echo esc_html($shipxio_shipping_startup_calculator_item['description']); ?></p>
                                    <?php if (isset($shipxio_shipping_startup_calculator_item['aside'])) : ?>
                                        <p class="calculator-item-aside"><?php echo esc_html($shipxio_shipping_startup_calculator_item['aside']); ?></p>
                                    <?php endif; ?>
                                    <?php if (null !== $shipxio_shipping_startup_calculator_details) : ?>
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
                                    <?php endif; ?>
                                </div>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="calculator-bulk" data-ssc-id="bulk-toggle" data-select-label="<?php echo esc_attr__('Select All', 'shipxio-shipping-startup-calculator'); ?>" data-clear-label="<?php echo esc_attr__('Clear All', 'shipxio-shipping-startup-calculator'); ?>"><?php echo esc_html__('Select All', 'shipxio-shipping-startup-calculator'); ?></button>
            </div>

            <div class="calculator-summary" role="group" aria-labelledby="<?php echo esc_attr($id); ?>-summary-title">
                <div class="calculator-summary-header">
                    <p class="calculator-panel-title" id="<?php echo esc_attr($id); ?>-summary-title"><?php echo esc_html__('Your estimate', 'shipxio-shipping-startup-calculator'); ?></p>
                    <svg class="calculator-summary-icon" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" focusable="false"><rect x="4" y="2.75" width="12" height="14.5" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M7 6.5h6M7 10.5h.01M10 10.5h.01M13 10.5h.01M7 13.5h.01M10 13.5h.01M13 13.5h.01" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </div>

                <div class="calculator-summary-primary">
                    <strong data-ssc-id="startup-total" aria-live="polite">J$0</strong>
                </div>

                <button type="button" class="calculator-breakdown-toggle" data-ssc-id="breakdown-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($id); ?>-breakdown"><?php echo esc_html__('View breakdown', 'shipxio-shipping-startup-calculator'); ?></button>

                <div class="calculator-breakdown" id="<?php echo esc_attr($id); ?>-breakdown">
                    <div data-ssc-id="recurring-costs" class="calculator-recurring" aria-live="polite" hidden>
                        <div data-ssc-id="monthly-cost-row" hidden>
                            <span><?php echo esc_html__('Monthly expenses', 'shipxio-shipping-startup-calculator'); ?></span>
                            <strong data-ssc-id="monthly-total">US$0/month</strong>
                        </div>
                        <div data-ssc-id="yearly-cost-row" hidden>
                            <span><?php echo esc_html__('Yearly expenses', 'shipxio-shipping-startup-calculator'); ?></span>
                            <strong data-ssc-id="yearly-total">US$0/year</strong>
                        </div>
                    </div>
                    <p class="calculator-note"><?php echo esc_html__('This is a planning estimate. Actual costs may vary. JMD and USD costs are shown separately.', 'shipxio-shipping-startup-calculator'); ?></p>
                </div>
            </div>
        </form>
    </section>
</div>
