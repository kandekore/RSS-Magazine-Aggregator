<?php
if (!defined('ABSPATH')) exit;

add_action('rma_fetch_feeds', 'rma_fetch_feeds');

function rma_fetch_feeds() {
    global $wpdb;
    $feeds = (array) get_option('rma_feeds', []);
    $keywords = array_filter((array) get_option('rma_keywords', []));

    foreach ($feeds as $feed_url) {
        $rss = fetch_feed($feed_url);
        if (is_wp_error($rss)) continue;

        foreach ($rss->get_items(0, 20) as $item) {

            $title = $item->get_title();
            $desc  = wp_strip_all_tags($item->get_description());
            $text  = strtolower($title . ' ' . $desc);

            $match = false;
            foreach ($keywords as $kw) {
                if (stripos($text, $kw) !== false) {
                    $match = true;
                    break;
                }
            }
            if (!$match) continue;

            $link = esc_url_raw($item->get_link());
            $hash = md5($link);

            $image = '';
            $enclosure = $item->get_enclosure();
            if ($enclosure && $enclosure->get_link()) {
                $image = esc_url_raw($enclosure->get_link());
            }

            $wpdb->insert(
                $wpdb->prefix . 'rma_items',
                [
                    'unique_hash' => $hash,
                    'source'      => parse_url($feed_url, PHP_URL_HOST),
                    'feed_url'    => $feed_url,
                    'title'       => $title,
                    'excerpt'     => wp_trim_words($desc, 30),
                    'link'        => $link,
                    'image_url'   => $image,
                    'published_at'=> date('Y-m-d H:i:s', strtotime($item->get_date()))
                ],
                ['%s','%s','%s','%s','%s','%s','%s','%s']
            );
        }
    }
}
