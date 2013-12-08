<?php
/**
 * Plugin Name: Language Mix
 * Plugin URI: http://projects.andriylesyuk.com/projects/language-mix
 * Description: TODO
 * Version: 1.0b
 * Author: Andriy Lesyuk
 * Author URI: http://blog.andriylesyuk.com
 * License: GPL2
 */

/**
 * Copyright 2013 Andriy Lesyuk (email:s-andy@andriylesyuk.com)
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

define('PLLX_COOKIE', 'pllx_languages');

# TODO Widget suggesting to enable/disable languages

/**
 * Handles POST requests
 */
function pllx_init() {
    if (isset($_POST['language']) && is_array($_POST['language'])) { # FIXME: more checks? or move
        $languages_cookie = implode(',', $_POST['language']);
        if (!empty($languages_cookie)) {
            setcookie(PLLX_COOKIE, $languages_cookie, time() + (10 * 365 * 86400), COOKIEPATH, COOKIE_DOMAIN);
            $_COOKIE[PLLX_COOKIE] = $languages_cookie;
            # TODO Set message
        }
        # TODO Clear cookie
        #setcookie(PLLX_COOKIE, '', time() - 3600, 0, COOKIEPATH, COOKIE_DOMAIN);
        #unset($_COOKIE[PLLX_COOKIE]);
    }
}
add_action('init', 'pllx_init');

/**
 * Modifies the SQL query for posts
 */
function pllx_posts_where($where) {
    global $polylang;

    if (preg_match("/post_type = 'post'/", $where)) {
        $languages = array();
        $slugs     = pllx_enabled_languages();

        foreach ($slugs as $slug) {
            $languages[] = (int)$polylang->model->get_language($slug)->term_taxonomy_id;
        }

        if ((count($languages) > 0) && preg_match('/\.term_taxonomy_id IN \(([^\)]*)\)/', $where, $matches)) {
            $sql_terms = explode(',', $matches[1]);

            if ((count($sql_terms) == 1) && ($term = pllx_get_term_taxonomy($sql_terms[0]))) {
                if ($term->taxonomy == 'language') {
                    return preg_replace('/\.term_taxonomy_id IN \([^\)]*\)/', '.term_taxonomy_id IN (' . implode(',', $languages) . ')', $where);
                } else {
                    $terms = $polylang->model->get_translations('term', $sql_terms[0]);
                    if (in_array(intval($sql_terms[0]), $terms)) {
                        $languages = array();

                        foreach ($slugs as $slug) {
                            if (isset($terms[$slug])) {
                                $languages[] = $terms[$slug];
                            }
                        }

                        return preg_replace('/\.term_taxonomy_id IN \([^\)]*\)/', '.term_taxonomy_id IN (' . implode(',', $languages) . ')', $where);
                    }
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

    foreach ($items as &$item) {
        if ($item->object == 'category') {
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
        }
    }
    unset($item);

    return $items;
}
add_filter('wp_nav_menu_objects', 'pllx_nav_menu_objects');

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