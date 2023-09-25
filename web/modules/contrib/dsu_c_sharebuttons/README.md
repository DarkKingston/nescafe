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

The Lightnest Sharebuttons component provides functionality to share the content on social sites with maximum customization.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Social Media(https://www.drupal.org/project/social_media)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* GoogleTagManager (https://www.drupal.org/project/google_tag)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the settings **/admin/config/services/social-media**

   - If `target='_blank'` attribute exist then add `rel='noopener noreferrer'` as well.
   - By default for all href add  `rel='noreferrer'`.

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Link/document container paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* Select multiple options fro socail sharing.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-share-buttons.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Sharebuttons.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.