
# <a name="top"> </a>CONTENTS OF THIS FILE

 * [Introduction](#introduction)
 * [Requirements](#requirements)
 * [Recommended modules](#recommended-modules)
 * [Features](#features)
 * [Installation](#installation)
 * [Uninstallation](#uninstallation)
 * [Configuration](#configuration)
 * [Slick Formatters](#formatters)
 * [Troubleshooting](#troubleshooting)
 * [FAQ](#faq)
 * [Contribution](#contribution)
 * [Maintainers](#maintainers)

***
## <a name="introduction"></a>INTRODUCTION

Visit **/admin/help/slick_ui** once Slick UI installed to read this in comfort.

Slick is a powerful and performant slideshow/carousel solution leveraging Ken
Wheeler's [Slick Carousel](http://kenwheeler.github.io/slick).

Slick has gazillion options, please start with the very basic working
samples from [Slick Example](https://drupal.org/project/slick_extras) only if
trouble to build slicks. Spending 5 minutes or so will save you hours in
building more complex slideshows.

The module supports Slick 1.6 above until 1.8.1. Versions 1.9.0 and above are
not currently supported. Slick 2.x is just out 9/21/15, and hasn't been
officially supported now, Jan 2020.


***
## <a name="requirements"> </a>REQUIREMENTS
1. Slick library:

   **Standard version**

   * Download Slick archive **>= 1.6 && <= 1.8.0** from
     [Slick releases](https://github.com/kenwheeler/slick/releases)
   * Master branch (1.9.0 but in code as 1.8.1) is not supported, and had been
     removed from official repo 2019. Instead download, etract and rename one of
     the official slick releases to `slick`, so the assets are at:
     + **/libraries/slick/slick/slick.css**
     + **/libraries/slick/slick/slick-theme.css** (optional)
     + **/libraries/slick/slick/slick.min.js**
     + Or any path supported by core library finder as per Drupal 8.9+. If using
       composer, the library directory is `slick-carousel`. They are fine.
       Slick v1.6.0 is the only battle-tested, and less issues, only lacking
       features such as extra lazy-load, etc.

   **Accessible version**

   * Download the Accessible Slick archive **>= 1.0.1** from
     [Accessible Slick releases](https://github.com/Accessible360/accessible-slick/releases)
   * Extract and rename the folder to `accessible-slick`, so the
     assets are at:
     + **/libraries/accessible-slick/slick/slick.css**
     + **/libraries/accessible-slick/slick/slick-theme.css** (optional)
     + **/libraries/accessible-slick/slick/slick.min.js**
     + Or any path supported by core library finder as per Drupal 8.9+.

2. [Download jqeasing](https://github.com/gdsmith/jquery.easing), so available:

   **/libraries/easing/jquery.easing.min.js**

   This is CSS easing fallback for non-supporting browsers.

3. [Blazy](https://drupal.org/project/blazy) 2.10+ since Slick:2.7  
   To reduce DRY stuffs, and as a bonus, advanced lazyloading such as delay lazyloading for below-fold sliders, iframe, (fullscreen) CSS background lazyloading, breakpoint dependent multi-serving images, lazyload ahead for smoother UX. Check out Blazy installation guides!


***
## <a name="installation"> </a>INSTALLATION
Be sure to read the entire docs and form descriptions before working with
Slick to avoid headaches for just ~15-minute read.

1. **MANUAL:**

   Install the module as usual, more info can be found on:

   [Installing Drupal 8 Modules](https://drupal.org/node/1897420)

2. **COMPOSER:**

   ```
   $ composer require npm-asset/blazy \
   npm-asset/slick-carousel:1.8.0 \
   npm-asset/jquery-mousewheel \
   npm-asset/jquery.easing \
   drupal/blazy \
   drupal/slick
   ```
   See [Blazy composer](/admin/help/blazy_ui#composer) for details.

***
## <a name="uninstallation"> </a>UNINSTALLATION
Please check out below for solutions:  

* [Slick 7.x](https://www.drupal.org/project/slick/issues/3261726#comment-14406766)
* [Slick D8+](https://www.drupal.org/project/slick/issues/3257390)


***
## <a name="configuration"> </a>CONFIGURATION
Visit the following to configure Slick:

1. `/admin/config/media/slick`

   Enable Slick UI sub-module first, otherwise regular **Access denied**.

2. Visit any entity types:

  + `/admin/structure/types`
  + `/admin/structure/block/block-content/types`
  + `/admin/structure/paragraphs_type`
  + etc.

   Use Slick as a formatter under **Manage display** for multi-value fields:
   Image, Media, Paragraphs, Entity reference, or even Text.
   Check out [SLICK FORMATTERS](#formatters) section for details.

3. `/admin/structure/views`

   Use Slick as standalone blocks, or pages.


***
## <a name="recommended-modules"> </a>RECOMMENDED MODULES
Slick supports enhancements and more complex layouts.

### OPTIONAL
* [Media](https://drupal.org/project/media), to have richer contents: image,
  video, or a mix of em. Included in core since D8.6+.
* [Colorbox](https://drupal.org/project/colorbox), to have grids/slides that
   open up image/ video in overlay.
* [Photobox](https://drupal.org/project/photobox), idem ditto.
* [Picture](https://drupal.org/project/picture) for more robust responsive
  image. Included in core as Responsive Image since D8.
* [Paragraphs](https://drupal.org/project/paragraphs), to get more complex
  slides at field level.
* [Field Collection](https://drupal.org/project/field_collection), idem ditto.
* [Mousewheel](https://github.com/brandonaaron/jquery-mousewheel) at:
  + **/libraries/mousewheel/jquery.mousewheel.min.js**


### SUB-MODULES
The Slick module has several sub-modules:
* Slick UI, included, to manage optionsets, can be uninstalled at production.

* Slick Media, included as a plugin since Slick 2.x.

* [Slick Views](https://drupal.org/project/slick_views)
  to get the most complex slides you can imagine.

* [Slick Paragraphs](https://drupal.org/project/slick_paragraphs)
  to get more complex slides at field level.

* [Slick Lightbox](https://drupal.org/project/slick_lightbox)
  to get Slick within lightbox for modern features: responsive, swipes, etc.

* [Slick Entityreference](https://drupal.org/project/slick_entityreference)
  to get Slick for entityreference and entityreference revisions.

* [ElevateZoom Plus](https://drupal.org/project/elevatezoomplus)
  to get ElevateZoom Plus with Slick Carousel and lightboxes, commerce ready.

* [Slick Example](https://drupal.org/project/slick_extras)
  to get up and running Slick quickly.

***
## <a name="features"></a>FEATURES
* Fully responsive. Scales with its container.
* Uses CSS3 when available. Fully functional when not.
* Swipe enabled. Or disabled, if you prefer.
* Desktop mouse dragging.
* Fully accessible with arrow key navigation.
* Built-in lazyLoad, and multiple breakpoint options.
* Random, autoplay, pagers, arrows, dots/text/tabs/thumbnail pagers etc...
* Supports pure text, responsive image, iframe, video carousels with
  aspect ratio. No extra jQuery plugin FitVids is required. Just CSS.
* Works with Views, core and contrib fields: Image, Media Entity.
* Optional and modular skins, e.g.: Carousel, Classic, Fullscreen, Fullwidth,
  Split, Grid or a multi row carousel.
* Various slide layouts are built with pure CSS goodness.
* Nested sliders/overlays, or multiple slicks within a single Slick via Views.
* Some useful hooks and drupal_alters for advanced works.
* Modular integration with various contribs to build carousels with multimedia
  lightboxes or inline multimedia.
* Media switcher: Image linked to content, Image to iframe, Image to colorbox,
  Image to photobox.
* Cacheability + lazyload = light + fast.
