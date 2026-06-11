=== SideMenu ===
Contributors: domainsupport
Donate link: https://webd.uk/product/sidemenu-block-upgrade/
Tags: side menu, sliding menu, off canvas menu, mobile menu, sidebar menu
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 5.6
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a responsive sliding SideMenu to your WordPress site with the SideMenu block.

== Description ==

SideMenu adds an off-canvas panel that slides in from the side of your site and can be opened from a block button placed in your content, templates or template parts.

Version 2.0 introduces a block-first workflow for modern WordPress sites. Create reusable SideMenu content in the editor, choose the default SideMenu content for free blocks, then place the "SideMenu Open Button" block wherever visitors should be able to open the panel.

The block workflow is ideal for block themes and Full Site Editing because the SideMenu button can live directly in a template, header, navigation area, page or post.

= Key features =

* SideMenu Open Button block for posts, pages, templates and template parts.
* Reusable SideMenu content managed as its own editor content type.
* Default SideMenu content selector in the editor.
* Button text and hamburger icon controls.
* Block styling support for color, typography, spacing, dimensions and borders.
* Responsive sliding panel with cover overlay and close button.
* Developer filters for default content and panel settings.

= How to use the SideMenu block =

1. Go to "SideMenus" in the WordPress admin and add the content you want to show in the sliding panel.
2. Add the "SideMenu Open Button" block to a post, page, template or template part.
3. Set the button text and choose whether to show a hamburger icon.
4. Choose the default SideMenu content from the editor's SideMenu settings panel.

The free block uses one default SideMenu content item. Developers can filter the block content ID with the `sidemenu_block_content_id` filter.

= Classic theme and classic SideMenu support =

Classic SideMenu features are still available when your site uses a classic theme, or when the plugin detects existing classic SideMenu usage. This keeps existing shortcodes, widget sidebars, menu locations, mobile menu hijacks and Customizer settings working while giving block themes a cleaner setup path.

On classic themes, SideMenu loads the original Customizer-driven runtime. This provides the traditional SideMenu sidebar, menu location, shortcode, mobile menu hijack options and classic styling controls.

If you move an older site to a block theme, SideMenu checks for existing classic usage and keeps the classic runtime available when it finds classic SideMenu blocks, shortcodes, open links, widgets, menu locations or mobile menu hijack settings.

= How to use the [sidemenu] shortcode =

Classic shortcode support is still available when the classic runtime is active.

* Use `[sidemenu]` to show the default button.
* Use `[sidemenu]My Button Title[/sidemenu]` or `[sidemenu title="My Button Title"]` to show your own button text.
* Use `[sidemenu class="my-class" title="Filtered SideMenu Button"]` to show a button that only displays classic widgets or menu items with the specified class.

To add a class to a menu item, go to "Appearance - Menus", open "Screen Options" and enable "CSS Classes". To add a class to a block widget, select the block in "Appearance - Widgets", open the "Advanced" panel and use "Additional CSS class(es)".

= How to open SideMenu from a link =

To use a normal link as a SideMenu button, give it the `open_sidemenu` class:

`<a href="#" class="open_sidemenu">Example Link</a>`

To use an anchor link, set the link URL to `#open_sidemenu`.

For classic filtered content, add a dash and the class name to the anchor. For example, `#open_sidemenu-testclass` opens SideMenu and shows classic content marked with the `testclass` class.

= Supported mobile menu hijacks =

SideMenu can hijack the existing mobile menu button in a range of themes, including:

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
* Varia and child themes such as Rockfield

== Installation ==

1. Install "SideMenu" automatically from the WordPress plugin directory, or upload the plugin ZIP file.
2. Activate the plugin from the "Plugins" screen in WordPress.
3. For block themes, add your panel content under "SideMenus" and place the "SideMenu Open Button" block where you want the button.
4. For classic themes, add the SideMenu button to a menu, widget or post, then add widgets to the SideMenu sidebar or assign a menu to the SideMenu display location.
5. Adjust any classic settings in "Customizer - SideMenu" when classic mode is active.

== Frequently Asked Questions ==

= Where are the SideMenu settings? =

For block themes, reusable SideMenu content is managed under "SideMenus" in the WordPress admin. The default SideMenu content can be chosen from the editor's SideMenu settings panel.

For classic themes and existing classic SideMenu usage, settings are available in "Customizer - SideMenu".

= Why can I not see the classic Customizer settings on my block theme? =

On block themes, SideMenu only loads the classic Customizer settings when a classic trigger is detected. Add and save a classic SideMenu block, shortcode or `open_sidemenu` trigger in published content, an active widget area or an assigned classic navigation menu, or enable the mobile menu hijack setting if your site still needs the classic runtime.

= Can different buttons open different SideMenu content? =

Classic SideMenu buttons can filter widget and menu content by class. The free SideMenu block uses one default SideMenu content item.

= Does SideMenu still support widgets and menus? =

Yes. Widget sidebar and menu location support remain available through the classic runtime.

== Changelog ==

= 2.0 =
* Added a block-first SideMenu workflow with reusable SideMenu content.
* Added SideMenu content management and default content selection for the block editor.
* Improved compatibility with block themes while preserving classic SideMenu usage when detected.
* Updated plugin metadata and Plugin Check compatibility.

== Upgrade Notice ==

= 2.0 =
SideMenu now requires WordPress 6.2 or later and introduces the new block-first SideMenu workflow while preserving detected classic usage.
