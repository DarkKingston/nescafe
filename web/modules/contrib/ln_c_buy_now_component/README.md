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

The Lightnest buy now component is combinations of existing buy features adimo, price_spider and wunderman. User have capability to add any of the buy now component as a paragraph type.
User can select type of the buy now and another value will appear for filling the buy now values.

Adimo:
------------

Adimo’s goal is to make marketing useful by making it shoppable. Through Adimo integration,offer frictionless eCommerce solutions for brands, publishers and retailers.
This module creates a new field type that allows you to setup adimo where to buy button in any type of entity. It's llows us to guide shoppers to your products and follow them along every step of the purchasing funnel.
It’s one of the most sophisticated and effective marketing solution for product.

Price Spider:
------------
This feature creates a new field type that allows you to setup price spider where to buy button in any type of entity. It's llows us to guide shoppers to your products and follow them along every step of the purchasing funnel. 
It’s one of the most sophisticated and effective where-to-buy solutions anywhere.

Wunderman:
------------
This feature creates a new field type that allows you to setup Fusepump where to buy button in any type of entity. It's llows us to guide shoppers to your products and follow them along every step of the purchasing funnel. 
It’s one of the most sophisticated and effective where-to-buy solutions anywhere.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Entity Class Formatter (https://www.drupal.org/project/entity_class_formatter)
* Link target(https://www.drupal.org/project/link_target)
* Inline Entity Form(https://www.drupal.org/project/inline_entity_form)
* Video Embed Field(https://www.drupal.org/project/video_embed_field)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Structure » Content types » Any content type : Add entity reference or paragraph
 reference field and select card paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* Card style can be added.

* Card color can be added.

* Card item can be added.

* Use existing entity or new entity can be added.

* Place as many card items as it is necessary.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--ln-c-buy-now-component.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph component.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
