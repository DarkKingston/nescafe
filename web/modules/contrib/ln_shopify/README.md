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

* The Lightnest Shopify component allows to do a simple integration with Drupal by using a JS Snippet that can be embedded to any page.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Shopify Cart Snippet paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* JS script can be added.

* User can choose text horizontal alignment left, right, and center.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-shopify.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Text.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.