CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Functionality
* Maintainers

INTRODUCTION
------------

This module allows us to embed media entities into CKEDITOR.
Available five field formatters are:

* Lightnest Media Thumbnail
* Lightnest Video Thumbnail
* Lightnest Media Modal
* Lightnest Media Lazyload
* Lightnest Oembed Content 
 ( Integration of Lightness Media Modal and Lazyload. )


REQUIREMENTS
------------

This module requires the following modules:

* Colorbox ( https://www.drupal.org/project/colorbox )


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Go to `Text formats and editors` ( admin >> config >> Text formats and editors ) setting.
* Edit `text format` where you want to place the media library button.
* Enable media library button.
* Check `Embed media` under a filter.
* Then under Embed media filter setting: Check `External Video` media entity for,
  `Media types selectable in the Media Library`.
* Rest all configuration will be the default.


FUNCTIONALITY
-------------

* A Oembed source media video can be embedded in CKEDITOR enabled field.
* Option for alternate thumbnail for the same video.
* Option to show video on Colorbox modal.


MAINTAINERS
-----------

* Nestle Webcms team.
