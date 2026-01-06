<?php
if (!defined('ABSPATH')) exit;

add_shortcode('rss_magazine', function ($atts) {
    global $wpdb;

    $atts = shortcode_atts([
        'limit' => 20
    ], $atts);

    $limit = max(1, intval($atts['limit']));

    $items = $wpdb->get_results(
        $wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}rma_items
            ORDER BY published_at DESC
            LIMIT %d
        ", $limit)
    );

    if (!$items) {
        return '<p>No stories available.</p>';
    }

    ob_start();
    ?>
    <div class="rma-magazine">
        <?php foreach ($items as $index => $item): ?>
            <article class="<?php echo $index === 0 ? 'rma-featured' : 'rma-item'; ?>">
                
                <?php if (!empty($item->image_url)): ?>
                    <img 
                        src="<?php echo esc_url($item->image_url); ?>" 
                        alt="<?php echo esc_attr($item->title); ?>"
                        loading="lazy"
                    />
                <?php endif; ?>

                <h3><?php echo esc_html($item->title); ?></h3>

                <?php if (!empty($item->excerpt)): ?>
                    <p><?php echo esc_html($item->excerpt); ?></p>
                <?php endif; ?>

                <small>
                    <?php echo esc_html($item->source); ?>
                    · <?php echo esc_html(date('d M Y', strtotime($item->published_at))); ?>
                </small>

                <p>
                    <a href="<?php echo esc_url($item->link); ?>" target="_blank" rel="noopener">
                        Read more
                    </a>
                </p>
            </article>
        <?php endforeach; ?>
    </div>
    <?php

    return ob_get_clean();
});
