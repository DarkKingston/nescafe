CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Functionality
 * CONFIGURATION
 * Maintainers
 * Extend

INTRODUCTION
------------

	PDH is an intuitive platform that helps retailers and brands manage, collaborate and share product data in one place

REQUIREMENTS
------------

This module requires the following modules:

* Paragraphs (https://www.drupal.org/project/paragraphs)
* Image Widget Crop(https://www.drupal.org/project/image_widget_crop)
* Entity Browser(https://www.drupal.org/project/entity_browser)
* Entity Reference Revisions (https://www.drupal.org/project/entity_reference_revisions)


INSTALLATION
------------

	3.1 REQUIREMENTS
		- Paragraphs, Entity Revision, Video embed field modules
		- Certificates
		- Entity Browser
		- Guzzle


	3.2 PROCESS

		1 -  Install the module and it's dependencies as usual.

		2 -  Please configure the module UI and test the connection to the service in /admin/config/ln_pdh/config

		3 -  Once the connection test has passed, please proceed with the manual importation (in /admin/config/ln_pdh/importer). It will increase the performance.

    4 - The module includes a configuration for the dsu_product content type form in the config/optional folder. Please be aware that importing that file will remove any previous customization in that form.

	3.3 CAUTION

		1 - The importation procees may need specific server (php) configuration, to avoid timeout, and memory limit, depending on the market and on the server configuration (not usual)

Functionality
------------

	o URL, Market, Brand & Language configurable
	o Migration of all Products of a specific market to local
	o Creation of a Content-type to store products, with Paragraphs and Taxonomies integrated
	o UI for Packets of products requested, cron synchronization and the time where cron will be executed
	o Custom basic templates
	o Cron configuration to run PDH periodically
	o Import images locally to be able to use image styles and other Drupal functionality.
	o Drupal 9 compatibility and fallback with older versions.

Configurations
--------------

	1 - The configuration it's set in Configuration->LightNest PDH

	2 - CONFIGURE THE SERVER CALL TO PDH URL endpoints

		Set the parameters, ask to the Nestle Digital Hub to get it

	3 - CONFIGURE THE FIELDS THAT WILL BE IMPORTED

    Select the optional fields that will be added to the Product content type and synchronized. Check the product form display and and view display and, if paragraph fields have been selected, check also those configurations for the new paragraph types.

  4 - CONFIGURE SYNCHRONIZATION PROCESS IN YOUR WEBSITE

		Set on the checkbox to active the Cron Proces. Without this option, the products will not be synchronized periodically

		Set the time when this process will be executed


Uninstall
---------

	To uninstall the module, be aware that the content type dsu_product and it's content are erased from the site.


EXTEND
------

 * hook_theme for extending default template of Lightnest PDH.
 * hook_preprocess_node for data processing.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
