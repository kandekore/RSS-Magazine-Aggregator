<?php
if (!defined('ABSPATH')) exit;

add_action('rma_fetch_feeds', 'rma_fetch_feeds');

function rma_fetch_feeds() {
    global $wpdb;

    $feeds    = (array) get_option('rma_feeds', []);
    $keywords = array_filter((array) get_option('rma_keywords', []));

    if (empty($feeds) || empty($keywords)) {
        return;
    }

    foreach ($feeds as $feed_url) {

        $rss = fetch_feed($feed_url);
        if (is_wp_error($rss)) {
            continue;
        }

        foreach ($rss->get_items(0, 50) as $item) {

            $title = trim($item->get_title());
            $desc  = wp_strip_all_tags($item->get_description());
            $haystack = strtolower($title . ' ' . $desc);

            // Keyword match (OR logic)
            $match = false;
            foreach ($keywords as $kw) {
                if ($kw !== '' && stripos($haystack, $kw) !== false) {
                    $match = true;
                    break;
                }
            }
            if (!$match) continue;

            $link = esc_url_raw($item->get_link());
            if (!$link) continue;

            $hash = md5($link);

            /* IMAGE RESOLUTION */
            $image = '';

            // 1. Enclosure
            $enclosure = $item->get_enclosure();
            if ($enclosure && $enclosure->get_link()) {
                $image = esc_url_raw($enclosure->get_link());
            }

            // 2. media:content
            if (!$image) {
                $media = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
                if (!empty($media[0]['attribs']['']['url'])) {
                    $image = esc_url_raw($media[0]['attribs']['']['url']);
                }
            }

            // 3. media:thumbnail
            if (!$image) {
                $thumb = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
                if (!empty($thumb[0]['attribs']['']['url'])) {
                    $image = esc_url_raw($thumb[0]['attribs']['']['url']);
                }
            }

            $published = $item->get_date('Y-m-d H:i:s');
            if (!$published) {
                $published = current_time('mysql');
            }

            // Insert safely (ignore duplicates)
            $wpdb->query(
                $wpdb->prepare("
                    INSERT IGNORE INTO {$wpdb->prefix}rma_items
                    (unique_hash, source, feed_url, title, excerpt, link, image_url, published_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                ",
                $hash,
                parse_url($feed_url, PHP_URL_HOST),
                $feed_url,
                $title,
                wp_trim_words($desc, 30),
                $link,
                $image,
                $published
                )
            );
        }
    }
}
