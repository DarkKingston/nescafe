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

Lightnest Newsletter Component provide below options:

* webform "Newsletter Email Collection" (/admin/structure/wetbform/manage/newsletter_email_collecion)
* Newsletter Signup CTA Block component (/admin/structure/paragraphs_type/c_newsletter_signup_cta/fields)

REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)
* Link Attributes (https://www.drupal.org/project/link_attributes)
* Webform (https://www.drupal.org/project/webform)
* Clientside Validation (https://www.drupal.org/project/clientside_validation)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

* Configure the field inside the Struture » Content types » Any content type : Add entity reference or paragraph reference field and select paragraph in reference.

* Go to that Node type page under crate a node form.

* Add the Newsletter Signup CTA content.

* Configure the component selection inside the Struture » Block Layout » Block Component: edit field and select component which you want to add.

* Go to that Block Content » custom block page under Block section.

* Add custom block with Newsletter collection form / Newsletter Signup CTA and save.

FUNCTIONALITY
-------------

* A newsletter webform can be added in blocks.

* A Newsletter Signup CTA component can be added in blocks.

TROUBLESHOOTING
---------------

 * If the content does not display, check the following:

   - Does paragraph--c-newsletter-signup-cta.html.twig template is execute?
   - Does webform--newsletter-email-collection.html template is execute ?

EXTEND
------

 * hook_theme for extending default template of Lightnest paragraph Gallery.
 * hook_preprocess_paragraph for data processing.
 * hook_form_alter for override form processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
