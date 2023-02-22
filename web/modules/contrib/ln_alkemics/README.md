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

	Alkemics is an intuitive platform that helps retailers and brands manage, collaborate and share product data in one place

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
		- API KEY needed
		- Entity Brawoser
		- Guzzle


	3.2 PROCESS

		1 -  Install de module and it's dependencies as usual.

		2 -  Please configure the module UI and test the connection to the Alkemics in /admin/config/ln_alkemics/config

		4 -  Once the connection test has passed, please proceed with the manual importation (in /admin/config/ln_alkemics/importer). It will increase the performance.

	3.3 CUATION

		1 - The importation procees may need specific server (php) configuration, to avoid timeout, and memomry limit, depending on the market and on the server configuration (not usual)

Functionality
------------

	o URL, Market, Lenguage & User configurable
	o Migration of all Products of a specific market to local
	o Creation of a Cotent-type to store Alekemics products, with Paragraphs and Taxonomies integrated
	o UI for Packets of recipes requested, cron syncronization and the time where cron will be executed
	o Custom basic temaplates
	o Cron configuration to run Alekemics priodiocially 
	o Images are storing with URL so won't create duplicate media files.
	o Drupal 9 compatibility and fallback with older versions.

Configurations
--------------

	1 - The configuration it's setted in Configuration->Alkemics Configuration->Lightnest Alkemics 

	2 - CONFIGURE THE SERVER CALL TO Alkemics URL endpoints

		Set the parameters, ask to the Nestle Digital Hub to get it

	3 - CONFIGURE SYNCHRONIZATION PROCESS IN YOUR WEBSITE

		Set on the checkbox to active the Cron Proces. Without this options, there recipes will not be syncronize periodically.

		Set the time where this process will be executed. (PLEASE NOTE; that the recipe search already has a syncronization feature, making this process more periodically we will improve the search feature performance)


Uninstall
---------

	To uninstall the module, be aware that the content type dsu_product and it's content are erased from the site.
	

EXTEND
------

 * hook_theme for extending default template of Lightnest alekemics.
 * hook_preprocess_node for data processing.
 * hook_ln_alkemics_import_alter & hook_ln_alkemics_header_options_alter for overriding the values.
 * Read ln_alkemics_api.php for more informations to override the product values.
 * Override default css libraries to change default UI/UX.


MAINTAINERS
-----------

* Nestle Webcms team.
