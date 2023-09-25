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

DSU Gallery Slider can be used to create a slideshow of any paragraph content (not just images) that can use video also in slider.

Powered by jQuery, it is heavily customizable: you may choose slideshow settings for each slider you will create.

REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Video Embed Field (https://www.drupal.org/project/video_embed_field)
* Slick Carousel(https://www.drupal.org/project/slick)
* Image Widget Crop(https://www.drupal.org/project/image_widget_crop)
* Entity Browser(https://www.drupal.org/project/entity_browser)
* Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)

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

* A title can be added.

* User can choose title position Left, right, top and bottom.

* Main image and Thumbnail image can be uploaded.

* Video URLs can be added.

* User can select slider navigation format like dots, images and can be both.

* If Image and a URL are uploaded at the same item, only the video will be displayed.

* As many items as are needed can be added.

* Thumbnail image is a mandatory field.

* Image or Video URL must be added.

* Users can see a main image and thumbnail images underneath.

* When users click on a thumbnail, they can see it as the main image.

* Users can also play a video.

* Crop the image for responsive layout.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--dsu-gallery.html.twig template is execute?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Gallery.
 * hook_preprocess_paragraph for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.