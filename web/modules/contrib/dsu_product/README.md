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

This module help us to create basic layout of PDP page with all integrated and created component.

Default layout of product detail page after installing or enabling DSU product. We can extend it and create more field
based on seperate site requirements.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Advanced DataLayer (https://www.drupal.org/project/advanced_datalayer)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* Entity Browser(https://www.drupal.org/project/entity_browser)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* No configuration required.

FUNCTIONALITY
-------------

* A product title, GTIN and description can be added.

* A product image can be uploaded.

* Slider alignment can be added.

* Showing Product ratings with aggregate start ratings.

* Showing Bazaarvoice R&R.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does node--dsu-product.html template is execute?

EXTEND
------

 * hook_theme for extending default template of product content type.
 * Override default css libraries to change default UI/UX.

MAINTAINERS
-----------

* Nestle Webcms team.