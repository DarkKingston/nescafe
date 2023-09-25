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

This module help us to add space between two or more components.

The Lightnest Spacer module is used to add space between components


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Go to any content type and add Spacer paragraph under entity reference or paragraph reference.

* Go to that Node type page under crate a node form.


FUNCTIONALITY
-------------

* User can choose Type of divider line and line full width.

* Divider height cab be added.

* Margin top can be added.

* Margin bottom can be added.


TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-spacer.html.twig template is execute?


EXTEND
------

 * Hook_theme for extending default template of Lightnest paragraph Spacer.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.
 
MAINTAINERS
-----------

* Nestle Webcms team.