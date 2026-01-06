<?php
if (!defined('ABSPATH')) exit;

add_shortcode('rss_magazine', function ($atts) {
    global $wpdb;

    $items = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}rma_items
        ORDER BY published_at DESC
        LIMIT 10
    ");

    if (!$items) return '<p>No stories available.</p>';

    ob_start();
    ?>
    <div class="rma-magazine">
        <?php foreach ($items as $i => $item): ?>
            <article class="<?php echo $i === 0 ? 'rma-featured' : 'rma-item'; ?>">
                <?php if ($item->image_url): ?>
                    <img src="<?php echo esc_url($item->image_url); ?>" />
                <?php endif; ?>
                <h3><?php echo esc_html($item->title); ?></h3>
                <p><?php echo esc_html($item->excerpt); ?></p>
                <small><?php echo esc_html($item->source); ?> · <?php echo date('d M Y', strtotime($item->published_at)); ?></small>
                <p><a href="<?php echo esc_url($item->link); ?>" target="_blank">Read more</a></p>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});
