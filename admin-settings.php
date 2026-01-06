<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'RSS Magazine',
        'RSS Magazine',
        'manage_options',
        'rss-magazine',
        'rma_settings_page',
        'dashicons-rss'
    );
});

function rma_settings_page() {
    if (isset($_POST['rma_save'])) {
        update_option('rma_feeds', array_map('trim', explode("\n", $_POST['feeds'])));
        update_option('rma_keywords', array_map('trim', explode("\n", $_POST['keywords'])));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    if (isset($_POST['rma_refresh'])) {
        do_action('rma_fetch_feeds');
        echo '<div class="updated"><p>Feeds refreshed.</p></div>';
    }

    if (isset($_POST['rma_clear'])) {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}rma_items");
        echo '<div class="updated"><p>All items cleared.</p></div>';
    }

    $feeds = implode("\n", (array) get_option('rma_feeds', []));
    $keywords = implode("\n", (array) get_option('rma_keywords', []));
    ?>
    <div class="wrap">
        <h1>RSS Magazine Aggregator</h1>

        <form method="post">
            <h2>Feeds (one per line)</h2>
            <textarea name="feeds" rows="6" style="width:100%"><?php echo esc_textarea($feeds); ?></textarea>

            <h2>Keywords (one per line)</h2>
            <textarea name="keywords" rows="6" style="width:100%"><?php echo esc_textarea($keywords); ?></textarea>

            <p>
                <button name="rma_save" class="button button-primary">Save Settings</button>
                <button name="rma_refresh" class="button">Refresh Now</button>
                <button name="rma_clear" class="button button-secondary">Clear All Items</button>
            </p>
        </form>
    </div>
    <?php
}
