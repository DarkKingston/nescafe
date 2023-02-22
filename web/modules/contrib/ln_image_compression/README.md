CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Troubleshooting
 * Maintainers

INTRODUCTION
------------

This module all to use local binaries and optimize the image while uploading or rendering. ImageAPI Optimize allows you to use your preferred toolkit and optimize (losslessly) the image when it is saved. Practice for web performance suggests that images should be optimized for better loading time. With this module enabled, 
Google's Page Speed will always give you an A in image optimize.


REQUIREMENTS
------------

This module requires the following modules:

* Image Optimize (https://www.drupal.org/project/imageapi_optimize)
* Image Optimize Binaries (https://www.drupal.org/project/imageapi_optimize_binaries)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

* This is feature module and having default configuration of image_optmize module with lcoal binaries.
* Go to ImageAPI settings page (admin/settings/imageapi), select ImageAPI Optimize as the default toolkit instead of GD or Imagemagick.
* Go to ImageAPI Optimize settings page, select SmushIt if you haven't other tools compiled.

FUNCTIONALITY
-------------

* Uplaod an image and validate the size of the image with actual image. Image size will reduce for performance optimization.

  
TROUBLESHOOTING
---------------

 * Check once modules properly configure.

MAINTAINERS
-----------

* Nestle Webcms team.