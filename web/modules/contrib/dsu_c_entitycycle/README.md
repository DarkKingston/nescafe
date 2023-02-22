CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Troubleshooting
 * Extend
 * Maintainers

INTRODUCTION
------------

The Lightnest entitycycle component provides functionality to create entity slider with maximum customization.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Options Table (https://www.drupal.org/project/options_table)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* Slick Entity Reference(https://www.drupal.org/project/slick_entityreference)
* Slick Carousel (https://www.drupal.org/project/slick)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Entity Cycle paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A reference entity can be added.

* Display option can be selected for UI/UX.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-entitycycle.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Entity Cycle.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.

MAINTAINERS
-----------

* Nestle Webcms team.