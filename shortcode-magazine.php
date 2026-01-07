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

                <?php
$image = !empty($item->image_url)
    ? esc_url($item->image_url)
    : esc_url( plugin_dir_url(__FILE__) . 'assets/fallback.png' );
?>

<img
    src="<?php echo $image; ?>"
    alt="<?php echo esc_attr($item->title); ?>"
    loading="lazy"
/>


                <div class="rma-content">
                    <h3><?php echo esc_html($item->title); ?></h3>

                    <?php if (!empty($item->excerpt)): ?>
                        <p><?php echo esc_html($item->excerpt); ?></p>
                    <?php endif; ?>

                <div class="meta">
                    <small>
                        <?php echo esc_html($item->source); ?>
                        · <?php echo esc_html(date('d M Y', strtotime($item->published_at))); ?>
                    </small>

                    <p class="read">
                        <a href="<?php echo esc_url($item->link); ?>" target="_blank" rel="noopener">
                            Read more
                        </a>
                    </p>
					</div>
                </div>

            </article>

        <?php endforeach; ?>

    </div>
    <?php

    return ob_get_clean();
});
