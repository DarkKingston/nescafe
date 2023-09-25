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

The Lightnest teaser cycle component provides functionality to create slider component with maximum customization.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Slick Paragraphs (https://www.drupal.org/project/slick_paragraphs)
* Slick Carousel (https://www.drupal.org/project/slick)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* Entity Browser(https://www.drupal.org/project/entity_browser)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Teaser Cycle paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A title can be added.

* A description can be added

* Image can be uploaded.

* CTA URLs and text can be added.

* Place as many teaser cycle items as it is necessary.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-teasercycle.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Teaser Cycle.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.

MAINTAINERS
-----------

* Nestle Webcms team.