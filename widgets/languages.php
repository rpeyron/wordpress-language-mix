<?php
/**
 * Languages widget
 */
class WP_Widget_Languages extends WP_Widget {

    public function __construct() {
        # FIXME load_textdomain('language-mix', plugin_dir_path(__FILE__) . 'languages/language-mix-' . get_locale() . '.mo');

        parent::__construct('languages', __('Languages', 'language-mix'),
            array('classname' => 'widget_languages', 'description' => __('Allows to configure shown languages', 'language-mix'))
        );
    }

    /**
     * Front-end display of widget
     */
    public function widget($args, $instance) {
        global $polylang;
        extract($args);
        $enabled_languages = pllx_enabled_languages();
        $title = apply_filters('widget_title', empty($instance['title']) ? __('Languages', 'language-mix') : $instance['title'], $instance, $this->id_base);
        ?>
        <?php echo $before_widget; ?>
        <?php if ($title) { echo $before_title . $title . $after_title; } ?>
        <form method="post">
          <p>
            <input type="hidden" name="<?php echo PLLX_PARAMETER; ?>" value="" />
            <?php foreach ($polylang->model->get_languages_list() as $language): ?>
              <label>
                <input type="checkbox" class="checkbox" name="<?php echo PLLX_PARAMETER; ?>[]" value="<?php echo esc_attr($language->slug); ?>" <?php if (in_array($language->slug, $enabled_languages)) { echo 'checked="checked"'; } ?> />
                <?php echo $language->flag; ?> <?php echo esc_html($language->name); ?>
              </label><br />
            <?php endforeach; ?>
          </p>
          <p class="submit">
            <input type="submit" name="wp-submit" class="button button-primary" value="<?php _e('Apply'); ?>" />
          </p>
        </form>
        <?php echo $after_widget; ?>
        <?php
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