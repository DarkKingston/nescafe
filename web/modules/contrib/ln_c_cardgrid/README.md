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

The Lightnest Grid card component provides functionality  to create different type of grid card layouts with maximum
 customization.


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

   - Does paragraph--ln-c-cardgrid.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Card.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
