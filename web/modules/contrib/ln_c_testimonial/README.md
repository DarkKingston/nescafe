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

Lightnest Testimonial Component can be used to create a view  of 
testimonials content.

REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Entity Browser(https://www.drupal.org/project/entity_browser)
* Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : 
  Add entity reference or paragraph reference field and select 
  Testimonial paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A heading can be added.

* A Sub heading can be added.

* Author name can be added.

* Author role can be added.

* Author Company/market can be added.

* Link can be added.

* Quote Text can be added.

* Author Image can be added.

* Crop the image for responsive layout.

* Testimonial style can be changed (default/featured)

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-testimonial.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Gallery.
 * hook_preprocess_paragraph for data processing.
 * hook_ln_sample_content for default content.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
