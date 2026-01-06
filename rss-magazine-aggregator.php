<?php
/**
 * Plugin Name: RSS Magazine Aggregator
 * Description: Aggregates RSS feeds, filters by keywords, and displays magazine-style layouts.
 * Version: 1.0.2
 * Author: Darren Kandekore
 */

if (!defined('ABSPATH')) exit;

define('RMA_PATH', plugin_dir_path(__FILE__));
define('RMA_URL', plugin_dir_url(__FILE__));

global $rma_db_version;
$rma_db_version = '1.0';

/**
 * Activation: Create DB table + schedule cron
 */
register_activation_hook(__FILE__, 'rma_activate');
function rma_activate() {
    global $wpdb, $rma_db_version;

    $table = $wpdb->prefix . 'rma_items';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        unique_hash CHAR(32) NOT NULL,
        source VARCHAR(190),
        feed_url TEXT,
        title TEXT,
        excerpt TEXT,
        link TEXT,
        image_url TEXT,
        published_at DATETIME,
        is_featured TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_hash (unique_hash)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    add_option('rma_db_version', $rma_db_version);

    if (!wp_next_scheduled('rma_fetch_feeds')) {
        wp_schedule_event(time(), 'hourly', 'rma_fetch_feeds');
    }
}

/**
 * Deactivation: Clear cron
 */
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('rma_fetch_feeds');
});

require_once RMA_PATH . 'admin-settings.php';
require_once RMA_PATH . 'cron-fetch.php';
require_once RMA_PATH . 'shortcode-magazine.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('rma-style', RMA_URL . 'assets/style.css');
});
