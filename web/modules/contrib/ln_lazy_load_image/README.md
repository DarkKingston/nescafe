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

The Lightnest Lazyload Image module provides to add lazyload on image filed.

The Lightnest Lazyload Image module is used to increase the site performance.

REQUIREMENTS
------------

* No module require.

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select Countdown paragraph in reference.

* Go to that Node type page under crate a node form.

FUNCTIONALITY
-------------

* Add lazyload on images.

TROUBLESHOOTING
---------------

 * If the lazyloading is not working, check the following:

   - Does ln_lazy_load_image.module file is execute?

EXTEND
------

 * hook_preprocess_image and  hook_preprocess_responsive_image for extending default lazyloading of Lightnest lazyload image.

MAINTAINERS
-----------

* Nestle Webcms team.