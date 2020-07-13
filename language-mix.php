<?php
/**
 * Plugin Name: Language Mix
 * Plugin URI: http://projects.andriylesyuk.com/project/wordpress/language-mix
 * Description: This plugin unhides contents which are in languages you speak.
 * Version: 1.0
 * Author: Andriy Lesyuk
 * Author URI: http://www.andriylesyuk.com
 * Text Domain: language-mix
 * License: GPL2
 */

/**
 * Copyright 2014 Andriy Lesyuk (email:s-andy@andriylesyuk.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('PLLX_COOKIE',    'pllx_languages');
define('PLLX_PARAMETER', 'pllx_language');

define('PLL_PLUGIN_NAME', 'polylang/polylang.php');

require_once(ABSPATH . 'wp-admin/includes/plugin.php');

$author_rules_backup = array();

/**
 * Iterrupt plugin activation if Polylang is not activated/installed
 */
function pllx_check_polylang($network_wide) {
    if (!is_plugin_active(PLL_PLUGIN_NAME)) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Language Mix plugin requires Polylang to be installed and activated.', 'language-mix'));
    }
}
register_activation_hook(__FILE__, 'pllx_check_polylang');

/**
 * Deactivates Language Mix if Polylang is not active
 */
function pllx_admin_init() {
    if (!is_plugin_active(PLL_PLUGIN_NAME)) {
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_init', 'pllx_admin_init');

/**
 * Load translations
 */
function pllx_loaded() {
    load_plugin_textdomain('language-mix', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'pllx_loaded');

/**
 * Handles POST requests
 */
function pllx_init() {
    global $polylang;
    if ($polylang && isset($_POST[PLLX_PARAMETER])) {
        if (is_array($_POST[PLLX_PARAMETER]) && !empty($_POST[PLLX_PARAMETER])) {
            $languages = array();
            foreach ($polylang->model->get_languages_list() as $language) {
                if (in_array($language->slug, $_POST[PLLX_PARAMETER])) {
                    $languages[] = $language->slug;
                }
            }
            if (!empty($languages)) {
                $languages_cookie = implode(',', $languages);
                setcookie(PLLX_COOKIE, $languages_cookie, time() + (10 * 365 * 86400), COOKIEPATH, COOKIE_DOMAIN);
                $_COOKIE[PLLX_COOKIE] = $languages_cookie;
            }
        } else {
            setcookie(PLLX_COOKIE, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
            unset($_COOKIE[PLLX_COOKIE]);
        }
    }
}
add_action('init', 'pllx_init');

require(plugin_dir_path(__FILE__) . 'widgets/languages.php');
require(plugin_dir_path(__FILE__) . 'widgets/translations.php');

/**
 * Registers widget(s)
 */
function pllx_widgets_init() {
    register_widget('WP_Widget_Languages');
    register_widget('WP_Widget_Translations');
}
add_action('widgets_init', 'pllx_widgets_init');

/**
 * Modifies the SQL query for posts
 */
function pllx_posts_where($where) {
    global $polylang;

    if ($polylang && preg_match("/post_type = 'post'/", $where) && !is_tax('language')) {
        $slugs = pllx_enabled_languages();

        if (count($slugs) > 0) {

            if (is_home()) {
                $languages = array();

                foreach ($slugs as $slug) {
                    $languages[] = (int)$polylang->model->get_language($slug)->term_taxonomy_id;
                }

                return preg_replace('/\.term_taxonomy_id IN \([^\)]*\)/', '.term_taxonomy_id IN (' . implode(',', $languages) . ')', $where);

            } else if (is_category() || is_tag() || is_tax()) {
                $term         = get_queried_object();
                $translations = pllx_get_translations_with_children($term->term_id, $term->taxonomy, $slugs);

                if (count($translations) > 0) {
                    return preg_replace('/\.term_taxonomy_id IN \([^\)]*\)/', '.term_taxonomy_id IN (' . implode(',', $translations) . ')', $where);
                }
            }

        }
    }

    return $where;
}
add_filter('posts_where', 'pllx_posts_where', 20);

/**
 * Translates titles of categories in menus
 */
function pllx_nav_menu_objects($items) {
    global $locale;
    global $polylang;

    $queried_object = get_queried_object();

    $item_selected = false;
    foreach ($items as $item) {
        if ($item->current) {
            $item_selected = true;
            break;
        }
    }

    $pages      = array();
    $categories = array();
    if ($item_selected == false) {
        if (is_category()) {
            $categories = pllx_get_translations_with_parents($queried_object->cat_ID);
        } else if (is_page()) {
            $pages = $polylang->model->get_translations('page', $queried_object->ID);
        } else if (is_single() && ($queried_object->post_type == 'post')) {
            $post_categories = wp_get_post_categories($queried_object->ID);
            foreach ($post_categories as $category_id) {
                $categories = array_merge($categories, pllx_get_translations_with_parents($category_id));
            }
            $categories = array_unique($categories);
        }
    }

    foreach ($items as &$item) {
        if ($item->object == 'page') {
            $page = get_post($item->object_id);
            if ($page) {
                $language = $polylang->model->get_post_language($item->object_id);
                if ($language && ($language->locale != $locale)) {
                    $translation_id = pll_get_post($item->object_id, $locale);
                    if ($translation_id) {
                        $page = get_post($translation_id);
                        if ($page) {
                            $item->title = $page->post_title;
                        }
                    }
                }
            }
            if ($item_selected == false) {
                if (is_page()) {
                    if (in_array($item->object_id, $pages)) {
                        $item->classes[] = 'current-menu-item';
                        $item_selected = true;
                    }
                }
            }
        } else if ($item->object == 'category') {
            $category = get_term($item->object_id, 'category');
            if ($category) {
                $language = $polylang->model->get_term_language($item->object_id);
                if ($language && ($language->locale != $locale)) {
                    $translation_id = pll_get_term($item->object_id, $locale);
                    if ($translation_id) {
                        $translation = get_term($translation_id, 'category');
                        if ($translation) {
                            $item->title = $translation->name;
                        }
                    }
                }
            }
            if (($item_selected == false) && !empty($categories)) {
                if (is_category() || is_single()) {
                    if (in_array($item->object_id, $categories)) {
                        $item->classes[] = 'current-menu-item';
                        $item_selected = true;
                    }
                }
            }
        }
    }
    unset($item);

    return $items;
}
add_filter('wp_nav_menu_objects', 'pllx_nav_menu_objects');

/**
 * Showing empty categories by default (as their translations can contain posts)
 */
function pllx_widget_categories_args($args) {
    $args['hide_empty'] = 0;

    return $args;
}
add_filter('widget_categories_dropdown_args', 'pllx_widget_categories_args');
add_filter('widget_categories_args',          'pllx_widget_categories_args');

/**
 * Relaces the front page with the corresponding translation
 */
function pllx_get_page_on_front($post_id) {
    global $locale;
    global $polylang;

    if ($polylang && $post_id && !is_admin()) {
        $language = $polylang->model->get_post_language($post_id);
        if ($language && ($language->locale != $locale)) {
            $translated_id = pll_get_post($post_id, $locale);
            if ($translated_id) {
                return $translated_id;
            }
        }
    }

    return $post_id;
}
add_filter('option_page_on_front', 'pllx_get_page_on_front');

/**
 * Looks for translations for the category featured post
 */
function pllx_category_featured_post($post_id, $term_id) {
    global $polylang;
    if ($polylang && !$post_id) {
        $translations = $polylang->model->get_translations('term', $term_id);
        foreach ($translations as $language => $term_id) {
            if ($post_id = get_category_featured_post($term_id)) {
                break;
            }
        }
    }
    return $post_id;
}
add_filter('get_category_featured_post', 'pllx_category_featured_post', 10, 2);

/**
 * Make a copy of author rules
 */
function pllx_backup_author_rules($rules) {
    global $author_rules_backup;
    $author_rules_backup = $rules;
    return $rules;
}
add_filter('author_rewrite_rules', 'pllx_backup_author_rules');

/**
 * Restore author rules
 */
function pllx_rewrite_rules($rules) {
    global $author_rules_backup;
    return $author_rules_backup + $rules;
}
add_filter('rewrite_rules_array', 'pllx_rewrite_rules');

# TODO same for date and search? etc?

/**
 * Remove link from author URL
 */
function pllx_author_link($link) {
    global $polylang;
    return $polylang->links_model->remove_language_from_link($link);
}
add_filter('author_link', 'pllx_author_link', 30);

/**
 * Get all translations of the term (including children)
 */
function pllx_get_translations_with_children($term_id, $taxonomy, $languages) {
    global $polylang;

    $translations      = array();
    $term_translations = $polylang->model->get_translations('term', $term_id);

    foreach ($term_translations as $language => $term_id) {
        if (in_array($language, $languages)) {
            $term = get_term($term_id, $taxonomy);
            if ($term) {
                $translations[] = $term->term_taxonomy_id;

                $term_children  = get_term_children($term_id, $taxonomy);

                foreach ($term_children as $term_child) {
                    $term = get_term($term_child, $taxonomy);
                    if ($term) {
                        $translations[] = $term->term_taxonomy_id;
                    }
                }
            }
        }
    }

    return $translations;
}

/**
 * Get all translations of the category (including parents)
 */
function pllx_get_translations_with_parents($category_id) {
    global $polylang;

    $translations      = array();
    $cat_translations = $polylang->model->get_translations('term', $category_id);

    foreach ($cat_translations as $language => $cat_id) {
        $translations[] = $cat_id;
        $translations   = array_merge($translations, pllx_get_category_parents($cat_id));
    }

    return $translations;
}

/**
 * Merges all category parents into a single array
 */
function pllx_get_category_parents($id, $visited = array()) {
    $parents = array();
    $parent  = get_category($id);

    if (!is_wp_error($parent)) {
        if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited)) {
            $visited[] = $parent->parent;
            $parents   = array_merge($parents, pllx_get_category_parents($parent->parent, $visited));
        }

        $parents[] = $parent->term_id;
    }

    return $parents;
}

/**
 * Parses the Accept-Language header
 */
function pllx_browser_languages() {
    $languages = array();

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $accepted_language) {
            list($lang, $q) = explode(';', $accepted_language);

            if ($q && preg_match('/\bq=([0-9]+(?:\.[0-9]+)?)/', $q, $m)) {
                $q = floatval($m[1]);
            } else {
                $q = 1;
            }

            $lang = str_replace('-', '_', $lang);
            $languages[$lang] = $q;
        }
        arsort($languages);
    }

    return $languages;
}

/**
 * Returns the array of enabled languages
 */
function pllx_enabled_languages() {
    global $polylang;

    if (isset($_COOKIE[PLLX_COOKIE])) {
        return explode(',', $_COOKIE[PLLX_COOKIE]);
    } else {
        $enabled_languages = array();
        $browser_languages = pllx_browser_languages();

        foreach ($polylang->model->get_languages_list() as $language) {
            if (isset($browser_languages[$language->slug]) ||
                isset($browser_languages[$language->locale])) {
                $enabled_languages[] = $language->slug;
            }
        }

        return $enabled_languages;
    }
}

/**
 * Fetches term taxonomy by ID
 */
function pllx_get_term_taxonomy($id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d",
        $id
    ));
}

?>