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

This module help us to integrate Drupal Ratings & Reviews for product type in each node.

The Lightnest Ratings & Reviews module is a suite of modules that provide a wide range of integrations with the drupal
ratings and reviews service.

REQUIREMENTS
------------

This module requires the following modules:

* Fivestar (http://drupal.org/project/fivestar)
* GoogleTagManager (https://www.drupal.org/project/google_tag)
* Advanced DataLayer (https://www.drupal.org/project/advanced_datalayer)
* Flag (https://www.drupal.org/project/flag)
* Colorbox (https://www.drupal.org/project/colorbox)
* ReCaptcha (https://www.drupal.org/project/recaptcha)

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.
* Colorbox needs to be installed in libraries/ folder in the drupal project, otherwise, image popups will not appear.

UPGRADE PATH
------------

If you already have version 1 installed, please proceed carefully with these steps.
* Do a database backup.
* Run “composer update”
* Drush cr and rebuild the cache
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Ratings & Reviews.
* Make sure to import all missing files. We have added extra configs so please import missing configs.
* After importing all missing configs. Go to change states of the module and make sure all components are in a default state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Follow the module documentation steps:
  * Add a comment field to a content type, using comment type "DSU Ratings and Reviews comment type".
    If you had one already, skip this step. If you had a different storage solution other than a Comment field,
    you would need to migrate those comments to this new field.
  * Change that field display mode to "DSU Ratings & Review Comment list"
  * Revert/delete any twig template changes that were required for version 1. These won't be needed anymore since
    comments will be shown as a field.
  * Go to Admin -> Config -> Lightnest -> Lightnest Ratings & Reviews configuration and customize texts as required,
    especially "Terms and conditions" field.
  * Manually add permission "Access comments" and "Post comments" for anonymous users if you need the feature.
* Test that everything is working, especially other existing comment types if your site has them.

CONFIGURATION
-------------

* Once enabled the module go to "DSU Product" content type (or any content type of your choice) and go to manage fields.
* Make sure a "Comment" type field exists and that it accepts comment type: "DSU Ratings and Reviews comment type".
* That field should also have comments as "Open" or comments will not shown.
* Go to manage display and replace the comment field default display "Comment" for  "DSU Ratings and Reviews Comment
 list" widget.
* Check that this module installed the correct permissions for authored users to "View comments" and
 "Post comments". If not, proceed to enable those. Permissions to "Post comments" are optional and not
 automatically set by this module. If you want this feature, add that permission manually.
* Access to one content type of the type you selected, and you should see a "Write a review" form below.
* With it, users can add comments, and only the moderator user will be able to reply to them with a single reply.
* Now please proceed to customize the configuration of this module on url:
 /admin/config/lightnest/dsu_ratings_reviews/settings or by accessing through "Lightnest" menu.
* In this url, you should configure the "Terms and Conditions" to show to the user before commenting,
 as well as the text that will be sent as mail to moderators once a new review needs moderation.
* To remove anonymous user link from image, go to `admin/config/people/accounts/display/compact` and change format to `Remove Anonymous Link from Image`.
* To remove cache from anonymous username, go to `admin/config/people/accounts/display/compact` and change format to `User name without cache`.
* To remove `not verified` from anonymous username, go to `Appearance` » `theme Settings` » uncheck `User verification status in comments` under `Page Element`.

FUNCTIONALITY
-------------

* Ratings & Reviews
* Event Tracking From GTM
* Showing and filtering Product ratings.
* Showing user reviews for product.
* Dashboard to calculate KPIs and metrics about all reviews.
* Adds Moderator role that is the only one to add a single reply per comment.
* Image attachments for reviews, being visible in a popup.
* Flags for end users to vote how useful comments were for them.

TROUBLESHOOTING
---------------

* If you don't see any filters or the "Write a review" text, make sure
 you selected the right widget on the installation steps to
 replace "Comment" field widget.
* If comments do not show for other users, go to People > Permissions and enable
 'View comments'. You may also want to configure permissions
 'Post comments' and 'Edit own comment' for common users.
* If brand user cannot reply to comments, go to People > Permissions
 and enable 'reply rating comments' permission for brand roles.
* If the colorbox popup doesn't show up when clicking on images, make sure "colorbox"
 library is installed in the /libraries/ folder, and clean caches.

EXTEND
------

If you need to use this module as multilingual:
 * Enable the usual multilingual/language/content translation modules and a new language for your site,
 if you haven't yet: "locale", "language", "content_translation", and "config_translation".
  * Edit the "DSU Ratings and Reviews Comment type" and open "Language Settings" tab.
   * Use a valid "Default language" for your users, likely UI language or author prefered language.
   * Optionally, with checkbox "Show language selector" users could also write in a different language
     than they are browsing.
 * Go to config translation: /admin/config/lightnest/dsu_ratings_reviews/settings/translate
   and translate Terms and conditions.

If you want to add styles to the module, either:
 * Customize the template by adding suggestions or overriding it with one of your own.
 * Use selectors: ".dsu-ratings-reviews-comment" or "#comment-dsu-ratings-reviews-comment-type-form"
 in order to modify appearance of comments on their display and form modes respectively.

If you need to extend code and configuration customizations for this module you may either:
 * Create a custom module whose weight is greater than this one and customize using similar hooks that this module uses
 * Export or reuse its configuration in a child module or a cmi folder
 * Alter adapter implementations with an inheriting class.
 * Extend or alter current templates used by the module.
 * Replace or alter the comment view created by this module.

MAINTAINERS
-----------

* Nestle Webcms team.
