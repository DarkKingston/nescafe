CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Troubleshooting
 * Maintainers
 * Extend


INTRODUCTION
------------

Qualifio’s goal is to make marketing useful by making it shoppable. As a site owner, I want to easily connect my contests from the third-party solution Qualifio into my LightNest brand site.


REQUIREMENTS
------------

## Additional steps to fully implement

####Installation and use

Install the module as usual. The module requires no extra configuration.

After the installation you'll have a new field type called "qualifio". You can add it directly to your Content Type or to another fieldable entity you're using in your site.

####Configuration
Installation of this module is just like any other Drupal module.

1) Go to drupal extend and enable the module.
2) Once enabled the module go to "Lightnest Qualifio" under configuration >> Lightnest >> Lightnest Qualifio and enter feed
url.
3) Go to content type and create field field_qualifio with type of Lightnest Qualifio type.
4) Configure the field and save the field settings.
5) Go to node/add/component_page and select qualifio component and enter required details to see the widget.
6) Save node form and Clear the cache.


TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--ln-qualifio.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Gallery.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.
 * The module comes with a simple twig template called "qualifio.html.twig"
 * You can copy this template in your theme and customize it.

Multlingual Widget
------------------
  * Qualifio multilingual widget rendering.
  * We added one dynamic variable language for getting the current language code in all twigs files.
  * Any agency can override the twig and add same variable to get current language widget.

MAINTAINERS
-----------

* Nestle Webcms team.
