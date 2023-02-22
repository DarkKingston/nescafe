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

The Lightnest Text + Image component provides functionality to create text with image component with maximum customization.


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

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Text + Image paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A title, subtitle and description can be added.

* User can choose text horizontal alignment left, right, and center.

* User can choose text vertical alignment top, middle and bottom.

* Text background image can uploaded.

* Classy Paragraph Style can be added.

* Image can be uploaded.

* User can choose image position left and right.

* CTA URLs and text can be added.

* CTA Colour can be added.

* User can choose CTA horizontal postion left and right.

* User can choose CTA vertical postion above and below.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-sideimagetext.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Text + Image.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.

MAINTAINERS
-----------

* Nestle Webcms team.
