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

Adimo’s goal is to make marketing useful by making it shoppable. Through Adimo integration,offer frictionless eCommerce solutions for brands, publishers and retailers.
This module creates a new field type that allows you to setup adimo where to buy button in any type of entity. It's llows us to guide shoppers to your products and follow them along every step of the purchasing funnel.
It’s one of the most sophisticated and effective marketing solution for product.


REQUIREMENTS
------------

## Additional steps to fully implement

####Installation and use

Install the module as usual. The module requires no extra configuration.

After the installation you'll have a new field type called "Adimo Button". You can add it directly to your Content Type or to another fieldable entity you're using in your site.

The next time you create any content using the entity where you added the Adimo Button, you'll see a textfield where you can add the Adimo Lightbox ID.

####Configuration
Installation of this module is just like any other Drupal module.

1) Go to drupal extend and enable the module.
2) Once enabled the module go to "Lightnest Adimo Integration" content type manage field.
3) Go to content type and create field field_adimo with type of Lightnest adimo type.
4) Configure the field and save the field settings.
5) Go to node/add/product and give adimo id to see the widget.
6) Add custom HTML/CSS options to override existing setting of widget.
7) Save node form and Clear the cache.

####Migration of Adimo ID's
Adimo migration means user can import multiple or bulk adimo id's in to the product type. Follow these steps
to import bulk adimo migration using Lightnest adimo.
- Go to this URL "/admin/config/services/ln-adimo/field-update"
- Choose adimo field from select list and associated content type to import id's.
- Upload csv file and sample csv file is uploaded under lightnest adimo module under migrations/example/example_by_nic.csv
- After uploading file and select field type submit it.
- All nid given in csv file will update the Adimo id's.



TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--dsu-gallery.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Gallery.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.
 * The module comes with a simple twig template called "adimo-button.html.twig"
 * You can copy this template in your theme and customize it.

Multlingual Widget
------------------
  * Adimo multilingual widget rendering.
  * We added one dynamic variable language for getting the current language code in all twigs files.
  * Any agency can override the twig and add same variable to get current language widget.
  * Please go through the twig that is in under template folder and get example of using language in onclick method.
  * Example -> onclick="window.Adimo.setLanguage('{{ language }}');"

MAINTAINERS
-----------

* Nestle Webcms team.
