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

This module help us to add social buttons components.

The Lightnest social buttons module is used to add social buttons in website.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Link Attributes widget(https://www.drupal.org/project/link_attributes)
* Link(https://www.drupal.org/project/link)
* Entity Browser(https://www.drupal.org/project/entity_browser)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Text + Image paragraph in reference.

* Go to that Node type page under crate a node form.

* Add social plateform details Struture » taxonomy » Social Platforms » List Items » Add terms


FUNCTIONALITY
-------------

* A title can be added.

* User can choose Social Link Platform Facebook, Instagram, and Pinterest.

* Multiple CTA URLs and text can be added for single social plateform.

* Place as many Social buttons items as it is necessary.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-socialbuttons.html.twig template is execute?

EXTEND
------

 * Hook_theme for extending default template of Lightnest paragraph Social buttons.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.