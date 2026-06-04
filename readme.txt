=== SideMenu ===
Contributors: domainsupport
Donate link: https://webd.uk/product/sidemenu-upgrade/
Tags: slide menu, side menu, side nav, vertical menu
Requires at least: 4.6
Tested up to: 7.0
Requires PHP: 5.6
Stable tag: 1.9.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Injects a sliding side menu / sidebar into any theme!

== Description ==
= SideMenu =

This plugin works really well with new FSE (Full Site Editing) themes such as Twenty Twenty-Two. Check out the menu in the [Rye](https://ryesussex.uk "Rye") website for an example real-world use of this lightweight plugin!

https://www.youtube.com/watch?v=p4L5DzNPC3g

This plugin will inject a responsive SideMenu sidebar and menu location into your theme that you can add widgets to.

The SideMenu is opened by clicking on a menu button that you can add to any menu in your site.

When the SideMenu slides open, the entire site is dimmed and slid to the left.

The SideMenu is closed by either clicking the close icon at the top right of the SideMenu, clicking on the dimmed site or by scrolling down.

This plugin can also hijack the existing mobile menu button within your theme if required. Themes that already work with this feature are ...

* Astra
* Auction Theme (AT10)
* Avada
* Avanam
* BeTheme
* Divi
* Genesis Block Theme
* Genesis Framework
* Inspiro
* OKAB
* Twenty Seventeen
* Twenty Twenty
* Twenty Twenty-One
* Twenty Twenty-Two
* Varia (and child themes like Rockfield)

... ask us in the [SideMenu Support Forum](https://wordpress.org/support/plugin/sidemenu/ "SideMenu Support Forum") to add your theme!

You can also enable a "Mega Menu" style dropdown menu feature.

= How to use the SideMenu block =

The SideMenu block to show the button to open the SideMenu. The optional title attribute is the text that is shown on the button.

Please note that for the meantime, all options are still found in "Customizer - SideMenu".

= How to use the [sidemenu] shortcode =

* Use [sidemenu] to just show the default button
* Use [sidemenu]My Button Title[/sidemenu] or [sidemenu title="My Button Title"] to show the button with your own text
* Use [sidemenu class="my-class" title="Filtered SideMenu Button"] to show a button which only shows widgets or menu items with the specified class

To add a class to a menu item in "Dashboard - Appearance - Menus" click "Screen Options in the top right hand corner of the screen and tick "CSS Classes". The menu items will then show a "CSS Classes" field when expanded.

To see the class of a widget in "Dashboard - Appearance - Widgets", expand the widget that you've added to the SideMenu widget area and the class name is shown at the bottom. Since WordPress v5.8 with the new Block Widget Editor, it's super easy to set a custom CSS class for each block, just select the block and in the block options on the right, scroll to the bottom and expand "Advanced" where you can add them into "Additional CSS class(es)".

This video will help to explain how to use classes to control the content of the widget ...

https://www.youtube.com/watch?v=or5jVYgMoQE

To use a link as a button to open the SideMenu, set the class attribute of the link to be "open_sidemenu" `<a href="#" class="open_sidemenu">Example Link</a>`. You can add classes to the element to control what content is shown as with the shortcode.

To use an anchor link to open the sidemenu, set the link to be "#open_sidemenu". This doesn't require a class to be set. To control what content is shown, add a dash and the class of the element(s) to be shown, for example "#open_sidemenu-testclass".

== Installation ==

Easily use this plugin to add a SideMenu to your WordPress website ...

1) Install "SideMenu" automatically or by uploading the ZIP file.
2) Activate the plugin through the "Plugins" menu in WordPress.
3) Add the SideMenu open button to an existing menu on your site in "Appearance - Menus" or by using a [sidemenu] shortcode in a widget or post.
4) Add widgets to the SideMenu sidebar in "Appearance - Widgets" or add a menu to the SideMenu display location in "Appearance - Menus".

https://www.youtube.com/watch?v=ac50F_Sufog

== Changelog ==

= 1.9.0 =
* Fix a minor issue highlighted by "Plugin Check" code review and preparing for WordPress v7.0

= 1.8.9 =
* General housekeeping preparing for "Plugin Check" code review

= 1.8.8 =
* Added support to hijack the mobile menu toggle in Genesis Framework theme

= 1.8.7 =
* Added support to hijack the mobile menu toggle in Avanam theme

= 1.8.6 =
* Added support to hijack the mobile menu toggle in Inspiro theme

= 1.8.5 =
* Bug fix to allow a Button Block to open the SideMenu by adding the following classes to the block "wp-block-sidemenu-sidemenu open_sidemenu"

= 1.8.4 =
* Bug fix for Divi theme mobile menu hijack

= 1.8.3 =
* Remove Customizer CSS due to incompatible change introduced in WordPress v6.7

= 1.8.2 =
* Added support for Genesis Block Theme's custom header mobile menu button hijack

= 1.8.1 =
* Fixed a bug with the "Settings" link in the plugins panel

= 1.8 =
* Fixed an "unexpected output" bug

= 1.7.9 =
* Minor security updates to translation strings

= 1.7.8 =
* Fixed JavaScript bug with SideMenu block (for real this time!)

= 1.7.7 =
* Fixed JavaScript bug with SideMenu block

= 1.7.6 =
* Added a SideMenu block to add a button to open the SideMenu in Gutenberg

= 1.7.5 =
* General housekeeping

= 1.7.4 =
* Added support to hijack the mobile menu toggle in Inspiro theme

= 1.7.3 =
* Add a fix for a bug in WordPress core that fires wp_footer hook when managing Widgets and another bug in v6.2.2 that adds <br> and <p> tags to shortcodes in block theme templates

= 1.7.2 =
* Added title attribute to buttons and cleaned up JavaScript

= 1.7.1 =
* General housekeeping

= 1.7.0 =
* Added aria-label to open / close button icon wrappers

= 1.6.9 =
* Updated details of new mobile width / min width options

= 1.6.8 =
* General housekeeping

= 1.6.7 =
* Fixed a bug where the SideMenu closed when the iPhone keyboard appeared after clicking on an input element

= 1.6.6 =
* Added support to hijack the mobile menu toggle in Twenty Twenty theme

= 1.6.5 =
* Bug fix to delay by 5 milliseconds to allow Divi JavaScript to generate the mobile menu

= 1.6.4 =
* Preparing for WordPress v6.0

= 1.6.3 =
* Text Domain issue resolved

= 1.6.2 =
* Submenu toggle CSS tweak

= 1.6.1 =
* Bug fix

= 1.6.0 =
* Added support to hijack the mobile menu toggle in Auction Theme (AT10) and Twenty Twenty-Two themes

= 1.5.9 =
* Fixed a bug where # anchor links would not close the SideMenu when clicked

= 1.5.8 =
* Added details of some new premium options (menu item hover animation, grow content to fit SideMenu, rounded corners and close icon background color)

= 1.5.7 =
* Added option to show a border to the left of menu items in the SideMenu

= 1.5.6 =
* Added Customizer Panel and more Sections to make settings easier to find

= 1.5.5 =
* Various bug fixes

= 1.5.4 =
* Removed block margins when block has a background
* Added option to prevent admin auto-open with tooltips

= 1.5.3 =
* Use a dashicon for the submenu toggle icon
* On toggle close child submenus are closed too
* Added option to show the number of menu items in a submenu

= 1.5.2 =
* Added support to hijack the mobile menu toggle in Varia and Rockfield themes

= 1.5.1 =
* Use anchor links to open the SideMenu with #open_sidemenu

= 1.5.0 =
* Use links to open the SideMenu by adding "open_sidemenu" to the link class attribute

= 1.4.9 =
* Fixed a bug with Twenty Twenty-One mobile menu hijack

= 1.4.8 =
* Bug fixes including opening / closing transitions and hiding Twenty Twenty-One submenu toggles

= 1.4.7 =
* Bug fix for class filtering with themes that add extra wrappers to menu elements

= 1.4.6 =
* Added support for Block Widget Editor and removed all PHP short tags

= 1.4.5 =
* Preparing for WordPress v5.8

= 1.4.4 =
* Bug fix for Astra theme mobile menu hijack

= 1.4.3 =
* Added a control to increase the z-index property of the SideMenu

= 1.4.2 =
* Bug fix for Astra theme mobile menu hijack

= 1.4.1 =
* Bug fix for Astra theme sites using jQuery

= 1.4.0 =
* General housekeeping

= 1.3.9 =
* Bugfixes

= 1.3.8 =
* Fixed issue with Divi theme and dropdown submenus on iOS devies
* Fixed issue with SideMenu that can be seen on iPhone in landscape view
* Added a checkbox to choose to hide scrollbar

= 1.3.7 =
* Bug fix issue with submenu click events on iOS devices

= 1.3.6 =
* Added support to hijack the mobile menu toggle in Divi theme
* Added CSS to hide the vertical scrollbar on Windows browsers

= 1.3.5 =
* Added support for BeTheme's custom header mobile menu button hijack

= 1.3.4 =
* General housekeeping

= 1.3.3 =
* Bug fix to allow mobile menu hijack to work when no SideMenu toggle is on the page

= 1.3.2 =
* Added option to enable dropdown menus on Navigation Menu widgets

= 1.3.1 =
* Added option to enable dropdown menus

= 1.3.0 =
* Added support to hijack the mobile menu toggle in OKAB theme

= 1.2.9 =
* Bug fix

= 1.2.8 =
* Added the ability to filer the SideMenu to allow different SideMenu buttons to show different content in the SideMenu

= 1.2.7 =
* Added the ability to show the hamburger icon before or after the SideMenu button title

= 1.2.6 =
* Added support to hijack the mobile menu toggle in [Twenty Twenty-One](https://wordpress.org/themes/twentytwentyone/ "Twenty Twenty-One") and BeTheme themes

= 1.2.5 =
* General housekeeping

= 1.2.4 =
* Updated JavaScript to work with IE11

= 1.2.3 =
* Bug fix

= 1.2.2 =
* Added support to hijack the mobile menu toggle in [Astra theme](https://en-gb.wordpress.org/themes/astra/ "Astra")

= 1.2.1 =
* Added support to hijack the mobile menu toggle in [Twenty Seventeen theme](https://en-gb.wordpress.org/themes/twentyseventeen/ "Twenty Seventeen")

= 1.2.0 =
* CSS adjustments

= 1.1.9 =
* CSS adjustments

= 1.1.8 =
* CSS adjustments

= 1.1.7 =
* Bug fix

= 1.1.6 =
* Redeveloped the JavaScript to remove requirement for jQuery

= 1.1.5 =
* Bug fixes

= 1.1.4 =
* Bug fixes

= 1.1.3 =
* Moved all premium options to a new premium plugin

= 1.1.2 =
* Option to change width of section border

= 1.1.1 =
* Option for text size
* Bug fix

= 1.1.0 =
* Options for title size, weight, alignment and case

= 1.0.9 =
* Bug fix

= 1.0.8 =
* Options to change opacity and color of site overlay

= 1.0.7 =
* Bug fixes

= 1.0.6 =
* Option to choose your own Dashicon

= 1.0.5 =
* Option to use "hamburger" icon on open button(s)

= 1.0.4 =
* Options for color and width
* Allow SideeMenu scrolling

= 1.0.3 =
* New [sidemenu] shortcode
* Started adding premium options

= 1.0.2 =
* Full width SideMenu on mobile
* SideMenu display location

= 1.0.1 =
* Customizer option to position the menu on the left or the right

= 1.0.0 =
* First version of the plugin

== Upgrade Notice ==

= 1.9.0 =
* Fix a minor issue highlighted by "Plugin Check" code review and preparing for WordPress v7.0
