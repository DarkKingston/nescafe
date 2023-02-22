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

The Lightnest View Builder component provides functionality to refer any views as a component anywhere in the page.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Link target(https://www.drupal.org/project/link_target)
* Field Group(https://www.drupal.org/project/field_group)
* Entity Browser(https://www.drupal.org/project/entity_browser)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph
 reference field and select DSU View builder paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A title, subtitle and description can be added.

* User can choose any views from existing views.

* User can choose footer,header and CTA options additionally.

* Choose any block type and page of the views as a component.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-view.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph views builder.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
