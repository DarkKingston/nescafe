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

The Lightnest External Video module provides a field to display the video on website.

REQUIREMENTS
------------

This module requires the following modules:

 * Paragraphs (https://www.drupal.org/project/paragraphs)
 *  Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)
 * Video Embed Field (https://www.drupal.org/project/video_embed_field)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select External Video paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A Video URL can be added.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-externalvideo.html.twig template is execute?
   
 * If the thumbnail does not display, check admin/structure/paragraphs_type/c_externalvideo/display:

   - Edit C Video URL settings select Alternative thumbnail option in Image
   thumb field, update the settings and save.    

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph External Video.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.