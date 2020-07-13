=== Language Mix ===
Contributors: s-andy
Donate link: https://load.payoneer.com/LoadToPage.aspx?email=s-andy@andriylesyuk.com
Tags: multilingual, widget, polylang, cookies, browser languages, translations
Requires at least: 3.5.1
Tested up to: 3.7.1
Stable tag: 1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin modifies the behavior of the Polylang plugin making it show contents of all languages, which are believed to be known by the visitor.

== Description ==

There are countries, where people speak several languages. For these people there is no need to
separate the content of the site by such languages, especially when they come with some unique
(i.e., not translated) content. On the other side, there can still be people, who speak only one
language.

The Language Mix plugin for WordPress is an extension to the
[Polylang](http://wordpress.org/plugins/polylang/) plugin of
[Chouby](http://profiles.wordpress.org/chouby/), that implements a new approach of the multilingual
content handling. Its main feature is that it does not hide content in other languages, which are
known to the visitor! The plugin determines, which languages the user can read, using HTTP headers
sent by the user's browser.

Additionally, the plugin comes with two widgets:

* The **Languages** widget, that can be put on any WordPress page, allows to configure, content in
which languages the user wants to see. The widget saves its settings into cookies.
* The **Translations** widget, that can be put on the single content page only (e.g., the post page),
is an alternative to the Polylang's *Language Switcher* widget. The difference is that this widget
shows links only to translations of the current page (i.e., if no translations are available,
nothing is shown).

The [plugin's project](http://projects.andriylesyuk.com/project/wordpress/language-mix) is also
hosted on the [author's website](http://www.andriylesyuk.com) *(using
[Redmine](http://www.redmine.org/) and WordPress)*.
[There](http://projects.andriylesyuk.com/project/wordpress/language-mix) you can read news, report
bugs, and more.

*For the banner I used [the image](http://www.flickr.com/photos/fdecomite/3288906696/) of
[Francesco De Comite](http://www.flickr.com/photos/fdecomite/).*

== Installation ==

1. Install the [Polylang](http://wordpress.org/plugins/polylang/) plugin (check instructions
[here](http://wordpress.org/plugins/polylang/installation/))
1. Upload `language-mix` directory to `/wp-content/plugins/`
1. Activate the plugin using the Plugins menu in WordPress

== Screenshots ==

1. The Languages widget, which allows to configure shown languages (uses theme)
2. The Translations widget, which lets switching between translations (uses theme)

== Changelog ==

= 1.0 =
* Initial release

== Issue Tracker ==

Use [this issue tracker](http://projects.andriylesyuk.com/projects/language-mix/issues) to report
bugs, request features and file other issues.

== Documentation ==

The documentation for the plugin can be found in the
[Wiki](http://projects.andriylesyuk.com/projects/language-mix/wiki).

== Blog ==

The plugin's project has a blog [here](http://blog.andriylesyuk.com/projects/language-mix/).

== Live Demo ==

I'm using this plugin at [my personal website](http://www.andriylesyuk.com). A translated article
*(English and Russian)* can be found [here](http://blog.andriylesyuk.com/the-nature-of-euromaidan/).

== Thanks to ==

* [Francesco De Comite](http://www.flickr.com/photos/fdecomite/) for
[the image](http://www.flickr.com/photos/fdecomite/3288906696/) used as the plugin's banner.
