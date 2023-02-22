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

The Lightnest Link/document container component provides functionality  to add a dcument and a link with maximum customization.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Link target(https://www.drupal.org/project/link_target)
* Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Link/document container paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* Document and link component can added inside it.

  1. Document component:

	  * Document Name can be added.

	  * Document file can be upload.

	  * Document icon can be upload.

  2. Link component can add two type of link:
  
    - CTA link

      -  Link URL and text can be added.

      -  Link icon can be added.

    - CTA button:

      -  Link URL and text can be added.

      - CTA URL and text can be added.

      - CTA color can be added.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-link-document-container.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Link/document container.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.