CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * Troubleshooting
 * Extend
 * Maintainers

INTRODUCTION
------------


This Lightnest hreflang module specify the proper hreflang code using the format language-location (e.g., es-es, es-mx).

Create hreflang for SEO purpose in single language site.


REQUIREMENTS
------------

* No contributed module require.

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configutaion URLs **/admin/config/lightnest/ln-hreflang/hreflang**.

FUNCTIONALITY
-------------

* Add hreflang with custom hreflang code for seo.

TROUBLESHOOTING
---------------

 * If the hreflang does not override in page view source, check the following:

   - Check ln_hreflang_page_attachments_alter hook in ln_hreflang.module file.

EXTEND
------

 * ln_hreflang_page_attachments_alter for Override default hreflang.

MAINTAINERS
-----------

* Nestle Webcms team.