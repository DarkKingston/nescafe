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

The Lightnest Accordion can be used to create a vertically stacked list of items of any paragraph content.

Each item can be "expanded" or "collapsed" to reveal the content associated with that item.

REQUIREMENTS
------------

This module requires the following modules:

 * Paragraphs (https://www.drupal.org/project/paragraphs)
 * Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)

INSTALLATION
------------

*  Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Accordion paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A title can be added.

*  Teaser text can be added.

* Full content text can be added. Full content is entity reference with paragraph.

* As many items as are needed can be added.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--accordion.html.twig and paragraph--accordion-item.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Accordion.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.