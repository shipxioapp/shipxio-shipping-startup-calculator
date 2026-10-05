<?php
/**
 * Removes everything Shipxio Shipping Startup Calculator stored.
 *
 * WordPress loads this file only when the plugin is deleted. Deactivating the
 * plugin never reaches it, so settings survive a deactivate and reactivate.
 * The constant below is defined by WordPress immediately before the include,
 * so the guard also stops the file doing anything if it is reached any other
 * way, such as a direct request.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete the plugin's stored data for the site that is currently active.
 *
 * Only the calculator item text overrides and updater failure flag belong
 * to this plugin.
 */
function shipxio_shipping_startup_calculator_delete_site_data()
{
    $options = array(
        'shipxio_shipping_startup_calculator_items',
    );

    foreach ($options as $option) {
        delete_option($option);
    }

    // The update check's short-lived failure flag. It expires on its own, but
    // a site without cron can keep the row long after the plugin is gone.
    delete_transient('shipxio_shipping_startup_calculator_update_failed');
}

if (is_multisite()) {
    // Each site stores its own calculator text overrides.
    foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $shipxio_shipping_startup_calculator_site_id) {
        switch_to_blog($shipxio_shipping_startup_calculator_site_id);
        shipxio_shipping_startup_calculator_delete_site_data();
        restore_current_blog();
    }
} else {
    shipxio_shipping_startup_calculator_delete_site_data();
}
