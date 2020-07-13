<?php
/**
 * Translations widget
 */
class WP_Widget_Translations extends WP_Widget {

    public function __construct() {
        # FIXME load_textdomain('language-mix', plugin_dir_path(__FILE__) . 'languages/language-mix-' . get_locale() . '.mo');

        parent::__construct('translations', __('Translations', 'language-mix'),
            array('classname' => 'widget_translations', 'description' => __('Easy switching between translations', 'language-mix'))
        );
    }

    /**
     * Front-end display of widget
     */
    public function widget($args, $instance) {
        global $polylang;
        extract($args);
        if (is_singular()) {
            $queried_object = get_queried_object();
            if (in_array($queried_object->post_type, array('post', 'page'))) {
                $translations = $polylang->model->get_translations($queried_object->post_type, $queried_object->ID);
                if (!empty($translations)) {
                    $title = apply_filters('widget_title', empty($instance['title']) ? __('Translations', 'language-mix') : $instance['title'], $instance, $this->id_base);
                    ?>
                    <?php echo $before_widget; ?>
                    <?php if ($title) { echo $before_title . $title . $after_title; } ?>
                    <ul>
                    <?php foreach ($polylang->model->get_languages_list() as $language): ?>
                      <?php if (isset($translations[$language->slug]) && ($translations[$language->slug] != $queried_object->ID)): ?>
                        <li>
                          <?php echo $language->flag; ?>
                          <a hreflang="<?php echo $language->slug; ?>" href="<?php echo get_permalink($translations[$language->slug]); ?>" title="<?php echo esc_attr(get_the_title($translations[$language->slug])); ?>">
                            <?php echo esc_html($language->name); ?>
                          </a>
                        </li>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                    <?php echo $after_widget; ?>
                    <?php
                }
            }
        }
    }

    /**
     * Sanitize widget form values as they are saved
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /**
     * Back-end widget form
     */
    public function form($instance) {
        $instance = wp_parse_args((array)$instance, array('title' => ''));
        ?>
        <p>
          <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title'); ?>:</label>
          <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
        </p>
        <?php
    }

}
?>