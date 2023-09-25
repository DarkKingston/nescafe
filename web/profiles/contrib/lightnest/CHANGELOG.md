## 4.11.0

In this release,
1. Deprecate ln_metatag module
2. Fix lightning_core warning during installation
3. Add patch to recaptcha module for ajax support
4. Add dropzone library
5. Add new module ln_c_entity_compare
6. Update all dependencies

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps first on non-live environment.

* Run “composer update”
* Drush cr and rebuild the cache
* Run yourdomain.com/update.php or “drush updb”
* Test that everything is working.

## 4.10.0

In this release,
1. Add accessible-slick library
2. Remove hardcoded credentials in CHANGELOG
3. Add akamai module
4. Update all dependencies
5. Add ln_notification to lightnest_full

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps first on non-live environment.

* Run “composer update”
* Drush cr and rebuild the cache
* Run yourdomain.com/update.php or “drush updb”
* Test that everything is working.

## 4.9.0

In this release,
1. Remove paragraphs_ee dependency
2. Remove duplicate dropzone libraries
3. Fix warnings for PHP 8
4. Remove deprecated modules
5. Add new modules in composer.json
6. Add new submodule lightnest_full
7. Update patch for lightning_media

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps first on non-live environment.

* Run “composer update”
* Drush cr and rebuild the cache
* Run yourdomain.com/update.php or “drush updb”
* Test that everything is working.


## 4.8.0

In this release,
1. Prepare for Bootstrap 5
2. Remove deprecated Card component
3. Fix installation warnings
4. Remove satis credentials

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps first on non-live environment.

* Run “composer update”
* Drush cr and rebuild the cache
* Run yourdomain.com/update.php or “drush updb”
* Test that everything is working.


## 4.7.0 - 28/02/2022

In this release,
1. svg_image module is added.
2. Applied patch to seckit module which fix div tag appearing in header.
3. Applied patch to ligtning media module which fix SVG image upload issue.
4. Applied patch to remove google plus icon from admin settings.
5. Readme updated.

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Run “composer update”
* Drush cr and rebuild the cache
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Components & Lightnest Components.
* Make sure each list of modules must import missing files. We have added extra configs so please import missing configs.
* After import all missing configs. Go to change states of each module and * make sure all components must in a default state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.

## 4.6.0 - 16/11/2021

In this release, Robots txt, LN SEO hreflang modules are added and some other components based fixes.

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Run “composer update”
* Drush cr and rebuild the cache
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Components & Lightnest Components.
* Make sure each list of modules must import missing files. We have added extra configs so please import missing configs.
* After import all missing configs. Go to change states of each module and * make sure all components must in a default state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.

## 4.5.1 - 27/10/2021

Remove bootstrap library patch as it is already added in latest version.

## 4.5.0 - 15/09/2021

In this release, some major enhancements including:

Highlights of this release include new Profile Installation wizrad, component like ln_qualifio, ln_c_testimonial, ln_c_newsletter, ln_c_video_embed, component enhancements and bug fixes.

Profile Installation wizard allows site owners to install additional components and integrations with lightest installation and it will generate a report of components installation.

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Run “composer update”
* Drush cr and rebuild the cache
* Enable features and features_ui module if not enabled.
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Components & Lightnest Components.
* Make sure each list of modules must import missing files. We have added extra configs so please import missing
  configs.
* After import all missing configs. Go to change states of each module and make sure all components must in a default
  state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.

## 4.4.1 - 02/07/2021

Hotfix to remove dsu_srh requirement from the profile as this module is as an standalone component that should be required directly in the project composer.

## 4.4.0 - 07/06/2021

In this release, some major enhancements include:

For LightNest Drupal 9 sites, highlights of this release include adimo mass uploader changes, each component can choose H1 - H6
title tag, Paragraph previewer options added, component bug fixes, dsu_c_extend component and updated dsu_multitheme with d9 library
changes.

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Run “composer update”
* Drush cr and rebuild the cache
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Components & Lightnest Components.
* Make sure each list of modules must import missing files. We have added extra configs so please import missing
  configs.
* After import all missing configs. Go to change states of each module and make sure all components must in a default
  state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.


## Quick Start Guide For D9 with Lightnest Setup in Few Steps

### Drupal 9 (Lightnest 4.x):

* composer create-project --no-install drupal/recommended-project lightnest
* cd lightnest
* composer.json replace "minimum-stability": "dev" with "minimum-stability": "stable"
* Make sure you set "enable-patching": true in the Root Composer.json file. Check in extra key or other composer file
  example.
* composer config repositories.drupal composer https://packages.drupal.org/8
* composer config repositories.lightnest composer https://satis.lightnest.nestle.com/
* composer config repositories.asset-packagist composer https://asset-packagist.org
* composer clear-cache
* composer require lightnest/lightnest:^4

## 4.3.3 - 15/04/2021

In this release, some major enhancements include:

For LightNest Drupal 9 sites, highlights of this release include a new PowerReviews connector, a new User
Recertification Admin View, enhanced Buy Now Adimo mass importer parameters and multiple touchpoint IDs, new brand
filter option for Alkemics, inline editing of blocks and frontend components, new datalayer events for the Engage
Contact Us form, new FAQs schema, improved UX accessibility for frontend components, improved LightNest package
installation and more.

Mandatory Installation Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Run “composer update”
* Drush cr and rebuild the cache
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Components & Lightnest Components.
* Make sure each list of modules must import missing files. We have added extra configs so please import missing
  configs.
* After import all missing configs. Go to change states of each module and make sure all components must in a default
  state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.

## 3.13.0 - 22/01/2021

In this release, some of the major enhancements include:

* Two new components have been added: Card Grid and Buy Now Component for landing pages
* The Social Buttons component is now available in a footer block
* Enhancements and dependency updates to all components
* Editorial enhancements include anchor linking, Linkit module, File Browser module, WYSIWYG document uploads
* Shopify cart experience integration extended to Product Page
* Smart Recipe Hub updates include a new tag structure, significant enhancements to MyMenuIQ, and ingredient quantities
  added to each recipe step
* Asset expiration workflow added to Content Hub
* New Product Data Hub integration for importing products
* SEO enhancements include improved load more scrolling, article schema, no-follow options for links
* Custom datalayer events added to Engage Contact Us form
* Options Table and Login Security modules updated to Drupal 9 compatible versions

## 4.2.0 - 18/01/2021

In this release, some of the major enhancements include:

* Two new components have been added: Card Grid and Buy Now Component for landing pages
* The Social Buttons component is now available in a footer block
* Enhancements and dependency updates to all components
* Editorial enhancements include anchor linking, Linkit module, File Browser module, WYSIWYG document uploads
* Shopify cart experience integration extended to Product Page
* Smart Recipe Hub updates include a new tag structure, significant enhancements to MyMenuIQ, and ingredient quantities
  added to each recipe step
* Asset expiration workflow added to Content Hub
* New Product Data Hub integration for importing products
* SEO enhancements include improved load more scrolling, article schema, no-follow options for links
* Custom datalayer events added to Engage Contact Us form
* Options Table and Login Security modules updated to Drupal 9 compatible versions

Mandatory Install Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Run “composer update”
* Drush cr and rebuild the cache
* Go to Admin -> config -> Development -> Features Page (admin/config/development/features)
* Select the bundle DSU Components & Lightnest Components.
* Make sure each list of modules must import missing files. We have added extra configs so please import missing
  configs.
* After import all missing configs. Go to change states of each module and make sure all components must in a default
  state.
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.

## 4.1.0 - 11/11/2020

In this release, Some of the major enhancements include:

* Two new components have been added: Shopify Cart and ViewBuilder. New gradient and text color styles have been added
  to the Image and Gallery components. The Teaser Card/Slider component has been deprecated.
* A video browser was added to the ContentHub connector. More search filtering options on the configuration and
  editorial interfaces also have been added.
* Adimo Buy Now updates include a multi-touchpoint integration, better multilingual handling for the end user, and a
  cookie consent passthrough from Evidon to Adimo on user preferences.
* Editorial enhancements include a new autosave function, a new Drupal Content Planner, and the creation of a custom
* Node ID Exporter.
* Added a user deletion propagation in the CIAM module.

Mandatory Install Steps Please follow the LightNest guidelines

The agency must perform these steps ON THEIR LOCAL ENVIRONMENT and not on the server!

* Disable Teaser Card/Slider component if it is installed.
* Search API Solr was removed from Lightnest. If required, add the module to the project root composer.json file
  according to the Drupal compatible version.
* Run “composer update”
* Run yourdomain.com/update.php or “drush updb”
* Clear all cache or “drush cr”
* Test that everything is working.

## 4.0.0 - 15/09/2020

This is a MAJOR release for LightNest, which aligns with the recent Drupal 9 release. Major changes include the removal
of the Lightning Base Profile, an upgrade to Bootstrap 4 and addition to the Barrio theme. The preferred XML Sitemap
module was changed to Simple XML Sitemap. A newLightNest CkEditor profile with enhanced functionality has been added by
default. Added a Sample Component Content generator to make it easy to style and test components.

Before upgrading to LightNest 4.0, it is crucial to review all the components and functionality in use on your
particular site and make them compatible with Drupal 9.

Major Code Changes:

* The latest release of all contributed modules on the LightNest dependent.
* Search API and Solr updated version added.
* Removed base lightning profile dependency in LightNest.
* Geolocation module updated with schema updates.
* D9 dependency added on all modules and components.
* Video embed module upgrade and thumbnails options is not supported with D9 release.
* SRH module MyMenuIQ is created; added D9 dependency also compatible with D8.
* BLT/Drush and Pantheon dependency added with updated version.
* LightNest Editor profile for more CKeditor options created.
* Lazy Load images module added.

Major and Platform level changes:

* Drupal core: 9.0.5
* BLT: 12
* Drush: 11
* LightNest: D9 Compatibility
* Platform changes: Mysql & PHP 7.3
* Factory hooks changes: No updates

Guidelines: https://lightnest.nestle.com/blog/how-update-latest-drupal-version

## 3.11.0 - 11/09/2020

This is a minor release that aligns with Drupal 8.9.1. All components and functionality are compatible with Drupal 9. To
move to LightNest 4.0/Drupal 9.0, sites must update to this version first.

Major Code Changes:

* The latest release of all contributed modules on the lightest dependent.
* Remove not require or unstable module from core lightnest dependency.
* Update Lightning Media, layout and core module.
* Remove dependency of link_target module.
* Added CKeditor plugins for more options in editor.
* Upgrade kint libraries for D9 compatible.
* Remove lightning dependency and will remove lightning from composer in Lightnest 4.0.0 release.
* Bootstarap theme updates with remove old library dependency.
* Update all info files for D9 compatiblility.

Major and Platform level changes:

* Drupal core: 8.9.5
* BLT: 12
* Drush: 11
* LightNest: D9 Compatibility
* Platform changes: No updates
* Factory hooks changes: No updates

*********** Mandatory steps: **************

Please follow the LightNest guidelines

The agency must perform those steps on their local environment and not on the server! They should always do the
necessary backups before processing.

1. Delete /composer.lock file and /vendor folder
2. Run “composer update”
3. Run yourdomain.com/update.php or “drush updb”
4. Clear all cache or “drush cr”
5. Test that everything is fine

## 3.10.0 - 09/07/2020

* Added focal point control, improved image and document uploading, and added scheduling and workflow modules.
* Enabled content editors to easily add forms to landing pages.
* Made Tint and Video Embeds GDPR compliant.
* Improved site performance with the Advanced CSS/JS aggregation module.
* Bulk uploader added to PriceSpider and Wunderman connectors.
* Continued added Bootstrap IV classes to components and content types.
* Updated Analytics Datalayer Framework.
* Created a custom HrefLang module to handle single and multilingual sites localization tagging for SEO and Engage
  Contact US form purposes.
* Added SEO Structured Data Framework and Google Console Verification ID field.
* Added loading icon and load more pagination in Content Hub library.
* Enhanced synchronization handling of Smart Recipe Hub (SRH) and added Menu IQ Score Widget
* Improved code efficiency for Alkemics and added Alkemics API to extend customizations.
* Made the prefix field translatable and increased the description field max length of Engage Contact Us connector.
* Bugfixes to various components released in 3.1.0.

## Major Code Changes:

* The latest release of all contributed modules on the lightest dependent.
* Mass id's uploader for Price spider and Wunderman Commerce.
* SRH with MenuIQ integration.

## Major and Platform level changes:

* Drupal core: 8.9.1
* BLT: 12
* Drush: 11
* LightNest: D9 Compatibility
* Platform changes: No updates
* Factory hooks changes: No updates

## 21/05/2020

* Add and install default Bootstrap Barrio theme in Lightnest and How to load Bootstrap Libraries in Bootstarp Barrio
  theme.
* Bootstrap Libraries is automatically downloaded via composer and copied into vendors folder.
* To copy dist files into libraries folder during installation, you can add the following 2 lines of script into your
  main/root composer.json file.

````
"scripts": {
	"post-install-cmd": [
		"@composer drupal-scaffold",
+		"cp -R vendor/twbs/bootstrap/dist docroot/libraries/bootstrap"
	],
	"post-update-cmd": [
		"@composer drupal-scaffold",
+       "cp -R vendor/twbs/bootstrap/dist web/libraries/bootstrap"
	],
````

* Go to the **/admin/appearance/settings/bootstrap_barrio** and select **Local minimized (production)** option in Load
  Library field.
* For more details
  visit: https://www.drupal.org/docs/8/themes/barrio-bootstrap-4-drupal-89-theme/bootstrap-libraries-load

## 3.2.0 - 16/05/2018

* Lightnest dependencies have been locked so only hotfixes are automatically updated.
* Security module:
    * Implementation to ensure inconsistent feedback is not given on e-mail validation.
    * New submodule: dsu_security_admin_module, blocks user login access on main domain for sites that are not meant to
      have a public login.
* Added a pre-package-update script to composer. This script will ensure new version of Lightnest are retrofitted
  seamlessly.
* Bugfixes to various components released in 3.1.0.

## 3.1.0 - 01/03/2018

* Made Ghostery/Evidon module multilingual.
* Moved all the modules from the custom folder to the contrib one.
    * FIX: If you get an error after updating, follow this method: https://www.drupal.org/node/2153725
* Components:
    * New component: dsu_c_entitycycle, allows to create a cycle between different user-selected entities.
    * New component: dsu_c_teasercycle, similar to the entity cycle, this one allows to create teasers and cycle through
      them.
    * New component: dsu_c_externalvideo, allows to embed a video into a component.
    * New component: dsu_c_gallery, allows to create a gallery with thumbnails and videos.
    * New component: dsu_c_link_document_container, allows to create a list with links and documents.
    * New component: dsu_c_sharebuttons, allows to create a bar with share buttons.
    * New component: dsu_c_tabbedcontent, allows to create a layout with tabs, that at the same time can contain
      components.
* Integrations:
    * New integration: dsu_engage, generates a webform that integrates into engage.

## 3.0.0 - 01/02/2018

* Integrated with composer. Modules are removed from main profile and added as a dependency.
    * Lightnest project: Set of tools and libraries that will trigger the full or light installation of Lightnest.
      Includes the readme file and declares all the internal DSU repositories.
    * Lightnest: Drupal installation profile. This project will orchestrate the versioning of all the dsu modules.
    * DSU Modules: Each one of the modules that have ever been developed for Lightnest is now sitting in its own
      repository, allowing a more granular version control.

## 2.1.0 - 01/10/2017

* Added accordion component.
* Removed all the media_entity dependencies.

## 2.0.0 - 01/08/2017

* Added composer dependency for Bootstrap and Fivestar.
* Added the dsu_rating_reviews module.
    * Creates a new rating content type integrated with Fivestar. This comment type can be added to any content type to
      provide simple rating and reviews functionality.
* Patched classy paragraphs so styles can be set for specific components.
* Components:
    * Optimized the component fields to be re-used.
    * New component: dsu_c_map, allows user to place a map with multiple locations.
    * New component: dsu_c_image, allows user to place an image and text over it.
    * New component: dsu_c_text, allows user to create text and apply multiple styles to it by using classy paragraphs.
    * New component: dsu_c_sideimagetext, allows user to create text and place an image by the side. Positions can be
      switched.
* Security module new version with new security fixes.
* Switched all the image fields to media fields in preparation for Drupal 8.4.
* Created a new content type to speed up component creation: Component page.
* Bootstrap is now set as the default Frontend theme, and configured to work with container fluid.
* Changed versioning system to allow semantic versioning.
* Removed UUIDs from all old components.
* Ghostery module cleaned up to avoid notices and warnings. Module still requires some rewriting.

## 1.3 - 01/06/2017

* Removed Lightning extension in favour of a new Lightnest subprofile of Lightning.
* Updates to security module.
* Updates to composer dependencies.
* Added store content type.
* Added new component system.
    * Created the dsu_c_core, which creates the layout components. Layout components allow users to store components
      inside.
    * Created the dsu_c_slider component, to allow the creation of Sliders.
    * Added classy paragraphs to allow different component styles.

## 1.2 - 01/05/2017

* Improvements to the security module.
* Changed project creation system to reduce dependency issues. Installation is now casted from lightnest_installer.sh
* Added INSTALL.MD to provide instructions.
* New Ghostery module version. Module is now configurable through the interface.
* Added instructions to Ghostery.

## 1.1 - 01/03/2017

* Added multitheme module.

## 1.0 - 01/02/2017

* Added Security module.
* Added Ghostery module.
* Integrated BLT to provide a common development workflow (http://blt.readthedocs.io/).
* Added composer to manage dependencies.

## 0.1 - 01/12/2016

* Added product and article content types.
* Added the DSU bundle.
* Added core fields for content types.
* Created a extension to Lightning.
