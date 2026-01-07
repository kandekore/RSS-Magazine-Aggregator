<?php
if (!defined('ABSPATH')) exit;

class RMA_Feed_List_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'rma_feed_list_widget',
            'RSS Magazine – Feed List',
            [
                'description' => 'Text-only list of aggregated RSS stories (ideal for sidebars and footers).'
            ]
        );
    }

    /**
     * Frontend output
     */
    public function widget($args, $instance) {
        global $wpdb;

        $title = !empty($instance['title'])
            ? apply_filters('widget_title', $instance['title'])
            : 'Latest News';

        $limit = !empty($instance['limit'])
            ? intval($instance['limit'])
            : 5;

        echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }

        $items = $wpdb->get_results(
            $wpdb->prepare("
                SELECT title, link, source, published_at
                FROM {$wpdb->prefix}rma_items
                ORDER BY published_at DESC
                LIMIT %d
            ", $limit)
        );

        if ($items) {
            echo '<ul class="rma-feed-list">';
            foreach ($items as $item) {
                echo '<li>';
                echo '<a href="' . esc_url($item->link) . '" target="_blank" rel="noopener">';
                echo esc_html($item->title);
                echo '</a>';
                echo '<div class="rma-feed-meta">';
                echo esc_html($item->source) . ' · ' . esc_html(date('d M Y', strtotime($item->published_at)));
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No stories available.</p>';
        }

        echo $args['after_widget'];
    }

    /**
     * Backend form
     */
    public function form($instance) {

        $title = $instance['title'] ?? 'Latest News';
        $limit = $instance['limit'] ?? 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
            <input
                class="widefat"
                id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>"
                type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">Number of items</label>
            <input
                class="tiny-text"
                id="<?php echo $this->get_field_id('limit'); ?>"
                name="<?php echo $this->get_field_name('limit'); ?>"
                type="number"
                step="1"
                min="1"
                value="<?php echo esc_attr($limit); ?>">
        </p>
        <?php
    }

    /**
     * Save options
     */
    public function update($new_instance, $old_instance) {
        return [
            'title' => sanitize_text_field($new_instance['title']),
            'limit' => intval($new_instance['limit']),
        ];
    }
}
