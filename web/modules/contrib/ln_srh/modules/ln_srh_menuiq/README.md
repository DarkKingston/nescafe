CONTENTS OF THIS FILE
---------------------

   * Introduction
   * Requirements
   * Installation
   * Configuration
   * Functionality
   * Maintainers

INTRODUCTION
------------

Creates an overlay menu with information about the recipe.

REQUIREMENTS
------------

This module requires the following modules:

   * Lightnest SRH Connector (ln_srh)
   * Lightnest components: Core (dsu_c_core)
   * Paragraphs (https://www.drupal.org/project/paragraphs)
   * Media (media)
   * Media Library (media_library)
   * Media Image (lightning_media_image)
   * Media Video (lightning_media_video)
   * Display Suite (https://www.drupal.org/project/ds)
   * Slick Carousel (https://www.drupal.org/project/slick)

INSTALLATION
------------

   * Install as you would normally install a contributed Drupal module. Visit https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

   * Go to admin/config/lightnest/srh/mymenuiq and press save directly if you don't want to change the default values.
   * Go to admin/structure/types/manage/srh_recipe/display and place "SRH MenuIQScore" field in the content region setting with formatter "SRH My MenuIQ"
   * Clear all caches.

FUNCTIONALITY
-------------

   * Creates an overlay menu to show information about the current recipe.
   * You can combine different dishes with the main recipe and get their score.

MAINTAINERS
-----------

   * Nestle Webcms team.
