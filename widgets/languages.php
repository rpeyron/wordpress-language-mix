<?php
/**
 * Languages widget
 */
class WP_Widget_Languages extends WP_Widget {

    public function __construct() {
        load_textdomain('language-mix', plugin_dir_path(__FILE__) . 'languages/language-mix-' . get_locale() . '.mo');

        parent::__construct('languages', __('Languages', 'language-mix'),
            array('classname' => 'widget_languages', 'description' => __('Allows switching to other languages', 'language-mix'))
        );
    }

    /**
     * Front-end display of widget
     */
    public function widget($args, $instance) { # TODO
        extract($args);
        $title = apply_filters('widget_title', empty($instance['title']) ? __('Languages', 'language-mix') : $instance['title'], $instance, $this->id_base);
        ?>
        <?php echo $before_widget; ?>
        <?php if ($title) { echo $before_title . $title . $after_title; } ?>
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
          <label for="<?php esc_attr_e($this->get_field_id('title')); ?>"><?php _e('Title'); ?>:</label>
          <input class="widefat" id="<?php esc_attr_e($this->get_field_id('title')); ?>" name="<?php esc_attr_e($this->get_field_name('title')); ?>" type="text" value="<?php esc_attr_e($instance['title']); ?>" />
        </p>
        <?php
    }

}
?>