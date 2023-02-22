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

The Lightnest slider component provides functionality to display a carousel with several slides. Also known as a hero slider.


REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Link target(https://www.drupal.org/project/link_target)
* Slick Carousel(https://www.drupal.org/project/slick)
* Twig Tweak(https://www.drupal.org/project/twig_tweak)
* Slick Paragraphs (https://www.drupal.org/project/slick_paragraphs)
* Entity Reference Revisions(https://www.drupal.org/project/entity_reference_revisions)
* Image Widget Crop (https://www.drupal.org/project/image_widget_crop)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select content slide paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* A slide title and description can be added.

* User can choose slide content position over center, over left,over right, above left, above right, above center, bellow left, bellow right and bellow center.

* Slice image can be uploaded.

* CTA URLs and text can be added.

* Place as many slides as it is necessary.

* One paragraph reference field (`Full Content`) is added to add components into slider.

* Now site users can add Externalvideo, card, Text + image, image, coutdown components into slider.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-slide.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Slider.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.

MAINTAINERS
-----------

* Nestle Webcms team.